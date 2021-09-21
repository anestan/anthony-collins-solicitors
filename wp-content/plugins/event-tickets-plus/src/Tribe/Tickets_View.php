<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Tickets_Plus__Tickets_View {

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 *
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance instanceof self ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Hook the necessary filters and Actions!
	 *
	 * @static
	 * @return self
	 */
	public static function hook() {
		$myself = self::instance();

		add_action( 'event_tickets_after_attendees_update', [ $myself, 'save_meta' ] );
		add_action( 'wp_ajax_tribe-tickets-save-attendee-info', [ $myself, 'save_attendee_info' ] );
		add_action( 'wp_ajax_nopriv_tribe-tickets-save-attendee-info', [ $myself, 'save_attendee_info' ] );
		add_action( 'event_tickets_orders_attendee_contents', [ $myself, 'output_attendee_meta' ] );
		add_filter( 'tribe_tickets_template_paths', [ $myself, 'add_template_path' ] );
		add_action( 'tribe_tickets_orders_rsvp_item', [ $myself, 'add_meta_to_rsvp' ], 10, 2 );
		add_action( 'tribe_tickets_orders_before_submit', [ $myself, 'output_ticket_order_form' ] );
		add_action( 'event_tickets_user_details_rsvp', [ $myself, 'output_attendee_list_checkbox' ], 10, 2 );
		add_action( 'event_tickets_user_details_tickets', [ $myself, 'output_attendee_list_checkbox' ], 10, 2 );

		return $myself;
	}

	/**
	 * Filter template paths to add the ET+ paths
	 *
	 * @param  array $paths
	 * @return array $paths
	 */
	public function add_template_path( $paths ) {
		$paths['plus'] = Tribe__Tickets_Plus__Main::instance()->plugin_path;
		return $paths;
	}

	/**
	 * Updates attendee meta from ajax request
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function save_attendee_info() {
		if (
			! isset( $_POST['nonce'] )
			|| ! wp_verify_nonce( $_POST['nonce'], 'save_attendee_info' )
		) {
			wp_send_json_error( null, 403 );
		}

		if ( empty( $_POST['event_id'] ) ) {
			wp_send_json_error( null, 400 );
		}

		/*
		 * There are hooks on wp_loaded (See various process_front_end_tickets_form methods) that handle saving of the
		 * ticket meta from $_POST by Tribe__Tickets_Plus__Meta__Storage::maybe_set_attendee_meta_cookie.
		 */

		/**
		 * Get all tickets currently in the cart.
		 *
		 * @since 4.9
		 *
		 * @param array $tickets Array indexed by ticket id with quantity as the value
		 *
		 * @return array
		 */
		$tickets_in_cart = apply_filters( 'tribe_tickets_tickets_in_cart', [] );

		/** @var Tribe__Tickets_Plus__Meta__Contents $contents */
		$contents = tribe( 'tickets-plus.meta.contents' );

		$meta_up_to_date = $contents->is_stored_meta_up_to_date( $tickets_in_cart );

		wp_send_json_success( [
			'meta_up_to_date' => $meta_up_to_date,
		] );
	}

	/**
	 * Saves the Attendee Information from the front-end My Tickets editing form.
	 *
	 * @since 4.10.7 Handles hashed input names (Checkboxes and Radios since 4.10.2)
	 *              and only updates post_meta if there's a change detected.
	 *
	 * @param int $event_id The event this change applies to.
	 */
	public function save_meta( $event_id ) {
		$user_id = get_current_user_id();

		// This only runs for Tickets
		if ( isset( $_POST['attendee'] ) && ! empty( $_POST['event_id'] ) ) {
			$event_id = absint( $_POST['event_id'] );

			$attendees_by_order = $this->get_event_attendees_by_order( $event_id, $user_id );

			foreach ( $_POST['attendee'] as $order_id => $order_data ) {
				if ( ! isset( $attendees_by_order[ $order_id ] ) ) {
					continue;
				}

				$first_attendee = reset( $attendees_by_order[ $order_id ] );

				if ( ! isset( $first_attendee['provider'] ) ) {
					continue;
				}

				$optout = isset( $_POST['optout'][ $order_id ] ) ? $_POST['optout'][ $order_id ] : 0;
				$optout = filter_var( $optout, FILTER_VALIDATE_BOOLEAN );
				$optout = (int) $optout;

				foreach ( $attendees_by_order[ $order_id ] as $attendee ) {
					if ( $user_id !== (int) $attendee['user_id'] ) {
						continue;
					}

					$provider = Tribe__Tickets__Tickets::get_ticket_provider_instance( $attendee['provider'] );

					if ( empty( $provider ) ) {
						continue;
					}

					$attendee_optout_key = $provider::get_attendee_optout_key( $provider );

					if ( ! empty( $attendee_optout_key ) ) {
						update_post_meta( $attendee['attendee_id'], $attendee_optout_key, $optout );
					}

					/**
					 * Allow hooking into after the attendee data has been updated.
					 *
					 * @since 5.1.0
					 *
					 * @param int|null                $attendee_id               The attendee ID.
					 * @param int                     $order_id                  The order ID.
					 * @param int                     $ticket_id                 The ticket ID.
					 * @param int                     $post_id                   The ID of the post associated to the ticket.
					 * @param Tribe__Tickets__Tickets $provider                  The current ticket provider object.
					 */
					do_action( 'tribe_tickets_plus_attendee_update', $attendee['attendee_id'], $order_id, $attendee['product_id'], $attendee['event_id'], $provider );
				}
			}
		}

		// If we don't have the Meta we skip the rest
		if ( empty( $_POST['tribe-tickets-meta'] ) ) {
			return;
		}

		$attendees_data = $_POST['tribe-tickets-meta'];

		foreach ( $attendees_data as $attendee_id => $data ) {
			$attendee_owner = $this->get_attendee_owner( $attendee_id );

			// Only saves if this user is the owner
			if ( $user_id !== $attendee_owner ) {
				continue;
			}

			/**
			 * Allow developers to prevent users to update specific Attendees or Events
			 *
			 * @param boolean $is_meta_update_allowed If is allowed or not
			 * @param int     $event_id               Which event this applies to
			 * @param int     $attendee_id            Which attendee this update will be done to
			 * @param array   $data                   Data that will be saved
			 */
			$is_meta_restricted = apply_filters( 'event_tickets_plus_is_meta_restricted', false, $event_id, $attendee_id, $data );

			// Just skip if this is not allowed
			if ( $is_meta_restricted ) {
				continue;
			}

			$args = [
				'by' => [
					'id' => $attendee_id,
				],
			];

			$attendee_data = Tribe__Tickets__Tickets::get_event_attendees_by_args( $event_id, $args );

			// Attendee not found.
			if ( ! isset( $attendee_data['attendees'] ) ) {
				continue;
			}

			$attendee = current( $attendee_data['attendees'] );

			$fields = Tribe__Tickets_Plus__Meta::instance()->get_meta_fields_by_ticket( $attendee['product_id'] );

			foreach ( $fields as $field ) {
				if ( ! $field instanceof Tribe__Tickets_Plus__Meta__Field__Abstract_Field ) {
					continue;
				}

				// If a field is restricted, do not allow changing its data
				if ( $field->is_restricted( $attendee_id ) ) {
					continue;
				}

				if (
					'checkbox' === $field->type
					|| 'radio' === $field->type
				) {
					$map = $field->get_hashed_options_map();

					reset( $data );
					foreach ( $data as $key => $value ) {
						if ( array_key_exists( $key, $map ) ) {
							$data[ $field->slug . '_' . sanitize_title( $value ) ] = $map[$key];
							unset( $data[$key] );
						}

						// Remove hidden field from Checkbox template that sends an empty field to ensure empty saves work for AJAX
						if (
							0 === $key
							&& '' === $value
						) {
							unset( $data[$key] );
						}
					}
				}
			}

			$values = (array) get_post_meta( $attendee_id, Tribe__Tickets_Plus__Meta::META_KEY, true );

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
			$data_to_save = apply_filters( 'tribe_tickets_plus_attendee_save_meta', $data, $attendee_id, $attendee['order_id'], $attendee['product_id'], null );

			// Only write to database if arrays do not match, regardless of the order of keys
			if ( $data_to_save != $values ) {
				// Updates the meta information associated with individual attendees
				update_post_meta( $attendee_id, Tribe__Tickets_Plus__Meta::META_KEY, $data_to_save );

				/**
				 * An Action fired when an Attendees Meta Data is Updated.
				 *
				 * @since 4.11.0
				 *
				 * @param array $data        An array of attendee meta that was saved for the attendee.
				 * @param int   $attendee_id the ID of an attendee.
				 * @param int   $event_id    the ID of an event.
				 */
				do_action( 'event_tickets_plus_attendee_meta_update', $data_to_save, $attendee_id, $event_id );
			}

			$provider = Tribe__Tickets__Tickets::get_ticket_provider_instance( $attendee['provider'] );

			/**
			 * Allow hooking into after the attendee update is completed.
			 *
			 * @since 5.1.0
			 *
			 * @param int|null                $attendee_id  The attendee ID.
			 * @param array                   $data_to_save The data that was saved.
			 * @param array                   $data         The data prior to filtering for saving.
			 * @param int                     $order_id     The order ID.
			 * @param int                     $ticket_id    The ticket ID.
			 * @param int                     $post_id      The ID of the post associated to the ticket.
			 * @param Tribe__Tickets__Tickets $provider     The current ticket provider object.
			 */
			do_action( 'tribe_tickets_plus_after_my_tickets_attendee_update', $attendee_id, $data_to_save, $data, $attendee['order_id'], $attendee['product_id'], $event_id, $provider );
		}

		/**
		 * Allow hooking into after the attendee updates are all completed.
		 *
		 * @since 5.1.0
		 *
		 * @param int $post_id The ID of the post associated to the ticket.
		 */
		do_action( 'tribe_tickets_plus_after_my_tickets_attendee_updates', $event_id );
	}

	/**
	 * Add the template for Editing Meta on an RSVP.
	 *
	 * @param array $attendee The attendee information.
	 * @param int   $i        The attendee index position.
	 */
	public function add_meta_to_rsvp( $attendee, $i ) {
		/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$ticket = get_post( $attendee['product_id'] );

		/**
		 * This filter allows the admin to control the re-send email option when an attendee's email is updated.
		 *
		 * @since 5.1.0
		 *
		 * @param bool         $allow_resending_email Whether to allow email resending.
		 * @param WP_Post|null $ticket                The ticket post object if available, otherwise null.
		 * @param array|null   $attendee              The attendee information if available, otherwise null.
		 */
		$allow_resending_email = (bool) apply_filters( 'tribe_tickets_my_tickets_allow_email_resend_on_attendee_email_update', true, $ticket, $attendee );

		$args = [
			'order_id'                    => $attendee['order_id'],
			'order'                       => $attendee,
			'attendee'                    => $attendee,
			'i'                           => $i,
			'ticket'                      => $ticket,
			'field_slug_for_resend_email' => $iac->get_iac_ticket_field_slug_for_resend_email(),
			'allow_resending_email'       => $allow_resending_email,
		];

		tribe_tickets_get_template_part( 'tickets-plus/orders-edit-meta', null, $args );
	}

	/**
	 * Outputs custom attendee meta for RSVP attendee order records
	 *
	 * @param array $attendee The attendee information.
	 */
	public function output_attendee_meta( $attendee ) {
		/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$ticket = get_post( $attendee['product_id'] );

		/**
		 * This filter allows the admin to control the re-send email option when an attendee's email is updated.
		 *
		 * @since 5.1.0
		 *
		 * @param bool         $allow_resending_email Whether to allow email resending.
		 * @param WP_Post|null $ticket                The ticket post object if available, otherwise null.
		 * @param array|null   $attendee              The attendee information if available, otherwise null.
		 */
		$allow_resending_email = (bool) apply_filters( 'tribe_tickets_my_tickets_allow_email_resend_on_attendee_email_update', true, $ticket, $attendee );

		$args = [
			'attendee'                    => $attendee,
			'ticket'                      => $ticket,
			'field_slug_for_resend_email' => $iac->get_iac_ticket_field_slug_for_resend_email(),
			'allow_resending_email'       => $allow_resending_email,
		];

		tribe_tickets_get_template_part( 'tickets-plus/orders-edit-meta', null, $args );
	}

	/**
	 * Gets an attendee owner from attendee meta
	 *
	 * @param int $attendee_id The Attendee ID
	 *
	 * @return int
	 */
	public function get_attendee_owner( $attendee_id ) {
		return (int) get_post_meta( $attendee_id, Tribe__Tickets__Tickets::ATTENDEE_USER_ID, true );
	}

	/**
	 * Fetches from the Cached attendees list the ones that are relevant for this user and event
	 * Important to note that this method will bring the attendees organized by order id
	 *
	 * @param  int       $event_id      The Event ID it relates to
	 * @param  int|null  $user_id       An Optional User ID
	 * @param  boolean   $include_rsvp  If this should include RSVP, which by default is false
	 * @return array                    List of Attendees grouped by order id
	 */
	public function get_event_attendees_by_order( $event_id, $user_id = null, $include_rsvp = false ) {
		if ( ! $user_id ) {
			$attendees = Tribe__Tickets__Tickets::get_event_attendees( $event_id );
		} else {
			// If we have a user_id then limit by that.
			$args = [
				'user' => $user_id,
			];

			$attendee_data = Tribe__Tickets__Tickets::get_event_attendees_by_args( $event_id, $args );

			$attendees = $attendee_data['attendees'];
		}

		$orders = [];

		foreach ( $attendees as $attendee ) {
			// Ignore RSVP if we don't tell it specifically
			if (
				'rsvp' === $attendee['provider_slug']
				&& ! $include_rsvp
			) {
				continue;
			}

			$orders[ (int) $attendee['order_id'] ][] = $attendee;
		}

		return $orders;
	}

	/**
	 * Outputs tickets form
	 *
	 */
	public function output_ticket_order_form() {
		tribe_tickets_get_template_part( 'tickets-plus/orders-tickets' );
	}

	/**
	 * Outputs the attendee list checkbox
	 *
	 */
	public function output_attendee_list_checkbox( $attendee_group, $post_id ) {
		$first_attendee = reset( $attendee_group );

		$args = [
			'attendee_group' => $attendee_group,
			'post_id'        => $post_id,
			'first_attendee' => $first_attendee,
		];

		if ( doing_action( 'event_tickets_user_details_rsvp' ) ) {
			$template_part = 'tickets-plus/attendee-list-checkbox-rsvp';
		} else {
			$template_part = 'tickets-plus/attendee-list-checkbox-tickets';
		}
		tribe_tickets_get_template_part( $template_part, null, $args );
	}
}
