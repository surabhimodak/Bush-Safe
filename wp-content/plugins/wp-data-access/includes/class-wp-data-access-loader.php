<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\includes
 */

/**
 * Class WP_Data_Access_Loader
 *
 * Adds and activates plugin filters and actions.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WP_Data_Access_Loader {

	/**
	 * Registered plugin actions
	 *
	 * @var array
	 */
	protected $actions = [];

	/**
	 * Registered plugin filters
	 *
	 * @var array
	 */
	protected $filters = [];

	/**
	 * Adds an action to the action array
	 *
	 * Calls method add the create array element to be added to action array.
	 *
	 * @param string $hook Action name to be registered.
	 * @param object $component Reference to object to which the action will be applied.
	 * @param string $callback Callback function: method of $component object.
	 * @param int    $priority Priority of the action (default = 10).
	 * @param int    $accepted_args Number of accepted arguments of callback (default = 1).
	 *
	 * @see WP_Data_Access_Loader::add()
	 *
	 * @since   1.0.0
	 *
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Create action or filter element
	 *
	 * Generic creation of an element that can be added to the action array or filter array.
	 *
	 * @param array  $hooks Action or filter array to which the hook is added.
	 * @param string $hook Action or filter name to be registered.
	 * @param object $component Reference to object to which the action or filter will be applied.
	 * @param string $callback Callback function: method of $component object.
	 * @param int    $priority Priority of the action or action.
	 * @param int    $accepted_args Number of accepted arguments of callback.
	 *
	 * @return array
	 * @since   1.0.0
	 *
	 */
	protected function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];

		return $hooks;
	}

	/**
	 * Adds a filter to the filter array
	 *
	 * Calls method add the create array element to be added to filter array.
	 *
	 * @param string $hook Filter name to be registered.
	 * @param object $component Reference to object to which the filter will be applied.
	 * @param string $callback Callback function: method of $component object.
	 * @param int    $priority Priority of the filter (default = 10).
	 * @param int    $accepted_args Number of accepted arguments of callback (default = 1).
	 *
	 * @see WP_Data_Access_Loader::add()
	 *
	 * @since   1.0.0
	 *
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Registration of filters and actions
	 *
	 * @since   1.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [
				$hook['component'],
				$hook['callback']
			], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [
				$hook['component'],
				$hook['callback']
			], $hook['priority'], $hook['accepted_args'] );
		}
	}

}
