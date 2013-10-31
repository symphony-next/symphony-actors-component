<?php

	/**
	 * @package symphony\Actors
	 */

	namespace symphony\Actors;
	use ArrayIterator;

	/**
	 * Executes Actors one at a time, allowing Actors that depend on other
	 * Actors to wait until the last moment.
	 *
	 *	$actors = new ExecutionIterator([
	 *		new TestActor(),
	 *		new TestActor()
	 *	]);
	 *
	 * 	while (true) {
	 *		try {
	 *			if ($actors->execute() === false) break;
	 *		}
	 *
	 *		catch (Exception $e) {
	 *			$actor = $actors->current();
	 *			throw new Exception('Error while executing actor ' . get_class($actor));
	 *		}
	 *	}
	 */
	class ExecutionIterator extends ArrayIterator {
		/**
		 * When this is true time has run out, any Actor not yet executed
		 * will be given one final chance to do so before execution stops.
		 *
		 * @var		boolean
		 */
		protected $lastChance;

		/**
		 * Represents the number of items in the itorator last time
		 * `execute` was called. If this number does not change after the
		 * following execution, the execution after that will be declared
		 * as the final execution (final countdown...)
		 *
		 * @var		integer
		 */
		protected $lastLength;

		/**
		 * Get the currently executing actor.
		 *
		 * @return	Actor
		 */
		public function current() {
			return $this->current();
		}

		/**
		 * Execute the next actor.
		 *
		 * @return	boolean
		 *	False when all actors have been executed.
		 */
		public function execute() {
			$this->next();

			// Reached end of iterator, start over:
			if ($this->valid() === false) {
				$this->rewind();

				// Nothing was executed?
				if ($this->lastLength == $this->count()) {
					$this->lastChance = true;
				}

				$this->lastLength = $this->count();
			}

			$actor = $this->current();

			// No more actors:
			if ($actor === null) return false;

			// The actor is executable:
			if ($actor->executable()) {
				// Actor is ready, execute it:
				if ($actor->ready($this->lastChance)) {
					$actor->execute($this->lastChance);
					$this->offsetUnset($this->key());
				}

				// The actor was not ready at its last chance to execute:
				else if ($this->lastChance) {
					$this->offsetUnset($this->key());
				}
			}

			// The actor is not executable, remove it from the stack:
			else {
				$this->offsetUnset($this->key());
			}

			return true;
		}
	}