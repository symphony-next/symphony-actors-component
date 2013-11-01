<?php

	/**
	 * Manage actors (events, datasources) and their execution order.
	 *
	 * @package symphony\Actors
	 */

	namespace symphony\Actors;
	use Exception;

	/**
	 * Executes Actors one at a time, allowing Actors that depend on other
	 * Actors to wait until the last moment.
	 */
	class ExecutionIterator {
		/**
		 * Array of actors to work on.
		 *
		 * @var		Array
		 */
		protected $actors;

		public function __construct(array $actors) {
			$this->actors = $actors;
		}

		/**
		 * Iterate over actors ready for execution.
		 *
		 * @yields	Actor
		 */
		public function ready($last_chance = false) {
			foreach ($this->actors as $actor) {
				if (
					$actor->executed() === false
					&& $actor->executable()
					&& $actor->ready($last_chance)
				) {
					yield $actor;
				}
			}
		}

		/**
		 * Iterate over actors ready for execution.
		 *
		 * @yields	Actor|Exception
		 */
		public function execute() {
			$last_chance = false;

			while ($last_chance === false) {
				$work_done = false;

				foreach ($this->ready() as $actor) {
					$work_done = true;

					try {
						yield $actor => $actor->execute();
					}

					catch (Exception $error) {
						yield $actor => $error;
					}
				}

				$last_chance = !$work_done;
			}

			foreach ($this->ready(true) as $actor) {
				try {
					yield $actor => $actor->execute();
				}

				catch (Exception $error) {
					yield $actor => $error;
				}
			}
		}
	}