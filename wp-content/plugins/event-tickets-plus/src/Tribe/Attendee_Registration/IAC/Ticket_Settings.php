<?php

namespace Tribe\Tickets\Plus\Attendee_Registration\IAC;

/**
 * Class Ticket_Settings
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration\IAC
 *
 * @since   5.1.0
 */
class Ticket_Settings {

	/**
	 * Render the IAC options.
	 *
	 * @since 5.1.0
	 *
	 * @param int                           $post_id      Post ID of post the ticket is tied to.
	 * @param null|int                      $ticket_id    Ticket ID of ticket.
	 * @param null|\Tribe__Tickets__Tickets $provider_obj The provider object (if set).
	 */
	public function do_iac_ticket_settings_options( $post_id, $ticket_id, $provider_obj = null ) {
		// Do not show for RSVP tickets for now.
		if ( $this->is_ticket_rsvp( $ticket_id, $provider_obj ) ) {
			return;
		}

		/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$iac_options = $iac->get_iac_setting_options();
		$iac_default = $iac->get_default_iac_setting();
		$selected    = $iac->get_iac_setting_for_ticket( $ticket_id );

		// If showing a new ticket form, use the default IAC setting.
		if ( empty( $ticket_id ) ) {
			$selected = $iac_default;
		}

		// @todo Determine if this is a new ticket or not.

		/** @var \Tribe__Tickets_Plus__Admin__Views $view */
		$view = tribe( 'tickets-plus.admin.views' );

		$context = [
			'post_id'     => $post_id,
			'ticket_id'   => $ticket_id,
			'iac_options' => $iac_options,
			'iac_default' => $iac_default,
			'selected'    => $selected,
		];

		$view->template( 'editor/fieldset/attendee-collection', $context );
	}

	/**
	 * Save settings for ticket.
	 *
	 * @since 5.1.0
	 *
	 * @param int                           $post_id  Post ID of post the ticket is tied to.
	 * @param Tribe__Tickets__Ticket_Object $ticket   Ticket that was just saved.
	 * @param array                         $raw_data Ticket data.
	 * @param string                        $class    Commerce engine class.
	 */
	public function save_iac_ticket_option( $post_id, $ticket, $raw_data, $class ) {
		// Do not save to RSVP tickets for now.
		if ( $this->is_ticket_rsvp( $ticket->ID ) ) {
			return;
		}

		/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		if ( ! empty( $raw_data['ticket_iac'] ) ) {
			$value = $raw_data['ticket_iac'];
		} elseif ( ! empty( $raw_data['iac'] ) ) {
			$value = $raw_data['iac'];
		} else {
			// Don't save anything if not set.
			return;
		}

		update_post_meta( $ticket->ID, $iac->get_iac_setting_ticket_meta_key(), sanitize_text_field( $value ) );
	}

	/**
	 * Determine whether the ticket is an RSVP or not.
	 *
	 * @since 5.1.0
	 *
	 * @param null|int                      $ticket_id    Ticket ID of ticket.
	 * @param null|\Tribe__Tickets__Tickets $provider_obj The provider object (if set).
	 *
	 * @return bool Whether the ticket is an RSVP or not.
	 */
	public function is_ticket_rsvp( $ticket_id, $provider_obj = null ) {
		if ( $provider_obj instanceof \Tribe__Tickets__Tickets ) {
			return 'rsvp' === $provider_obj->orm_provider;
		}

		if ( ! $ticket_id ) {
			return false;
		}

		$provider = tribe_tickets_get_ticket_provider( $ticket_id );

		return $provider && 'rsvp' === $provider->orm_provider;
	}
}
