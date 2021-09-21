<?php

if ( class_exists( 'Tribe__Tickets_Plus__Meta__RSVP' ) ) {
	return;
}

class Tribe__Tickets_Plus__Meta__RSVP {
	/**
	 * Hooks the actions and filters required by the class to work as intended.
	 *
	 * @since 4.7
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process_front_end_tickets_form' ], 50 );
		add_action( 'event_tickets_rsvp_ticket_created', [ $this, 'save_attendee_meta_to_ticket' ], 10, 4 );
		add_action( 'event_tickets_rsvp_tickets_generated_for_product', [ $this, 'clear_meta_for_ticket' ] );
		add_action( 'event_tickets_rsvp_after_ticket_row', [ $this, 'front_end_meta_fields' ], 10, 2 );
		add_action( 'tribe_template_entry_point:tickets/v2/rsvp/ari/form/fields/meta:rsvp_attendee_fields', [ $this, 'rsvp_attendee_fields' ], 10, 3 );
		add_action( 'tribe_template_entry_point:tickets/v2/rsvp/ari/form/template/fields:rsvp_attendee_fields_template', [ $this, 'rsvp_attendee_fields' ], 10, 3 );

		add_filter( 'tribe_tickets_rsvp_render_step_template_args_pre_process', [ $this, 'rsvp_render_ari_step' ] );
	}

	/**
	 * Sets attendee data on attendee posts
	 *
	 * @since 4.1
	 *
	 * @param int $attendee_id       Attendee Ticket Post ID.
	 * @param int $order_id          RSVP Order ID.
	 * @param int $product_id        RSVP Product ID.
	 * @param int $order_attendee_id Attendee number in submitted order.
	 */
	public function save_attendee_meta_to_ticket( $attendee_id, $order_id, $product_id, $order_attendee_id ) {
		$meta_object = Tribe__Tickets_Plus__Main::instance()->meta();

		// Build the custom meta data that will be stored in the order meta.
		if ( ! $meta = $meta_object->build_order_meta( array( $product_id ) ) ) {
			return;
		}

		if ( ! isset( $meta[ $product_id ] ) ) {
			return;
		}

		// Check if we are starting from zero.
		if ( isset( $meta[ $product_id ][0] ) ) {
			$order_attendee_id --;
		}

		if ( ! isset( $meta[ $product_id ][ $order_attendee_id ] ) ) {
			return;
		}

		$attendee_meta = $meta[ $product_id ][ $order_attendee_id ];

		/**
		 * Allow filtering the attendee meta to be saved to the attendee.
		 *
		 * @since 5.1.0
		 *
		 * @param array    $attendee_meta   The attendee meta to be saved to the attendee.
		 * @param int      $attendee_id     The attendee ID.
		 * @param int      $order_id        The order ID.
		 * @param int      $ticket_id       The ticket ID.
		 * @param int|null $attendee_number The order attendee number.
		 */
		$attendee_meta_to_save = apply_filters( 'tribe_tickets_plus_attendee_save_meta', $attendee_meta, $attendee_id, $order_id, $product_id, $order_attendee_id );

		update_post_meta( $attendee_id, Tribe__Tickets_Plus__Meta::META_KEY, $attendee_meta_to_save );
	}

	/**
	 * Clear meta for the tickets generated.
	 *
	 * @since 4.11.0
	 *
	 * @param int $product_id RSVP Product ID.
	 */
	public function clear_meta_for_ticket( $product_id ) {
		$meta_object = Tribe__Tickets_Plus__Main::instance()->meta();
		$meta_object->clear_meta_cookie_data( $product_id );
	}

	/**
	 * Outputs the meta fields for the ticket
	 */
	public function front_end_meta_fields( $post, $ticket ) {
		include Tribe__Tickets_Plus__Main::instance()->get_template_hierarchy( 'meta.php' );
	}

	/**
	 * Processes the front-end tickets form data.
	 */
	public function process_front_end_tickets_form() {
		$storage = new Tribe__Tickets_Plus__Meta__Storage();
		$storage->maybe_set_attendee_meta_cookie();
	}

	/**
	 * Outputs the meta fields for the RSVP ticket.
	 *
	 * @since 5.0.0
	 *
	 * @param string          $hook_name        For which template include this entry point belongs.
	 * @param string          $entry_point      Which entry point specifically we are triggering.
	 * @param Tribe__Template $tickets_template Current instance of the template class doing this entry point.
	 */
	public function rsvp_attendee_fields( $hook_name, $entry_point, $tickets_template ) {
		$rsvp        = $tickets_template->get( 'rsvp' );
		$post_id     = $tickets_template->get( 'post_id' );
		$attendee_id = 'rsvp_attendee_fields' === $entry_point ? 0 : null;
		$attendee_id = tribe_tickets_plus_meta_field_get_attendee_id( $attendee_id );

		/** @var \Tribe\Tickets\Plus\Attendee_Registration\Fields $fields */
		$fields = tribe( 'tickets-plus.attendee-registration.fields' );

		$fields->render( $rsvp, $post_id, $attendee_id );
	}

	/**
	 * Check if the RSVP has meta.
	 *
	 * @since 5.0.0
	 * @deprecated 5.1.0 Use `$rsvp->has_meta_enabled()` instead.
	 *
	 * @param Tribe__Tickets__Ticket_Object $rsvp The rsvp ticket object.
	 *
	 * @return bool Whether the RSVP has meta.
	 */
	public function rsvp_has_meta( $rsvp ) {
		return $rsvp->has_meta_enabled();
	}

	/**
	 * Handle rendering the ARI step if ticket has ARI.
	 *
	 * @since 5.0.0
	 *
	 * @param array $args {
	 *      The list of step template arguments.
	 *
	 *      @type int                           $rsvp_id    The RSVP ticket ID.
	 *      @type int                           $post_id    The ticket ID.
	 *      @type Tribe__Tickets__Ticket_Object $rsvp       The RSVP ticket object.
	 *      @type null|string                   $step       Which step being rendered.
	 *      @type boolean                       $must_login Whether login is required to register.
	 *      @type string                        $login_url  The site login URL.
	 *      @type int                           $threshold  The RSVP ticket threshold.
	 * }
	 *
	 * @return array
	 */
	public function rsvp_render_ari_step( array $args ) {
		// If not trying to make RSVP, return as normal.
		if ( 'success' === $args['step'] || 'going' !== $args['step'] ) {
			return $args;
		}

		/** @var Tribe__Tickets__Ticket_Object $rsvp */
		$rsvp = $args['rsvp'];

		// If no meta on ticket, return as normal.
		if ( ! $rsvp->has_meta_enabled() ) {
			return $args;
		}

		// Override the step as ARI.
		$args['step'] = 'ari';

		return $args;
	}
}
