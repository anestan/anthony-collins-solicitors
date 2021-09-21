<?php

use Tribe__Utils__Array as Arr;

/**
 * The ORM/Repository class for WooCommerce attendees.
 *
 * @since 4.10.5
 *
 * @property Tribe__Tickets_Plus__Commerce__WooCommerce__Main $attendee_provider
 */
class Tribe__Tickets_Plus__Repositories__Attendee__WooCommerce extends Tribe__Tickets_Plus__Attendee_Repository {

	/**
	 * Key name to use when limiting lists of keys.
	 *
	 * @var string
	 */
	protected $key_name = 'woo';

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		parent::__construct();

		// Remove Easy Digital Downloads.
		unset( $this->schema['edd_order'] );

		$this->attendee_provider = tribe( 'tickets-plus.commerce.woo' );

		$this->create_args['post_type'] = $this->attendee_provider->attendee_object;

		// Use a regular variable so we can get constants from it in a PHP <7.0 compatible way.
		$attendee_provider = $this->attendee_provider;

		// Add object specific aliases.
		$this->update_fields_aliases = array_merge(
			$this->update_fields_aliases,
			[
				'ticket_id'      => $attendee_provider->attendee_product_key,
				'event_id'       => $attendee_provider->attendee_event_key,
				'post_id'        => $attendee_provider->attendee_event_key,
				'security_code'  => $attendee_provider->security_code,
				'order_id'       => $attendee_provider->attendee_order_key,
				'order_item_key' => $attendee_provider->attendee_order_item_key,
				'optout'         => $attendee_provider->attendee_optout_key,
				'user_id'        => $attendee_provider->attendee_user_id,
				'price_paid'     => $attendee_provider->price_paid,
				'price_currency' => $attendee_provider->price_currency,
				'full_name'      => $attendee_provider->full_name,
				'email'          => $attendee_provider->email,
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attendee_types() {
		return $this->limit_list( $this->key_name, parent::attendee_types() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function attendee_to_event_keys() {
		return $this->limit_list( $this->key_name, parent::attendee_to_event_keys() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function attendee_to_ticket_keys() {
		return $this->limit_list( $this->key_name, parent::attendee_to_ticket_keys() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function attendee_to_order_keys() {
		return $this->limit_list( $this->key_name, parent::attendee_to_order_keys() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function purchaser_name_keys() {
		/*
		 * This is here to reduce confusion by future developers.
		 *
		 * Purchaser name does not have a meta key stored on the attendee itself
		 * and must be retrieved by order customer for WooCommerce.
		 */
		return parent::purchaser_name_keys();
	}

	/**
	 * {@inheritdoc}
	 */
	public function purchaser_email_keys() {
		/*
		 * This is here to reduce confusion by future developers.
		 *
		 * Purchaser name does not have a meta key stored on the attendee itself
		 * and must be retrieved by order customer for WooCommerce.
		 */
		return parent::purchaser_email_keys();
	}

	/**
	 * {@inheritdoc}
	 */
	public function security_code_keys() {
		return $this->limit_list( $this->key_name, parent::security_code_keys() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function attendee_optout_keys() {
		return $this->limit_list( $this->key_name, parent::attendee_optout_keys() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function checked_in_keys() {
		return $this->limit_list( $this->key_name, parent::checked_in_keys() );
	}

	/**
	 * Handle backwards compatible actions for WooCommerce.
	 *
	 * @since 5.2.0
	 *
	 * @param WP_Post                       $attendee      The attendee object.
	 * @param array                         $attendee_data List of additional attendee data.
	 * @param Tribe__Tickets__Ticket_Object $ticket        The ticket object.
	 */
	public function trigger_create_actions( $attendee, $attendee_data, $ticket ) {
		$attendee_id       = $attendee->ID;
		$post_id           = Arr::get( $attendee_data, 'post_id' );
		$order_id          = Arr::get( $attendee_data, 'order_id' );
		$product_id        = $ticket->ID;
		$order_attendee_id = Arr::get( $attendee_data, 'order_attendee_id', 0 );
		$quantity          = 1;
		$order             = null;

		if ( $order_id ) {
			$order = wc_get_order( $order_id );
		}

		/**
		 * WooCommerce-specific action fired when a WooCommerce-driven attendee ticket for an event is generated.
		 *
		 * @param int      $attendee_id ID of attendee ticket.
		 * @param int      $post_id     ID of event.
		 * @param WC_Order $order       WooCommerce order.
		 * @param int      $product_id  WooCommerce product ID.
		 */
		do_action( 'event_ticket_woo_attendee_created', $attendee_id, $post_id, $order, $product_id );

		/**
		 * Action fired when an attendee ticket is generated.
		 *
		 * @param int $attendee_id       ID of attendee ticket.
		 * @param int $order_id          WooCommerce order ID.
		 * @param int $product_id        WooCommerce product ID.
		 * @param int $order_attendee_id Attendee # for order.
		 */
		do_action( 'event_tickets_woocommerce_ticket_created', $attendee_id, $order_id, $product_id, $order_attendee_id );

		/**
		 * Action fired when a WooCommerce attendee tickets have been generated.
		 *
		 * @param int $order_id WooCommerce order ID.
		 */
		do_action( 'event_tickets_woocommerce_tickets_generated', $order_id );

		/**
		 * Action fired when a WooCommerce has had attendee tickets generated for it.
		 *
		 * @param int      $product_id  WooCommerce product ID.
		 * @param int      $order_id    WooCommerce order ID.
		 * @param int      $quantity    Quantity ordered.
		 * @param int      $post_id     ID of event.
		 */
		do_action( 'event_tickets_woocommerce_tickets_generated_for_product', $product_id, $order_id, $quantity, $post_id );

		parent::trigger_create_actions( $attendee, $attendee_data, $ticket );
	}
}
