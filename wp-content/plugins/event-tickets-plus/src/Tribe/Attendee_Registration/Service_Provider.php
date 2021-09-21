<?php

namespace Tribe\Tickets\Plus\Attendee_Registration;

use tad_DI52_ServiceProvider;
use Tribe\Tickets\Plus\Attendee_Registration\IAC\Hooks;
use Tribe\Tickets\Plus\Attendee_Registration\IAC\Ticket_Settings;

/**
 * Class Service_Provider
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration
 *
 * @since   5.1.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {
	/**
	 * Register the provider singletons.
	 *
	 * @since 5.1.0
	 */
	public function register() {
		$this->container->singleton( 'tickets-plus.attendee-registration.fields', Fields::class );
		$this->container->singleton( 'tickets-plus.attendee-registration.iac', IAC::class );
		$this->container->singleton( 'tickets-plus.attendee-registration.iac.hooks', Hooks::class );
		$this->container->singleton( 'tickets-plus.attendee-registration.modal', Modal::class );
		$this->container->singleton( 'tickets-plus.attendee-registration.view', View::class );
		$this->container->singleton( 'tickets-plus.attendee-registration.iac.ticket-settings', Ticket_Settings::class );

		// @todo Refactor this in the future to move to ET+ entirely, using same backcompat ET slug.
		$this->container->singleton( 'tickets.editor.attendee_registration', 'Tribe__Tickets__Editor__Attendee_Registration' );

		$this->hooks();
	}

	/**
	 * Add actions and filters for the classes.
	 *
	 * @since 5.1.0
	 */
	protected function hooks() {
		add_action( 'init', $this->container->callback( 'tickets-plus.attendee-registration.modal', 'hook' ) );

		// @todo Refactor this in the future to move to ET+ entirely.
		tribe( 'tickets.editor.attendee_registration' )->hook();

		// Only run the IAC feature if the new views are enabled.
		if ( ! function_exists( 'tribe_tickets_new_views_is_enabled' ) || ! tribe_tickets_new_views_is_enabled() ) {
			return;
		}

		// Enable the meta override for AR modal and page.
		add_action( 'tribe_template_before_include:tickets-plus/v2/attendee-registration/content', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ) );
		add_action( 'tribe_template_before_include:tickets-plus/v2/tickets/submit/button-modal', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ) );
		add_action( 'tribe_tickets_plus_meta_contents_up_to_date', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ) );

		// Show email disclaimer.
		add_action( 'tribe_template_before_include:tickets-plus/v2/attendee-registration/footer', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'render_email_disclaimer' ), 10, 3 );
		add_action( 'tribe_template_before_include:tickets-plus/v2/modal/attendee-registration/footer', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'render_email_disclaimer' ), 10, 3 );

		// Show the unique meta error templates.
		add_action( 'tribe_template_before_include:tickets/v2/tickets/title', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'render_unique_error_templates' ) );
		add_action( 'tribe_template_before_include:tickets-plus/v2/attendee-registration/content/title', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'render_unique_error_templates' ) );

		// Enable the meta override for the My Tickets page.
		add_action( 'tribe_template_after_include:tickets/tickets/orders-rsvp', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ) );

		// Handle resend email from the My Tickets page.
		add_action( 'tribe_tickets_plus_after_my_tickets_attendee_update', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'handle_my_tickets_resend_email' ), 10, 7 );

		// Enable the meta override for the new RSVP block.
		// add_action( 'tribe_template_before_include:tickets/v2/rsvp/ari/form', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ) );

		// Enable the meta override for the My RSVPs page.
		// add_action( 'tribe_template_before_include:tickets/tickets/orders-rsvp', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ) );

		// Enable the meta override for the Checkout links.
		add_action( 'edd_purchase_form_before_submit', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ), 9 );
		add_action( 'woocommerce_checkout_before_order_review', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'enable_override_meta' ), 9 );

		// Maybe add ARF data attributes to the ticket in the tickets block.
		add_filter( 'tribe_tickets_block_ticket_html_attributes', $this->container->callback( 'tickets-plus.attendee-registration.fields', 'maybe_add_html_attribute_to_ticket' ), 10, 2 );

		// Maybe add IAC data attributes to the ticket in the tickets block.
		add_filter( 'tribe_tickets_block_ticket_html_attributes', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'maybe_add_html_attribute_to_ticket' ), 10, 2 );

		// Handle IAC integration.
		add_filter( 'tribe_tickets_plus_ticket_has_meta_enabled', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'filter_tribe_tickets_plus_ticket_has_meta_enabled' ), 10, 2 );
		add_filter( 'event_tickets_plus_meta_fields_by_ticket', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'filter_event_tickets_plus_meta_fields_by_ticket' ), 10, 2 );

		// Handle IAC in the admin area.
		add_action( 'tribe_template_entry_point:tickets/admin-views/attendees:overview_section_after_ticket_name', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'render_iac_label_for_ticket' ), 10, 3 );

		// Handle IAC Classic Editor integration.
		add_action( 'tribe_template_after_include:tickets/admin-views/editor/fieldset/advanced', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'show_iac_settings_for_tickets' ), 10, 3 );

		// Handle saving the IAC setting for a ticket.
		add_action( 'tribe_tickets_ticket_add', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'save_iac_settings_for_tickets' ), 10, 4 );
		add_action( 'event_tickets_after_save_ticket', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'save_iac_settings_for_tickets' ), 10, 4 );

		// Handle IAC saving.
		add_filter( 'tribe_tickets_attendee_create_individual_name', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'filter_tribe_tickets_attendee_create_individual_name' ), 10, 6 );
		add_filter( 'tribe_tickets_attendee_create_individual_email', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'filter_tribe_tickets_attendee_create_individual_email' ), 10, 6 );
		add_action( 'tribe_tickets_plus_attendee_update', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'update_attendee' ), 10, 5 );
		add_action( 'tribe_tickets_plus_commerce_paypal_meta_before_save', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'update_attendee_for_tribe_commerce' ), 10, 5 );
		add_filter( 'tribe_tickets_plus_attendee_save_meta', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'remove_iac_fields_from_meta_before_save' ), 10, 5 );

		// Handle IAC in the block editor.
		add_filter( 'tribe_tickets_plus_editor_configuration_vars', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_editor_configuration_vars' ) );

		// Handle getting the IAC setting for tickets.
		// add_filter( 'tribe_tickets_rsvp_get_ticket', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_ticket_object' ), 10, 2 );
		add_filter( 'tribe_tickets_tpp_get_ticket', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_ticket_object' ), 10, 2 );
		add_filter( 'tribe_tickets_plus_edd_get_ticket', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_ticket_object' ), 10, 2 );
		add_filter( 'tribe_tickets_plus_woo_get_ticket', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_ticket_object' ), 10, 2 );

		// Handle getting IAC setting for tickets in cart.
		add_filter( 'tribe_tickets_commerce_cart_get_tickets_tribe-commerce', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_tickets_in_cart' ), 11 );
		add_filter( 'tribe_tickets_commerce_cart_get_tickets_edd', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_tickets_in_cart' ), 11 );
		add_filter( 'tribe_tickets_commerce_cart_get_tickets_woo', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_tickets_in_cart' ), 11 );
		add_filter( 'tribe_tickets_plus_attendee_registration_view_ticket_data', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'add_iac_to_ticket_in_cart' ) );

		// Handle getting the IAC values when editing a ticket.
		add_filter( 'tribe_tickets_plus_meta_field_pre_value', $this->container->callback( 'tickets-plus.attendee-registration.iac.hooks', 'filter_tribe_tickets_plus_meta_field_pre_value' ), 10, 3 );
	}
}
