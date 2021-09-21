<?php
/**
 * Handle the editor configuration for Event Tickets Plus.
 */

namespace Tribe\Tickets\Plus\Editor;

use Tribe__Editor__Configuration_Interface;

/**
 * Class Tribe__Tickets_Plus__Editor__Configuration
 *
 * Class used to set values into the editor client (browser) via localized variables.
 *
 * @since 5.1.0
 */
class Configuration implements Tribe__Editor__Configuration_Interface {

	/**
	 * Add actions / filters into WP.
	 *
	 * @since 5.1.0
	 */
	public function hook() {
		add_filter( 'tribe_editor_config', [ $this, 'editor_config' ], 15 );
	}

	/**
	 * Hook into "tribe_editor_config" to attach new variables for tickets plus.
	 *
	 * @since 5.1.0
	 *
	 * @param array $editor_config localized configuration for block editor.
	 *
	 * @return array
	 */
	public function editor_config( $editor_config ) {
		$tickets_plus = empty( $editor_config['ticketsPlus'] ) ? [] : $editor_config['ticketsPlus'];

		$editor_config['ticketsPlus'] = array_merge(
			$tickets_plus,
			$this->localize()
		);

		return $editor_config;
	}

	/**
	 * Variables attached into the group that is used to localize values into the client.
	 *
	 * @since 5.1.0
	 *
	 * @return array
	 */
	public function localize() {
		/**
		 * Allow filtering the editor configuration vars for Event Tickets Plus.
		 *
		 * @since 5.1.0
		 *
		 * @param array $vars List of configuration vars.
		 */
		return apply_filters( 'tribe_tickets_plus_editor_configuration_vars', [] );
	}
}
