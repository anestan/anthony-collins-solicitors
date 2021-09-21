<?php

use Tribe\Tickets\Plus\Commerce\WooCommerce\Attendee;
use Tribe\Tickets\Promoter\Triggers\Contracts\Attendee_Model;

/**
 * Class Tribe__Tickets_Plus__Commerce__WooCommerce__Promoter_Observer
 *
 * @since 4.12.0
 */
class Tribe__Tickets_Plus__Commerce__WooCommerce__Promoter_Observer {

	/**
	 * @since 4.12.0
	 *
	 * @var Tribe__Tickets__Promoter__Observer $observer ET Observer reference.
	 */
	private $observer;

	/**
	 * Tribe__Tickets_Plus__Commerce__WooCommerce__Promoter_Observer constructor.
	 *
	 * @param Tribe__Tickets__Promoter__Observer $observer ET Observer.
	 */
	public function __construct( Tribe__Tickets__Promoter__Observer $observer ) {
		$this->observer = $observer;
		$this->hook();
	}

	/**
	 * Hooks on which this observer notifies promoter.
	 *
	 * @since 4.12.0
	 */
	private function hook() {
		add_action( 'event_tickets_woocommerce_ticket_created', [ $this, 'ticket_created' ], 10, 4 );
		add_action( 'wootickets_checkin', [ $this, 'checkin' ], 10, 2 );
		add_action( 'wootickets_ticket_deleted', tribe_callback( 'tickets.promoter.observer', 'notify_event_id' ), 10, 2 );
		add_action( 'tribe_tickets_plus_woo_reset_attendee_cache', tribe_callback( 'tickets.promoter.observer', 'notify' ) );

		// The method might not exists if ET+ runs with previous version of ET.
		if ( method_exists( $this->observer, 'notify_ticket_event' ) ) {
			$this->notify_ticket_event();
		}
	}

	/**
	 * Listener for "wootickets_checkin" action.
	 *
	 * @since 4.12.3
	 *
	 * @param int       $attendee_id The ID of the attendee utilized.
	 * @param bool|null $qr          Whether it's from a QR scan.
	 */
	public function checkin( $attendee_id, $qr ) {
		$this->trigger( 'checkin', $attendee_id );
	}

	/**
	 * Action fired when an attendee ticket is generated.
	 *
	 * @since 4.12.3
	 *
	 * @param int $attendee_id       ID of attendee ticket.
	 * @param int $order_id          WooCommerce order ID.
	 * @param int $product_id        Product ID attendee is "purchasing".
	 * @param int $order_attendee_id Attendee # for order.
	 */
	public function ticket_created( $attendee_id, $order_id, $product_id, $order_attendee_id ) {
		$this->trigger( 'ticket_purchased', $attendee_id );
	}

	/**
	 * Deliver a trigger message for this specific provider.
	 *
	 * @since 4.12.3
	 *
	 * @param string $type        The type of trigger that is being fired.
	 * @param int    $attendee_id The ID of the attendee.
	 */
	private function trigger( $type, $attendee_id ) {
		/** @var Tribe__Tickets_Plus__Commerce__WooCommerce__Main $ticket */
		$ticket   = tribe( 'tickets-plus.commerce.woo' );
		$attendee = new Attendee( $ticket->get_attendee( $attendee_id ) );

		/**
		 * Dispatch a new trigger with an attendee.
		 *
		 * @since 4.12.3
		 *
		 * @param string                                           $type     The type of trigger that is being fired.
		 * @param Attendee_Model                                   $attendee The attendee model object.
		 * @param Tribe__Tickets_Plus__Commerce__WooCommerce__Main $ticket   The WooCommerce ticket object.
		 */
		do_action( 'tribe_tickets_promoter_trigger_attendee', $type, $attendee, $ticket );
	}

	/**
	 * Notify the event when a ticket, product or order is updated
	 *
	 * @since 4.12.0
	 */
	private function notify_ticket_event() {
		// Ticket
		add_action( 'save_post_tribe_wooticket', tribe_callback( 'tickets.promoter.observer', 'notify_ticket_event' ), 10, 1 );
		// Product
		add_action( 'save_post_product', tribe_callback( 'tickets.promoter.observer', 'notify_ticket_event' ), 10, 1 );
		// Order
		add_action( 'save_post_shop_order', [ $this, 'order_updated' ], 10, 1 );
		add_action( 'woocommerce_after_order_object_save', [ $this, 'order_updated' ], 10, 1 );
	}

	/**
	 * If an order is updated find the ID of the product to notify the event that the product has been updated.
	 *
	 * @since 4.12.0
	 *
	 * @param $order bool|WC_Abstract_Order|int Reference to the Woo Order.
	 */
	public function order_updated( $order ) {
		if (
			! function_exists( 'wc_get_order' )
			|| ! class_exists( 'WC_Abstract_Order' )
			|| ! class_exists( 'WC_Order_Item_Product' )
		) {
			return;
		}

		if ( ! $order instanceof WC_Abstract_Order ) {
			$order = wc_get_order( $order );
		}

		$data = [];

		if ( $order instanceof WC_Abstract_Order ) {
			$data = $order->get_items();
		}

		$data = is_array( $data ) ? $data : [];

		foreach ( $data as $item ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$this->observer->notify_ticket_event( $item->get_product_id() );
			}
		}
	}
}
