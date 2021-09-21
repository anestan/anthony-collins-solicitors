<?php

namespace Tribe\Tickets\Plus\Attendee_Registration\IAC;

use Tribe\Tickets\Plus\Attendee_Registration\IAC;
use Tribe__Utils__Array as Arr;

/**
 * Class Hooks
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration\IAC
 *
 * @since   5.1.0
 */
class Hooks {
	/**
	 * Whether to override meta handling for IAC.
	 *
	 * @var bool
	 */
	protected $override_meta = false;

	/**
	 * Handle enabling the override meta functionality.
	 *
	 * @since 5.1.0
	 *
	 * @param null|mixed $var The first variable which may or may not be set.
	 *
	 * @return null|mixed The first variable, if passed.
	 */
	public function enable_override_meta( $var = null ) {
		$this->override_meta = true;

		return $var;
	}

	/**
	 * Determine whether IAC will be used for a ticket based on hooks.
	 *
	 * @since 5.1.0
	 *
	 * @param int  $ticket_id     The ticket ID.
	 * @param bool $force_override Whether to force overriding.
	 *
	 * @return bool Whether IAC will be used for a ticket based on hooks.
	 */
	public function will_override_for_ticket( $ticket_id, $force_override = false ) {
		// Only override when we intend to.
		if ( ! $force_override && ! $this->override_meta ) {
			return false;
		}

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		// Check if IAC is on for the ticket.
		return IAC::NONE_KEY !== $iac->get_iac_setting_for_ticket( $ticket_id );
	}

	/**
	 * Filter whether the ticket has meta.
	 *
	 * @since 5.1.0
	 *
	 * @param bool $has_meta  Whether the ticket has meta.
	 * @param int  $ticket_id The ticket ID.
	 *
	 * @return bool Whether the ticket has meta.
	 */
	public function filter_tribe_tickets_plus_ticket_has_meta_enabled( $has_meta, $ticket_id ) {
		return $has_meta || $this->will_override_for_ticket( $ticket_id );
	}

	/**
	 * Filter and add any custom meta fields needed for IAC.
	 *
	 * @since 5.1.0
	 *
	 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] $fields    List of meta field objects for the ticket.
	 * @param int                                                $ticket_id The ticket ID.
	 *
	 * @return array The list of meta fields for a specific ticket.
	 */
	public function filter_event_tickets_plus_meta_fields_by_ticket( $fields, $ticket_id ) {
		// Only override when we intend to.
		$will_override = $this->will_override_for_ticket( $ticket_id );

		if ( ! $will_override ) {
			return $fields;
		}

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		// Add field configurations to top of list.
		return array_merge( $iac->get_field_configurations( $ticket_id ), $fields );
	}

	/**
	 * Filtering the value before it is retrieved from the database.
	 *
	 * @since 5.1.0
	 *
	 * @param null|string                                       $value       The value (default is null).
	 * @param int                                               $attendee_id The attendee ID.
	 * @param \Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field       The field object.
	 *
	 * @return null|string The value (default is null).
	 */
	public function filter_tribe_tickets_plus_meta_field_pre_value( $value, $attendee_id, $field ) {
		if ( null !== $value ) {
			return $value;
		}

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		return $iac->get_iac_override_meta_value( $attendee_id, $field );
	}

	/**
	 * Render the IAC override option in under Advance option for Ticket admin view.
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function show_iac_settings_for_tickets( $file, $name, $template ) {
		$post_id   = $template->get( 'post_id' );
		$ticket_id = $template->get( 'ticket_id' );
		$provider  = $template->get( 'provider' );

		/** @var Ticket_Settings $ticket_settings */
		$ticket_settings = tribe( 'tickets-plus.attendee-registration.iac.ticket-settings' );
		$ticket_settings->do_iac_ticket_settings_options( $post_id, $ticket_id, $provider );
	}

	/**
	 * Save ticket settings for IAC options.
	 *
	 * @param int                           $post_id  Post ID of post the ticket is tied to.
	 * @param Tribe__Tickets__Ticket_Object $ticket   Ticket that was just saved.
	 * @param array                         $raw_data Ticket data.
	 * @param string                        $class    Commerce engine class.
	 */
	public function save_iac_settings_for_tickets( $post_id, $ticket, $raw_data, $class ) {
		/** @var Ticket_Settings $ticket_settings */
		$ticket_settings = tribe( 'tickets-plus.attendee-registration.iac.ticket-settings' );
		$ticket_settings->save_iac_ticket_option( $post_id, $ticket, $raw_data, $class );
	}

	/**
	 * Filter the individual attendee name used when creating a new attendee when IAC is used.
	 *
	 * @since 5.1.0
	 *
	 * @param string                   $individual_attendee_name The attendee full name.
	 * @param int|null                 $attendee_number          The attendee number index value from the order, starting with zero.
	 * @param int                      $order_id                 The order ID.
	 * @param int                      $ticket_id                The ticket ID.
	 * @param int                      $post_id                  The ID of the post associated to the ticket.
	 * @param \Tribe__Tickets__Tickets $provider                 The current ticket provider object.
	 *
	 * @return string The attendee full name.
	 */
	public function filter_tribe_tickets_attendee_create_individual_name( $individual_attendee_name, $attendee_number, $order_id, $ticket_id, $post_id, $provider ) {
		// Only override when we intend to.
		$will_override = $this->will_override_for_ticket( $ticket_id, true );

		if ( ! $will_override ) {
			return $individual_attendee_name;
		}

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$iac_attendee_name = $iac->get_iac_attendee_name_from_attendee( $attendee_number, $order_id, $ticket_id, $post_id, $provider );

		// If no IAC attendee name available, return the normal attendee name.
		if ( '' === $iac_attendee_name ) {
			return $individual_attendee_name;
		}

		return $iac_attendee_name;
	}

	/**
	 * Filter the individual attendee email used when creating a new attendee when IAC is used.
	 *
	 * @since 5.1.0
	 *
	 * @param string                   $individual_attendee_email The attendee email.
	 * @param int|null                 $attendee_number           The attendee number index value from the order, starting with zero.
	 * @param int                      $order_id                  The order ID.
	 * @param int                      $ticket_id                 The ticket ID.
	 * @param int                      $post_id                   The ID of the post associated to the ticket.
	 * @param \Tribe__Tickets__Tickets $provider                  The current ticket provider object.
	 *
	 * @return string The attendee full name.
	 */
	public function filter_tribe_tickets_attendee_create_individual_email( $individual_attendee_email, $attendee_number, $order_id, $ticket_id, $post_id, $provider ) {
		// Only override when we intend to.
		$will_override = $this->will_override_for_ticket( $ticket_id, true );

		if ( ! $will_override ) {
			return $individual_attendee_email;
		}

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$iac_attendee_email = $iac->get_iac_attendee_email_from_attendee( $attendee_number, $order_id, $ticket_id, $post_id, $provider );

		// If no IAC attendee name available, return the normal attendee email.
		if ( '' === $iac_attendee_email ) {
			return $individual_attendee_email;
		}

		return $iac_attendee_email;
	}

	/**
	 * Handle updating the attendee with IAC name/email and the email resend.
	 *
	 * @since 5.1.0
	 *
	 * @param array    $attendee_meta   The attendee meta to be saved to the attendee.
	 * @param int      $attendee_id     The attendee ID.
	 * @param int      $order_id        The order ID.
	 * @param int      $ticket_id       The ticket ID.
	 * @param int|null $attendee_number The order attendee number.
	 */
	public function update_attendee_for_tribe_commerce( $attendee_meta, $attendee_id, $order_id, $ticket_id, $attendee_number ) {
		// Enable the override handling.
		$this->enable_override_meta();

		/** @var \Tribe__Tickets__Commerce__PayPal__Main $provider */
		$provider = tribe( 'tickets.commerce.paypal' );

		$this->update_attendee( $attendee_id, $order_id, $ticket_id, 0, $provider, $attendee_meta );
	}

	/**
	 * Handle updating the attendee with IAC name/email and the email resend.
	 *
	 * @since 5.1.0
	 *
	 * @param int|null                 $attendee_id The attendee ID.
	 * @param int                      $order_id    The order ID.
	 * @param int                      $ticket_id   The ticket ID.
	 * @param int                      $post_id     The ID of the post associated to the ticket.
	 * @param \Tribe__Tickets__Tickets $provider    The current ticket provider object.
	 * @param array                    $data        The data to use instead of looking up by meta.
	 */
	public function update_attendee( $attendee_id, $order_id, $ticket_id, $post_id, $provider, $data = [] ) {
		// Only override when we intend to.
		$will_override = $this->will_override_for_ticket( $ticket_id, true );

		if ( ! $will_override ) {
			return;
		}

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$field_slug_for_name  = $iac->get_iac_ticket_field_slug_for_name();
		$field_slug_for_email = $iac->get_iac_ticket_field_slug_for_email();

		if ( ! empty( $data ) ) {
			// Use the $data array to build the IAC information.
			$individual_attendee_name  = Arr::get( $data, $field_slug_for_name, '' );
			$individual_attendee_email = Arr::get( $data, $field_slug_for_email, '' );
		} else {
			// Build the IAC information based on meta we have.
			$individual_attendee_name  = $iac->get_iac_attendee_name_from_attendee( $attendee_id, $order_id, $ticket_id, $post_id, $provider );
			$individual_attendee_email = $iac->get_iac_attendee_email_from_attendee( $attendee_id, $order_id, $ticket_id, $post_id, $provider );
		}

		if ( ! empty( $individual_attendee_name ) ) {
			update_post_meta( $attendee_id, $provider->full_name, sanitize_text_field( $individual_attendee_name ) );
		}

		if ( ! empty( $individual_attendee_email ) ) {
			$attendee_data = $provider->get_attendee( $attendee_id, $post_id );

			update_post_meta( $attendee_id, $provider->email, sanitize_text_field( $individual_attendee_email ) );

			// Maybe reset security code if email has changed.
			if ( $attendee_data['holder_email'] !== $individual_attendee_email ) {
				// Enforce a unique security code.
				$security_code = $provider->generate_security_code( $attendee_id . '_' . $individual_attendee_email );

				update_post_meta( $attendee_id, $provider->security_code, $security_code );
			}
		}
	}

	/**
	 * Handle resend email on the My Tickets page.
	 *
	 * @since 5.1.0
	 *
	 * @param int|null                 $attendee_id  The attendee ID.
	 * @param array                    $data_to_save The data that was saved.
	 * @param array                    $data         The data prior to filtering for saving.
	 * @param int                      $order_id     The order ID.
	 * @param int                      $ticket_id    The ticket ID.
	 * @param int                      $post_id      The ID of the post associated to the ticket.
	 * @param \Tribe__Tickets__Tickets $provider     The current ticket provider object.
	 */
	public function handle_my_tickets_resend_email( $attendee_id, $data_to_save, $data, $order_id, $ticket_id, $post_id, $provider ) {
		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$iac_setting = $iac->get_iac_setting_for_ticket( $ticket_id );
		$resend_email_slug = $iac->get_iac_ticket_field_slug_for_resend_email();

		if ( empty( $data[ $resend_email_slug ] ) ) {
			return;
		}

		$resend_email = tribe_is_truthy( $data[ $resend_email_slug ] );

		if ( ! $resend_email ) {
			return;
		}

		$attendee = $provider->get_attendee( $attendee_id, $post_id );

		if ( ! $attendee ) {
			return;
		}

		$send_args = [
			'post_id'  => $post_id,
			'order_id' => $order_id,
		];

		$tickets = [
			$attendee,
		];

		$provider->send_tickets_email_for_attendee( $attendee['holder_email'], $tickets, $send_args );
	}

	/**
	 * Filter the attendee meta to be saved to the attendee to remove the IAC name/email/resend email field values.
	 *
	 * @since 5.1.0
	 *
	 * @param array    $attendee_meta   The attendee meta to be saved to the attendee.
	 * @param int      $attendee_id     The attendee ID.
	 * @param int      $order_id        The order ID.
	 * @param int      $ticket_id       The ticket ID.
	 * @param int|null $attendee_number The order attendee number.
	 *
	 * @return array The attendee meta to be saved to the attendee.
	 */
	public function remove_iac_fields_from_meta_before_save( $attendee_meta, $attendee_id, $order_id, $ticket_id, $attendee_number ) {
		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$name_slug         = $iac->get_iac_ticket_field_slug_for_name();
		$email_slug        = $iac->get_iac_ticket_field_slug_for_email();
		$resend_email_slug = $iac->get_iac_ticket_field_slug_for_resend_email();

		if ( isset( $attendee_meta[ $name_slug ] ) ) {
			unset( $attendee_meta[ $name_slug ] );
		}

		if ( isset( $attendee_meta[ $email_slug ] ) ) {
			unset( $attendee_meta[ $email_slug ] );
		}

		if ( isset( $attendee_meta[ $resend_email_slug ] ) ) {
			unset( $attendee_meta[ $resend_email_slug ] );
		}

		return $attendee_meta;
	}

	/**
	 * Add the IAC config to the editor configuration vars.
	 *
	 * @since 5.1.0
	 *
	 * @param array $vars List of configuration vars.
	 *
	 * @return array List of configuration vars.
	 */
	public function add_iac_editor_configuration_vars( $vars ) {
		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$iac_vars = [
			'iacDefault' => $iac->get_default_iac_setting(),
			'iacOptions' => [],
		];

		$options = $iac->get_iac_setting_options();

		foreach ( $options as $value => $label ) {
			$iac_vars['iacOptions'][] = [
				'label' => $label,
				'value' => $value,
			];
		}

		$vars['iacVars'] = $iac_vars;

		return $vars;
	}

	/**
	 * Add the IAC setting to the ticket object.
	 *
	 * @since 5.1.0
	 *
	 * @param \Tribe__Tickets__Ticket_Object $ticket  The ticket object.
	 * @param int                            $post_id The ticket parent post ID.
	 *
	 * @return \Tribe__Tickets__Ticket_Object The ticket object.
	 */
	public function add_iac_to_ticket_object( $ticket, $post_id ) {
		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$ticket->iac = $iac->get_iac_setting_for_ticket( $ticket );

		return $ticket;
	}

	/**
	 * Add the IAC setting to the tickets in the cart.
	 *
	 * @since 5.1.0
	 *
	 * @param array $tickets List of tickets in the cart.
	 *
	 * @return array List of tickets in the cart.
	 */
	public function add_iac_to_tickets_in_cart( $tickets ) {
		return array_map( [ $this, 'add_iac_to_ticket_in_cart' ], $tickets );
	}

	/**
	 * Add the IAC setting to the ticket in the cart.
	 *
	 * @since 5.1.0
	 *
	 * @param array $ticket The ticket data for the cart.
	 *
	 * @return array The ticket data for the cart.
	 */
	public function add_iac_to_ticket_in_cart( $ticket ) {
		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$ticket_id = isset( $ticket['ticket_id'] ) ? $ticket['ticket_id'] : $ticket['id'];

		$ticket['iac'] = $iac->get_iac_setting_for_ticket( $ticket_id );

		return $ticket;
	}

	/**
	 * Render the IAC type for tickets under Ticket Name for Attendee Reports overview section.
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function render_iac_label_for_ticket( $file, $name, $template ) {
		$ticket = $template->get( 'ticket_item_for_overview' );

		/** @var IAC $iac */
		$iac   = tribe( 'tickets-plus.attendee-registration.iac' );
		$label = $iac->get_iac_setting_label_for_ticket( $ticket, false );

		if ( empty( $label ) ) {
			return;
		}

		echo wp_kses_post( sprintf( '<br><span class="ticket_iac_type">%s</span>', $label ) );
	}

	/**
	 * Render the IAC email disclaimer.
	 *
	 * @since 5.1.0
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 *
	 * @return void
	 */
	public function render_email_disclaimer( $file, $name, $template ) {
		$tickets = $template->get( 'tickets', [] );
		$has_iac = 0;

		foreach ( $tickets as $ticket ) {
			$is_iac = IAC::NONE_KEY !== ( is_array( $ticket ) ? $ticket['iac'] : $ticket->iac );

			if ( $is_iac ) {
				$has_iac++;
			}
		}

		if ( ! $has_iac ) {
			return;
		}

		$template->template( 'v2/iac/attendee-registration/email-disclaimer' );
	}

	/**
	 * Render the IAC unique error templates.
	 *
	 * @since 5.1.0
	 */
	public function render_unique_error_templates() {
		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		$template->template( 'v2/iac/attendee-registration/unique-name-error' );
		$template->template( 'v2/iac/attendee-registration/unique-email-error' );
	}

	/**
	 * Filter the tickets block ticket data attributes.
	 *
	 * @since 5.2.1
	 *
	 * @param array                         $attributes The HTML data attributes.
	 * @param Tribe__Tickets__Ticket_Object $ticket The ticket object.
	 *
	 * @return array The HTML data attributes.
	 */
	public function maybe_add_html_attribute_to_ticket( $attributes, $ticket ) {
		if ( IAC::NONE_KEY === $ticket->iac ) {
			return $attributes;
		}

		$attributes['data-ticket-iac'] = $ticket->iac;

		return $attributes;
	}
}
