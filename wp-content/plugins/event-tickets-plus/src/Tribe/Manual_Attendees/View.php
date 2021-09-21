<?php
/**
 * The views logic needed for Manual Attendees forms.
 */

namespace Tribe\Tickets\Plus\Manual_Attendees;

use Tribe__Utils__Array as Arr;
use WP_Error;

/**
 * Class View
 *
 * @package Tribe\Tickets\Plus\Manual_Attendees
 *
 * @since 5.2.0
 */
class View {

	/**
	 * Get the Manual Attendee modal content,
	 * depending on the request.
	 *
	 * @since 5.2.0
	 *
	 * @param string|\WP_Error $render_response The render response HTML content or WP_Error with list of errors.
	 * @param array            $vars            The request variables.
	 *
	 * @return string $html The response with the HTML of the form, depending on the call.
	 */
	public function get_modal_content( $render_response, $vars ) {
		$html = '';

		if ( 'submit' === Arr::get( $vars, 'step', '' ) ) {
			$template_args = $this->get_form_template_args( $vars );
			$response_args = $this->process_request( $vars, $template_args );

			if ( is_string( $response_args ) || is_wp_error( $response_args ) ) {
				return $response_args;
			}

			$template_args = array_merge( $template_args, $response_args );

			/** @var \Tribe__Tickets_Plus__Admin__Views $template */
			$template = tribe( 'tickets-plus.admin.views' );

			// Add the rendering attributes into global context.
			$template->add_template_globals( $template_args );

			if ( 'tribe_tickets_manual_attendees_edit' === $vars['request'] ) {
				$html .= $template->template( 'manual-attendees/edit/success', $template_args, false );
			} elseif ( 'tribe_tickets_manual_attendees_add' === $vars['request'] ) {
				$html .= $template->template( 'manual-attendees/add/success', $template_args, false );
			}
		} else {
			if ( 'tribe_tickets_manual_attendees_edit' === $vars['request'] ) {
				$html .= $this->get_form_edit( $vars );
			} elseif ( 'tribe_tickets_manual_attendees_add' === $vars['request'] ) {
				$html .= $this->get_form_add( $vars );
			}
		}

		return $html;
	}

	/**
	 * Process the Manual Attendees submission request.
	 *
	 * @since 5.2.0
	 *
	 * @param array $vars          The list of request variables.
	 * @param array $template_args The list of template arguments.
	 *
	 * @return array|\WP_Error The response template arguments or an WP_Error with a list of errors.
	 */
	public function process_request( $vars, $template_args ) {
		// @TODO @skc @sc0ttclark: Create OR edit an attendee. You can move this to a method, or as you like.
		// If $vars['ticketId'] is there, we should edit.
		// Also can find that out if the request is `tribe_tickets_manual_attendees_edit` || `tribe_tickets_manual_attendees_add`

		$response_args = [];

		/**
		 * @var \Tribe__Tickets__Ticket_Object $ticket
		 * @var \Tribe__Tickets__Tickets       $provider
		 */
		$step        = $template_args['step'];
		$ticket_id   = $template_args['ticket_id'];
		$ticket      = $template_args['ticket'];
		$provider    = $template_args['provider'];
		$attendee_id = (int) $template_args['attendee_id'];

		if ( empty( $ticket_id ) || empty( $ticket ) ) {
			return new WP_Error( 'tribe-tickets-plus-manual-attendees-view-missing-ticket', __( 'Invalid ticket set, please try again.', 'event-tickets-plus' ) );
		}

		if ( empty( $vars['tribe_tickets'][ $ticket_id ]['attendees'][ $attendee_id ] ) ) {
			return new WP_Error( 'tribe-tickets-plus-manual-attendees-view-missing-attendee-info', __( 'Invalid ticket and attendee information provided, please try again.', 'event-tickets-plus' ) );
		}

		$submission = $vars['tribe_tickets'][ $ticket_id ]['attendees'][ $attendee_id ];

		$attendee_name         = $submission['tribe-tickets-plus-ma-name'];
		$attendee_email        = $submission['tribe-tickets-plus-ma-email'];
		$attendee_email_resend = tribe_is_truthy( Arr::get( $submission, 'tribe-tickets-plus-ma-email-resend', false ) );
		$attendee_meta         = (array) Arr::get( $submission, 'meta', [] );

		if ( empty( $attendee_name ) || empty( $attendee_email ) ) {
			return new WP_Error( 'tribe-tickets-plus-manual-attendees-view-missing-attendee-info', __( 'You must provide an Attendee name and email address, please try again.', 'event-tickets-plus' ) );
		}

		// Set up the attendee data for the creation/save.
		$attendee_data = [
			'full_name'         => $attendee_name,
			'email'             => $attendee_email,
			'attendee_meta'     => $attendee_meta,
			'attendee_source'   => 'admin',
			'attendee_added_by' => get_current_user_id(),
		];

		// Handle editing attendees.
		if ( 'edit' === $step ) {
			$attendee_data['send_ticket_email'] = $attendee_email_resend;

			// Check if attendee ID is set before updating.
			if ( empty( $attendee_id ) ) {
				return new WP_Error( 'tribe-tickets-plus-manual-attendees-view-edit-missing-attendee-id', __( 'Invalid attendee set, please try again.', 'event-tickets-plus' ) );
			}

			$attendee = $provider->update_attendee( $attendee_id, $attendee_data );

			// Check if attendee was updated.
			if ( false === $attendee ) {
				return new WP_Error( 'tribe-tickets-plus-manual-attendees-view-edit-attendee-update-error', __( 'Unable to update this attendee, please try again.', 'event-tickets-plus' ) );
			}
		} elseif ( 'add' === $step ) {
			/**
			 * Allow filtering whether to send the ticket email for new attendees (default: true).
			 *
			 * @since 5.2.2
			 *
			 * @param bool                           $send_ticket_email Whether to send the ticket email for new attendees (default: true).
			 * @param \Tribe__Tickets__Ticket_Object $ticket            The ticket object.
			 */
			$send_ticket_email = apply_filters( 'tribe_tickets_plus_manual_attendees_view_send_ticket_email_for_new_attendees', true, $ticket, $attendee_data );

			$attendee_data['send_ticket_email'] = $send_ticket_email;

			// Handle adding attendees.
			$attendee = $provider->create_attendee( $ticket, $attendee_data );

			// Check if attendee was created.
			if ( false === $attendee ) {
				return new WP_Error( 'tribe-tickets-plus-manual-attendees-view-add-attendee-creation-error', __( 'Unable to create this attendee, please try again.', 'event-tickets-plus' ) );
			}

			// Set the attendee ID.
			$response_args['attendee_id'] = $attendee->ID;
		}

		$response_args['attendee'] = $attendee;

		return $response_args;
	}

	/**
	 * Get the Manual Attendees template args to include as part of the template global vars.
	 *
	 * @param array $vars The request vars.
	 *
	 * @return array $template_args The array used as the template global vars.
	 */
	public function get_form_template_args( $vars = [] ) {
		$post_id     = (int) $vars['eventId'];
		$ticket_id   = (int) $vars['ticketId'];
		$attendee_id = (int) $vars['attendeeId'];

		$tickets = [];
		$ticket  = null;

		if ( 0 < $post_id ) {
			$tickets = \Tribe__Tickets__Tickets::get_all_event_tickets( $post_id );
		}

		$multiple_tickets = 1 < count( $tickets );

		// If there's only one ticket, always default to the first ticket as selected.
		if ( ! $multiple_tickets && $tickets ) {
			$ticket = reset( $tickets );
		}

		$ticket_id = $ticket ? $ticket->ID : (int) $ticket_id;

		if ( $ticket ) {
			// If we have a ticket, get the provider directly from that object.
			$provider = $ticket->get_provider();
		} else {
			// Detect the provider from the ticket.
			$provider = false;

			// Only get the provider if the ticket ID is set.
			if ( $ticket_id ) {
				$provider = tribe_tickets_get_ticket_provider( $ticket_id );

				// Get the ticket from the provider if the provider was found.
				if ( $provider ) {
					$ticket = $provider->get_ticket( $post_id, $ticket_id );
				}
			}
		}

		// Auto-detect the post ID.
		if ( $ticket && empty( $post_id ) ) {
			$post_id = $ticket->get_event_id();
		}

		$is_rsvp = $provider && 'Tribe__Tickets__RSVP' === $provider->class_name;

		$attendee       = null;
		$attendee_name  = '';
		$attendee_email = '';
		$attendee_meta  = [];

		// Get attendee data if attendee ID provided.
		if ( 0 < $attendee_id && $provider ) {
			$attendee = $provider->get_attendee( $attendee_id, $post_id );

			// Check if attendee was found.
			if ( $attendee ) {
				$attendee_name  = $attendee['holder_name'];
				$attendee_email = $attendee['holder_email'];
				$attendee_meta  = $attendee['attendee_meta'];
			} else {
				// No attendee was found, make the attendee ID invalid.
				$attendee_id = null;
			}
		}

		$ticket_post = null;

		// Get the ticket post if there's a ticket.
		if ( $ticket_id ) {
			$ticket_post = get_post( $ticket_id );
		}

		/**
		 * This filter allows the admin to control the re-send email option when an attendee's email is updated.
		 *
		 * @since 5.1.0
		 *
		 * @param bool         $allow_resending_email Whether to allow email resending.
		 * @param WP_Post|null $ticket                The ticket post object if available, otherwise null.
		 * @param array|null   $attendee              The attendee information if available, otherwise null.
		 */
		$allow_resending_email = (bool) apply_filters( 'tribe_tickets_my_tickets_allow_email_resend_on_attendee_email_update', true, $ticket_post, $attendee );

		$step = 'tribe_tickets_manual_attendees_add' === $vars['request'] ? 'add' : 'edit';

		$template_args = [
			'post_id'               => $post_id,
			'step'                  => $step,
			'ticket'                => $ticket,
			'ticket_id'             => $ticket_id,
			'tickets'               => $tickets,
			'multiple_tickets'      => $multiple_tickets,
			'is_rsvp'               => $is_rsvp,
			'provider'              => $provider,
			'provider_class'        => $provider ? $provider->class_name : '',
			'provider_orm'          => $provider ? $provider->orm_provider : '',
			'currency'              => tribe( 'tickets.commerce.currency' ),
			'attendee'              => $attendee,
			'attendee_id'           => $attendee_id,
			'attendee_name'         => $attendee_name,
			'attendee_email'        => $attendee_email,
			'attendee_meta'         => $attendee_meta,
			'allow_resending_email' => $allow_resending_email,
		];

		return $template_args;
	}

	/**
	 * Return Get the Attendees "Edit attendee" form,
	 * it'll be invoked from the AJAX request.
	 *
	 * @since 5.2.0
	 *
	 * @param array $vars Array containing request vars.
	 *
	 * @return string The Manual Attendees "Edit Attendee" form HTML.
	 */
	public function get_form_edit( $vars ) {
		$template_args = $this->get_form_template_args( $vars );

		/** @var \Tribe__Tickets_Plus__Admin__Views $template */
		$template = tribe( 'tickets-plus.admin.views' );

		// Add the rendering attributes into global context.
		$template->add_template_globals( $template_args );

		return $template->template( 'manual-attendees/edit', $template_args, false );
	}

	/**
	 * Get the Manual Attendees "Add Attendee" form,
	 * it'll be invoked from the AJAX request.
	 *
	 * @since 5.2.0
	 *
	 * @param array $vars Array containing request vars.
	 *
	 * @return string The Manual Attendees "Add Attendee" form HTML.
	 */
	public function get_form_add( $vars ) {
		$template_args = $this->get_form_template_args( $vars );

		/** @var \Tribe__Tickets_Plus__Admin__Views $template */
		$template = tribe( 'tickets-plus.admin.views' );

		// Add the rendering attributes into global context.
		$template->add_template_globals( $template_args );

		return $template->template( 'manual-attendees/add', $template_args, false );
	}
}
