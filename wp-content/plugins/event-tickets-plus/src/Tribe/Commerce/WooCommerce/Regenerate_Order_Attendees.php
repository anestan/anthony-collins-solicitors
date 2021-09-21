<?php

namespace Tribe\Tickets\Plus\Commerce\WooCommerce;

/**
 * Class Regenerate_Order_Attendees
 *
 * @package Tribe\Tickets\Plus\Commerce\WooCommerce
 *
 * @since 5.2.7
 */
class Regenerate_Order_Attendees {

	/**
	 * Slug text for registering actions.
	 *
	 * @since 5.2.7
	 *
	 * @var string
	 */
	public $action_slug = 'tec_event_tickets_plus_wc_force_regenerate_attendees';

	/**
	 * Register services.
	 *
	 * @since 5.2.7
	 */
	public function hook() {
		// Register the action for the Edit order screen.
		add_filter( 'woocommerce_order_actions', [ $this, 'add_single_order_action' ] );

		// Register the bulk action for the Orders screen.
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'add_bulk_order_action' ] );

		// Handler action for order edit screen.
		add_action( 'woocommerce_order_action_' . $this->action_slug, [ $this, 'regenerate_attendees' ] );

		// Handle bulk action for order list.
		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this, 'bulk_handler_for_attendee_regeneration' ], 9, 3 );

		// Show bulk order update confirmation.
		add_action( 'admin_notices', [ $this, 'display_bulk_action_confirmation_notice' ] );
	}

	/**
	 * Handle action for attendee regeneration.
	 *
	 * @since 5.2.7
	 *
	 * @param \WC_Order $order
	 */
	public function regenerate_attendees( \WC_Order $order ) {

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $commerce_woo */
		$commerce_woo = tribe( 'tickets-plus.commerce.woo' );

		$this->force_regenerate_attendees_for_order( $commerce_woo, $order );
	}

	/**
	 * Register the custom action to the list of single order edit actions.
	 *
	 * @since 5.2.7
	 *
	 * @param array $actions List of order actions.
	 *
	 * @return array List of order actions with new action registered.
	 */
	public function add_single_order_action( array $actions ) {

		$order_id = (int) tribe_get_request_var( 'post' );
		$order    = wc_get_order( $order_id );

		// Bail if the order is empty.
		if ( empty( $order ) ) {
			return $actions;
		}

		if ( ! $this->order_has_tickets( $order ) ) {
			return $actions;
		}

		$actions[ $this->action_slug ] = __( 'Regenerate Attendees', 'event-tickets-plus' );

		return $actions;
	}

	/**
	 * Register the custom action to the list of bulk order actions.
	 *
	 * @since 5.2.7
	 *
	 * @param array $actions List of order actions.
	 *
	 * @return array List of order actions with new action registered.
	 */
	public function add_bulk_order_action( array $actions ) {

		$actions[ $this->action_slug ] = __( 'Regenerate Attendees', 'event-tickets-plus' );

		return $actions;
	}

	/**
	 * Handle regenerating of attendees for an order.
	 *
	 * @since 5.2.7
	 *
	 * @param \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $commerce_woo The Event Tickets Plus commerce provider for WooCommerce.
	 * @param \WC_Order                                         $order        The WooCommerce order object.
	 */
	public function force_regenerate_attendees_for_order( \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $commerce_woo, \WC_Order $order ) {
		$order_id = $order->get_id();

		// Delete existing attendees for this order.
		$this->remove_existing_attendees_by_order( $order_id );

		// Remove the flag from the order meta that indicates the attendee is already generated.
		update_post_meta( $order_id, $commerce_woo->order_has_tickets, 0 );

		$commerce_woo->generate_tickets( $order_id );

		$order->add_order_note( __( 'Attendee Tickets were regenerated for this order.', 'event-tickets-plus' ) );
	}

	/**
	 * Regenerate bulk action for missing attendees.
	 *
	 * @since 5.2.7
	 *
	 * @param string $redirect_to The URL to redirect to.
	 * @param string $action      The bulk action name that is running.
	 * @param array  $ids         The list of Order ids.
	 *
	 * @return string The URL to redirect to.
	 */
	public function bulk_handler_for_attendee_regeneration( $redirect_to, $action, $ids ) {
		if ( $action !== $this->action_slug ) {
			return $redirect_to;
		}

		if ( empty( $ids ) ) {
			return $redirect_to;
		}

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $commerce_woo */
		$commerce_woo = tribe( 'tickets-plus.commerce.woo' );

		$changed = 0;

		foreach ( $ids as $id ) {
			$order = wc_get_order( $id );

			if ( ! $order ) {
				continue;
			}

			if ( ! $this->order_has_tickets( $order ) ) {
				continue;
			}

			$this->force_regenerate_attendees_for_order( $commerce_woo, $order );

			$changed ++;
		}

		if ( $changed ) {
			$args = [
				'post_type'   => $commerce_woo->order_object,
				'bulk_action' => $this->action_slug,
				'changed'     => $changed,
				'ids'         => implode( ',', $ids ),
			];

			$redirect_to = add_query_arg( $args, $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Show bulk action confirmation as admin notice.
	 *
	 * @since 5.2.7
	 */
	public function display_bulk_action_confirmation_notice() {
		global $post_type, $pagenow;

		if ( empty( $post_type ) || empty( $pagenow ) ) {
			return;
		}

		$bulk_action = tribe_get_request_var( 'bulk_action', false );

		// Bail out if not on shop order list page.
		if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type || ! $bulk_action ) {
			return;
		}

		$number      = tribe_get_request_var( 'changed', 0 );

		if ( $this->action_slug !== $bulk_action ) {
			return;
		}

		/* translators: %d: updated orders count */
		$message = sprintf( _n( '%d order has had attendees regenerated.', '%d orders have had their attendees regenerated.', $number, 'event-tickets-plus' ), number_format_i18n( $number ) );
		echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Check if the given order has attendee meta stored.
	 *
	 * @since 5.2.7
	 *
	 * @param \WC_Order $order The Order Object.
	 *
	 * @return bool
	 */
	public function order_has_tickets( \WC_Order $order ) {

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $commerce_woo */
		$commerce_woo = tribe( 'tickets-plus.commerce.woo' );

		return $commerce_woo->order_has_tickets( $order->get_id() );
	}

	/**
	 * Remove all attendees for given order.
	 *
	 * @since 5.2.7
	 *
	 * @param int $order_id The Order Id.
	 */
	public function remove_existing_attendees_by_order( $order_id ) {

		/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $commerce_woo */
		$woo_provider = tribe( 'tickets-plus.commerce.woo' );

		$attendees_orm = tribe_attendees( $woo_provider->orm_provider );

		$attendees_orm->by( 'order',$order_id )
		              ->by( 'status', [ 'publish', 'trash' ] );

		foreach ( $attendees_orm->get_ids() as $attendee ) {
			wp_delete_post( $attendee, true );
		}
	}
}
