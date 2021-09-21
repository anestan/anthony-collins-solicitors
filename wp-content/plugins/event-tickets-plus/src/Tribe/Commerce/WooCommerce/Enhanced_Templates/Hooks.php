<?php

namespace Tribe\Tickets\Plus\Commerce\WooCommerce\Enhanced_Templates;

/**
 * Class Hooks
 *
 * @since 5.2.7
 *
 * @package Tribe\Tickets\Plus\Commerce\WooCommerce\Enhanced_Templates
 */
class Hooks {
	/**
	 * Echoes the attendee meta when attached to relevant WooCommerce action.
	 *
	 * @see action woocommerce_order_item_meta_start
	 * @see Tribe__Tickets_Plus__Commerce__WooCommerce__Main::get_event_for_ticket()
	 *
	 * @since 5.2.7
	 */
	public function woocommerce_echo_event_info( $item_id, $item, $order ) {
		if (
			! $item instanceof \WC_Order_Item_Product
			|| ! $order instanceof \WC_Order
		) {
			return;
		}

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $wootix */
		$wootix = tribe( 'tickets-plus.commerce.woo' );

		$item_data = $item->get_data();

		// This is either true or a WP_Post, such as for any enabled post type (such as a ticket on a Page), not just for Tribe Events.
		$event = $wootix->get_event_for_ticket( $item_data['product_id'] );

		// Bail if no connected post, since it's required of a WooCommerce Ticket but not of all WooCommerce Products
		if ( empty( $event ) ) {
			return;
		}

		// Show event details if this ticket is for a tribe event.
		if (
			$event instanceof \WP_Post
			&& class_exists( 'Tribe__Events__Main' )
			&& \Tribe__Events__Main::POSTTYPE === $event->post_type
		) {
			$event_time       = tribe_events_event_schedule_details( $event, '<em>', '</em>' );
			$event_venue_name = tribe_get_venue( $event );
			$event_address    = tribe_get_full_address( $event );
			$event_details    = [];

			// Output event title in same format as Community Tickets.
			$event_details[] = sprintf(
				'<a href="%1$s" class="event-title">%2$s</a>',
				esc_attr( get_permalink( $event ) ),
				esc_html( get_the_title( $event ) )
			);

			if ( ! empty( $event_time ) ) {
				$event_details[] = $event_time;
			}

			if ( ! empty( $event_venue_name ) ) {
				$event_details[] = $event_venue_name;
			}

			if ( ! empty( $event_address ) ) {
				$event_details[] = $event_address;
			}

			printf(
				'<div class="tribe-event-details">%1$s</div>',
				implode( '<br />', $event_details )
			);
		}

		/**
		 * Allow filtering to enable/disable the Attendee Meta data.
		 *
		 * @since 5.2.8
		 *
		 * @param boolean Enable or Disable the Attendee Meta data. Defaults to true.
		 */
		$data_filter = apply_filters( 'tribe_tickets_plus_woo_meta_data_enabled', true );

		// Bail if $data_filter is empty or false.
		if ( empty( $data_filter ) ) {
			return;
		}
		$this->echo_attendee_meta( $order->get_id(), $item_data['product_id'] );
	}

	/**
	 * Echoes attendee meta for every attendee in selected order
	 *
	 * @since 5.2.7
	 *
	 * @param string $order_id  Order or RSVP post ID.
	 * @param string $ticket_id The specific ticket to output attendees for.
	 */
	protected function echo_attendee_meta( $order_id, $ticket_id = null ) {

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $woo_provider */
		$woo_provider = tribe( 'tickets-plus.commerce.woo' );
		$attendees    = $woo_provider->get_attendees_by_id( $order_id );

		foreach ( $attendees as $attendee ) {
			// Skip attendees that are not for this ticket type.
			if ( ! empty( $ticket_id ) && $ticket_id != $attendee['product_id'] ) {
				continue;
			}

			$table_columns = [];

			$table_columns[] = [
				sprintf(
					'<strong class="tribe-attendee-meta-heading">%1$s</strong>',
					esc_html_x( 'Ticket ID', 'Attendee meta table.', 'event-tickets-plus' )
				),
				sprintf(
					'<strong class="tribe-attendee-meta-heading">%1$s</strong>',
					esc_html( $attendee['ticket_id'] )
				),
			];

			$table_columns = $this->maybe_add_iac_data( $attendee, $table_columns );

			$attendee_meta_data = $this->get_attendee_meta( $attendee['product_id'], $attendee['qr_ticket_id'] );

			/**
			 * Allow filtering for the Attendee meta data.
			 *
			 * @since 5.2.8
			 *
			 * @param array $fields Array of attendee meta data.
			 */
			$fields = apply_filters( 'tribe_tickets_plus_woo_meta_data_filter', $attendee_meta_data );


			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					$table_columns[] = [
						esc_html( $field['label'] ),
						esc_html( $field['value'] ),
					];
				}
			}

			$table_columns[] = [
				esc_html_x( 'Security Code', 'Attendee meta table.', 'event-tickets-plus' ),
				esc_html( $attendee['security_code'] ),
			];

			$table                        = new \Tribe__Simple_Table( $table_columns );
			$table->html_escape_td_values = false;
			$table->table_attributes      = [
				'class' => 'tribe-attendee-meta',
			];

			echo wp_kses_post( $table->output_table() );
		}
	}

	/**
	 * Include attendee IAC data if available.
	 *
	 * @since 5.2.7
	 *
	 * @param array $attendee Attendee data.
	 * @param array $table_columns Table columns.
	 *
	 * @return array $table_columns
	 */
	protected function maybe_add_iac_data( $attendee, $table_columns ) {

		/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
		$iac            = tribe( 'tickets-plus.attendee-registration.iac' );
		$iac_for_ticket = $iac->get_iac_setting_for_ticket( $attendee['product_id'] );
		$iac_enabled    = $iac_for_ticket === $iac::ALLOWED_KEY || $iac_for_ticket === $iac::REQUIRED_KEY;

		if ( ! $iac_enabled ) {
			return $table_columns;
		}

		$table_columns[] = [
			sprintf(
				'<p class="tribe-attendee-meta-iac-name">%1$s</p>',
				esc_html_x( 'Name', 'Attendee meta table.', 'event-tickets-plus' )
			),
			sprintf(
				'<p class="tribe-attendee-meta-heading">%1$s</p>',
				esc_html( $attendee['holder_name'] )
			),
		];

		$table_columns[] = [
			sprintf(
				'<p class="tribe-attendee-meta-iac-email">%1$s</p>',
				esc_html_x( 'Email', 'Attendee meta table.', 'event-tickets-plus' )
			),
			sprintf(
				'<p class="tribe-attendee-meta-heading">%1$s</p>',
				esc_html( $attendee['holder_email'] )
			),
		];

		return $table_columns;
	}

	/**
	 * Get attendee meta
	 *
	 * @since 5.2.7
	 *
	 * @param string $ticket_id    Ticket ID.
	 * @param string $qr_ticket_id QR Ticket ID.
	 *
	 * @return array Attendee meta array.
	 */
	protected function get_attendee_meta( $ticket_id, $qr_ticket_id ) {
		$output = [];

		$meta_fields = \Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_ticket( $ticket_id );
		$meta_data   = get_post_meta( $qr_ticket_id, \Tribe__Tickets_Plus__Meta::META_KEY, true );

		foreach ( $meta_fields as $field ) {

			if ( 'checkbox' === $field->type && isset( $field->extra['options'] ) ) {
				$values = [];

				foreach ( $field->extra['options'] as $option ) {
					if ( '' === $option ) {
						continue;
					}

					// Support longer options by using the hash of the string.
					$key = $field->slug . '_' . md5( sanitize_title( $option ) );

					if ( ! isset( $meta_data[ $key ] ) ) {
						// Support existing fields that did not save with md5 hash.
						$key = $field->slug . '_' . sanitize_title( $option );
					}

					if ( isset( $meta_data[ $key ] ) ) {
						$values[] = $meta_data[ $key ];
					}
				}

				// There were no values for this checkbox.
				if ( empty( $values ) ) {
					continue;
				}

				$value = implode( ', ', $values );
			} elseif ( isset( $meta_data[ $field->slug ] ) ) {
				$value = $meta_data[ $field->slug ];
			} else {
				continue;
			}

			if ( '' === trim( $value ) ) {
				$value = '&nbsp;';
			}

			if ( ! empty( $value ) ) {
				$output[ $field->slug ] = [
					'slug'  => $field->slug,
					'label' => $field->label,
					'value' => $value,
				];
			}
		}

		return $output;
	}

	/**
	 * Adds the Event Title column header on WooCommerce Order Items table.
	 *
	 * @since 5.2.7
	 *
	 * @param \WC_Order $order Order Object.
	 */
	public function add_event_title_header( $order ) {

		if ( ! $this->should_render_event_column( $order ) ) {
			return;
		}

		?>
		<th class="item_event sortable" data-sort="string-ins">
			<?php esc_html_e( 'Event', 'event-tickets-plus' ); ?>
		</th>
		<?php
	}

	/**
	 * Add Event Link for Order Items.
	 *
	 * @since 5.2.7
	 *
	 * @param \WC_Product $product The Product object.
	 * @param \WC_Order_Item_Product $item The Order Item object.
	 * @param string $item_id Item ID.
	 */
	public function add_event_title_for_order_item( $product, $item, $item_id ) {

		if ( ! is_object( $product ) ) {
			return;
		}

		if ( ! $this->should_render_event_column( $item->get_order() ) ) {
			return;
		}

		$event_id   = $product->get_meta( '_tribe_wooticket_for_event' );
		$event_post = ! empty( $event_id ) ? get_post( $event_id ) : '' ;
		$event      = ! empty( $event_post ) ? $event_post->post_title : '';
		$schedule   = function_exists( 'tribe_events_event_schedule_details' ) ? tribe_events_event_schedule_details( $event_post ) : '';
		$link       = sprintf( '<a target="_blank" rel="noopener nofollow" href="%s">%s</a> %s', get_permalink( $event_post ), esc_html( $event ), $schedule );

		?>
		<td class="item_event" width="15%" data-sort-value="<?php echo esc_attr( $event ) ?>">
			<?php echo wp_kses_post( $link ); ?>
		</td>
		<?php
	}

	/**
	 * Add attendee data to Order Item view.
	 *
	 * @since 5.2.7
	 *
	 * @param string $item_id Order Item ID.
	 * @param \WC_Order_Item $item Order Item.
	 * @param \WC_Product $product WooCommerce Product Object.
	 */
	public function add_attendee_data_for_order_item( $item_id, $item, $product ) {

		if ( ! is_object( $product ) ) {
			return;
		}

		if ( ! $this->should_render_event_column( $item->get_order() ) ) {
			return;
		}

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $woo_provider */
		$woo_provider = tribe( 'tickets-plus.commerce.woo' );

		$ticket_id = $product->get_id();

		$attendees_orm = tribe_attendees( $woo_provider->orm_provider );

		$attendees_orm->by( 'order', $item->get_order_id() )
		              ->by( 'ticket', $product->get_id() )
		              ->by( 'status', [ 'publish', 'trash' ] );

		$attendees = $woo_provider->get_attendees_from_module( $attendees_orm->all() );

		foreach ( $attendees as $attendee ) {
			// Skip attendees that are not for this ticket type.
			if ( ! empty( $ticket_id ) && $ticket_id != $attendee['product_id'] ) {
				continue;
			}

			$deleted_class = get_post_status( $attendee['attendee_id'] ) === 'trash' ? 'deleted' : '';
			$deleted_label = ! empty( $deleted_class ) ? __( '( Deleted )', 'event-tickets-plus' ) : '';

			$table_columns = [];

			$table_columns[] = [
				sprintf(
					'<strong class="tribe-attendee-meta-heading">%1$s</strong>',
					esc_html_x( 'Ticket ID', 'Attendee meta table.', 'event-tickets-plus' )
				),
				sprintf(
					'<strong class="tribe-attendee-meta-heading">%1$s</strong>',
					esc_html( $attendee['ticket_id'] . ' ' . $deleted_label )
				),
			];

			$table_columns[] = [
				esc_html_x( 'Name', 'Attendee meta table.', 'event-tickets-plus' ),
				esc_html( $attendee[ 'holder_name' ] ),
			];

			$table_columns[] = [
				esc_html_x( 'Email', 'Attendee meta table.', 'event-tickets-plus' ),
				esc_html( $attendee[ 'holder_email' ] ),
			];

			$fields = $this->get_attendee_meta( $attendee['product_id'], $attendee['qr_ticket_id'] );

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					$table_columns[] = [
						esc_html( $field['label'] ),
						esc_html( $field['value'] ),
					];
				}
			}

			$table_columns[] = [
				esc_html_x( 'Security Code', 'Attendee meta table.', 'event-tickets-plus' ),
				esc_html( $attendee['security_code'] ),
			];

			$table                        = new \Tribe__Simple_Table( $table_columns );
			$table->html_escape_td_values = false;
			$table->table_attributes      = [
				'class' => 'tribe-attendee-meta ' . $deleted_class,
			];

			echo wp_kses_post( $table->output_table() );
		}
	}

	/**
	 * Add inline styles for Attendee Table for Order Items.
	 *
	 * @since 5.2.7
	 */
	function admin_order_table_styles() {

		$custom_css = '
                table.tribe-attendee-meta td:first-child {
	                padding-left: 0 !important;
                }
                table.tribe-attendee-meta td {
	                padding: 5px 10px !important;
                }
                table.tribe-attendee-meta.deleted {
	                color: #a00 !important;
                }
                ';
		wp_add_inline_style( 'event-tickets-admin-css', $custom_css );
	}

	/**
	 * Check if we have Tickets in Order.
	 *
	 * @since 5.2.7
	 *
	 * @param \WC_Order $order The Order object.
	 */
	public function should_render_event_column( $order ) {

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $woo_provider */
		$woo_provider = tribe( 'tickets-plus.commerce.woo' );

		return (bool) $order->get_meta( $woo_provider->order_has_tickets );
	}
}