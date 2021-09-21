<?php

namespace Tribe\Tickets\Plus\Manual_Attendees;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * @package Tribe\Tickets\Plus\Manual_Attendees
 *
 * @since   5.2.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {
	/**
	 * Register the provider singletons.
	 *
	 * @since 5.2.0
	 */
	public function register() {
		$this->container->singleton( 'tickets-plus.manual-attendees.assets', Assets::class );
		$this->container->singleton( 'tickets-plus.manual-attendees.hooks', Hooks::class );
		$this->container->singleton( 'tickets-plus.manual-attendees.permissions', Permissions::class );
		$this->container->singleton( 'tickets-plus.manual-attendees.modal', Modal::class );
		$this->container->singleton( 'tickets-plus.manual-attendees.view', View::class );

		// Only run the MA features if it is enabled.
		if ( ! tribe_tickets_ma_is_enabled() ) {
			return;
		}

		/**
		 * Allow filtering whether to show Manual Attendees functionality on the front of the site.
		 *
		 * @since 5.2.0
		 *
		 * @param bool $show_manual_attendees_on_front Whether to show Manual Attendees, defaults to off.
		 */
		$show_manual_attendees_on_front = (bool) apply_filters( 'tribe_tickets_plus_manual_attendees_show_on_front', false );

		if ( ! $show_manual_attendees_on_front && ! is_admin() ) {
			return;
		}

		$this->hooks();
		$this->assets();
	}

	/**
	 * Registers the assets.
	 *
	 * @since 5.2.0
	 */
	protected function assets() {
		$assets = new Assets( $this->container );
		$assets->register();
	}

	/**
	 * Add actions and filters for the classes.
	 *
	 * @since 5.2.0
	 */
	protected function hooks() {
		add_action( 'admin_footer', $this->container->callback( 'tickets-plus.manual-attendees.modal', 'render_modal' ) );

		// Handle Edit Links for Attendee list view.
		add_filter( 'event_tickets_attendees_table_row_actions', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'add_edit_attendee_row_action' ), 10, 2 );
		add_action( 'tribe_tickets_attendee_table_columns', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'add_attendee_edit_table_column_header' ), 10, 2 );
		add_filter( 'tribe_events_tickets_attendees_table_column', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'render_column_edit_attendee' ), 10, 3 );
		add_action( 'tribe_events_tickets_attendees_csv_export_columns', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'remove_edit_column_for_csv_export' ), 10, 3 );

		// Handle button for Add Attendee.
		add_filter( 'tribe_events_tickets_attendees_table_nav', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'add_nav_button' ), 10, 2 );
		add_action( 'tribe_tabbed_view_heading_after_text_label', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'add_nav_button_on_title' ) );
		add_action( 'tribe_report_page_after_text_label', $this->container->callback( 'tickets-plus.manual-attendees.hooks', 'add_nav_button_on_title' ) );

		// Hook the Manual Attendees view rendering for the AJAX requests.
		add_filter( 'tribe_tickets_admin_manager_request', $this->container->callback( 'tickets-plus.manual-attendees.view', 'get_modal_content' ), 10, 2 );
	}
}
