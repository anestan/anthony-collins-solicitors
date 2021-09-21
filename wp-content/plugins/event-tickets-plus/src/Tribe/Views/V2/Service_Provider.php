<?php
/**
 * The main service provider handling the integration between Event Tickets Plus and Views v2.
 *
 * @since   5.1.1
 *
 * @package Tribe\Tickets\Plus\Views\V2
 */

namespace Tribe\Tickets\Plus\Views\V2;

/**
 * Class Service_Provider
 *
 * @since   5.1.1
 *
 * @package Tribe\Tickets\Plus\Views\V2
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the bindings,
	 *
	 * @since 5.1.1
	 *
	 */
	public function register() {
		if ( ! tribe_events_tickets_views_v2_is_enabled() ) {
			// If Views v2 is not enabled, then none of its integration should be activated.
			return;
		}

		// Register this Service Provider on the container.
		$this->container->singleton( 'tickets-plus.views.v2.provider', $this );
		$this->container->singleton( static::class, $this );

		$this->register_hooks();
	}

	/**
	 * Registers the accessory service provider that will handle the actions and filters.
	 *
	 * @since 5.1.1
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'tickets-plus.views.v2.hooks', $hooks );
	}
}
