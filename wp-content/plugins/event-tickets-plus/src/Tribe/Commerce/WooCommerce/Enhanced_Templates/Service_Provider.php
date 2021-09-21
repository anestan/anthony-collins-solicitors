<?php

namespace Tribe\Tickets\Plus\Commerce\WooCommerce\Enhanced_Templates;

use tad_DI52_ServiceProvider;

class Service_Provider extends tad_DI52_ServiceProvider{

	/**
	 * Register the provider singletons.
	 *
	 * @since 5.2.7
	 */
	public function register() {

		// Disable the old extension if enabled.
		if ( class_exists( 'Tribe\Extensions\ET_Woo_Order_Details\Bootstrap' ) ) {
			$this->disable_extension();
		}

		// Load classes.
		$this->container->singleton( 'tickets-plus.woo.enhanced-template-hooks', Hooks::class );
		$this->hooks();
	}

	/**
	 * Add actions and filters for the classes.
	 *
	 * @since 5.2.7
	 */
	protected function hooks() {
		add_action( 'woocommerce_order_item_meta_start', $this->container->callback( 'tickets-plus.woo.enhanced-template-hooks', 'woocommerce_echo_event_info' ), 100, 3 );
		add_action( 'woocommerce_admin_order_item_headers', $this->container->callback( 'tickets-plus.woo.enhanced-template-hooks', 'add_event_title_header' ) );
		add_action( 'woocommerce_admin_order_item_values', $this->container->callback( 'tickets-plus.woo.enhanced-template-hooks', 'add_event_title_for_order_item' ), 10, 3 );
		add_action( 'woocommerce_before_order_itemmeta', $this->container->callback( 'tickets-plus.woo.enhanced-template-hooks', 'add_attendee_data_for_order_item' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', $this->container->callback( 'tickets-plus.woo.enhanced-template-hooks', 'admin_order_table_styles' ), 99 );
	}

	/**
	 * Deactivate the existing extension.
	 *
	 * @since 5.2.7
	 */
	protected function disable_extension() {
		add_action( 'admin_init', [ $this, 'deactivate_extension' ] );
		add_action( 'admin_notices', [ $this, 'display_notice' ] );
	}

	/**
	 * Deactivates the extension plugin.
	 *
	 * @since 5.2.7
	 */
	public function deactivate_extension() {
		if ( is_plugin_active( 'tribe-ext-woo-order-templates/Bootstrap.php' ) ) {
			deactivate_plugins( 'tribe-ext-woo-order-templates/Bootstrap.php' );
		}
	}

	/**
	 * Display deactivation notice.
	 *
	 * @since 5.2.7
	 */
	public function display_notice() {
		$class     = 'notice notice-warning';
		$extension = __( 'Event Tickets Plus Extension: Enhance Woo Order Templates', 'event-tickets-plus' );
		$message   = __( 'was deactivated, as this feature is now available within Event Tickets Plus', 'event-tickets-plus' );

		printf( '<div class="%1$s"><p><b>%2$s</b> - %3$s</div></p>', esc_attr( $class ), esc_html( $extension ), esc_html( $message ) );
	}

}