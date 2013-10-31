<?php

	/**
	 * Manage actors (events, datasources) and their execution order.
	 *
	 * @package symphony\Actors
	 */

	namespace symphony\Actors;

	interface Actor {
		/**
		 * Is this Actor executable in this session?
		 *
		 * @return	boolean
		 */
		public function executable();

		/**
		 * Execute the Actor.
		 *
		 * @param	boolean		$final
		 *	Is this the final call for readyness?
		 * @return	boolean
		 * @throws	Exception
		 */
		public function execute($final = false);

		/**
		 * Is this Actor ready to be executed?
		 *
		 * @param	boolean		$final
		 *	Is this the final call for readyness?
		 * @return	boolean
		 */
		public function ready($final = false);
	}