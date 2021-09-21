<?php

use Tribe\Tickets\Plus\Commerce\EDD\Attendee;
use Tribe\Tickets\Promoter\Triggers\Contracts\Attendee_Model;

/**
 * Class Tribe__Tickets_Plus__Commerce__EDD__Promoter_Observer
 *
 * @since 4.12.0
 */
class Tribe__Tickets_Plus__Commerce__EDD__Promoter_Observer {

	/**
	 * @since 4.12.0
	 *
	 * @var Tribe__Tickets__Promoter__Observer $observer ET Observer reference.
	 */
	private $observer;

	/**
	 * Tribe__Tickets_Plus__Commerce__EDD__Promoter_Observer constructor.
	 *
	 * @param Tribe__Tickets__Promoter__Observer $observer ET Observer.
	 */
	public function __construct( Tribe__Tickets__Promoter__Observer $observer ) {
		$this->observer = $observer;
		$this->hook();
	}

	/**
	 * Hooks on which this observer notifies promoter
	 *
	 * @since 4.12.0
	 */
	private function hook() {
		add_action( 'event_tickets_edd_ticket_created', [ $this, 'ticket_created' ], 10, 4 );
		add_action( 'eddtickets_ticket_deleted', [ $this->observer, 'notify_event_id' ], 10, 2 );
		add_action( 'eddtickets_checkin', [ $this, 'checkin' ], 10, 2 );

		// Only act if observer has notify_ticket_event method
		if ( method_exists( $this->observer, 'notify_ticket_event' ) ) {
			$this->notify_ticket_event();
		}
	}

	/**
	 * Listener when the "eddtickets_checkin" action is fired.
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
	 * @param int $order             EDD order ID.
	 * @param int $product_id        Product ID attendee is "purchasing".
	 * @param int $order_attendee_id Attendee # for order.
	 */
	public function ticket_created( $attendee_id, $order, $product_id, $order_attendee_id ) {
		$this->trigger( 'ticket_purchased', $attendee_id );
	}

	/**
	 * Trigger an action using the EDD provider.
	 *
	 * @since 4.12.3
	 *
	 * @param string $type        The type of trigger that is being fired.
	 * @param int    $attendee_id The ID of the attendee.
	 */
	private function trigger( $type, $attendee_id ) {
		/** @var Tribe__Tickets_Plus__Commerce__EDD__Main $ticket */
		$ticket   = tribe( 'tickets-plus.commerce.edd' );
		$attendee = new Attendee( $ticket->get_attendee( $attendee_id ) );

		/**
		 * Dispatch a new trigger with an attendee.
		 *
		 * @since 4.12.3
		 *
		 * @param string                                   $type     The type of trigger that is being fired.
		 * @param Attendee_Model                           $attendee The attendee model object.
		 * @param Tribe__Tickets_Plus__Commerce__EDD__Main $ticket   The EDD ticket object.
		 */
		do_action( 'tribe_tickets_promoter_trigger_attendee', $type, $attendee, $ticket );
	}

	/**
	 * H0ok into different actions specifically for EDD.
	 *
	 * @since 4.12.0
	 */
	private function notify_ticket_event() {
		// Downloads
		add_action( 'save_post_download', [ $this->observer, 'notify_ticket_event' ], 10, 1 );
		// Ticket
		add_action( 'save_post_tribe_eddticket', [ $this->observer, 'notify_ticket_event' ], 10, 1 );
		// Payments
		add_action( 'save_post_edd_payment', [ $this, 'payment_updated' ], 10, 1 );
		add_action( 'edd_customer_post_update', [ $this, 'customer_updated' ], 10, 2 );
	}

	/**
	 * Callback if an EDD customer has been updated.
	 *
	 * @since 4.12.0
	 *
	 * @param bool $updated     If the updated was successful or not.
	 * @param int  $customer_id Customer ID updated with the Order.
	 */
	public function customer_updated( $updated, $customer_id ) {
		if ( ! $updated || ! class_exists( 'EDD_Customer' ) ) {
			return;
		}

		$customer = new EDD_Customer( $customer_id );
		$payments = [];

		if ( method_exists( $customer, 'get_payment_ids' ) ) {
			$payments = $customer->get_payment_ids();
		}

		$payments = is_array( $payments ) ? $payments : [];
		foreach ( $payments as $payment_id ) {
			$this->payment_updated( $payment_id );
		}
	}

	/**
	 * If an EDD payment is updated notify to orders that are part of this payment.
	 *
	 * @since 4.12.0
	 *
	 * @param $payment_id int ID of the payment.
	 */
	public function payment_updated( $payment_id ) {
		// Make sure `edd_get_payment_meta_cart_details` exists
		if ( ! function_exists( 'edd_get_payment_meta_cart_details' ) ) {
			return;
		}

		$cart_details = edd_get_payment_meta_cart_details( $payment_id, true );
		$cart_details = is_array( $cart_details ) ? $cart_details : [];

		foreach ( $cart_details as $detail ) {
			if ( is_array( $detail ) && ! empty( $detail['id'] ) ) {
				$this->observer->notify_ticket_event( $detail['id'] );
			}
		}
	}
}
