<?php

namespace Tribe\Tickets\Plus\Repositories\Order;

use Tribe\Tickets\Plus\Repositories\Order;
use Tribe__Repository__Usage_Error as Usage_Error;
use Tribe__Tickets_Plus__Commerce__WooCommerce__Main;
use Tribe__Utils__Array as Arr;

/**
 * The ORM/Repository class for WooCommerce orders.
 *
 * @since 4.10.5
 *
 * @property Tribe__Tickets_Plus__Commerce__WooCommerce__Main $attendee_provider
 */
class WooCommerce extends Order {

	/**
	 * Key name to use when limiting lists of keys.
	 *
	 * @since 5.2.0
	 *
	 * @var string
	 */
	protected $key_name = 'woo';

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		parent::__construct();

		// Set the attendee provider for later use.
		$this->attendee_provider = tribe( 'tickets-plus.commerce.woo' );

		// Set the order post type.
		$this->default_args['post_type'] = $this->attendee_provider->order_object;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.2.0
	 *
	 * @return array|null|WP_Post|false The new post object or false if unsuccessful.
	 */
	public function create() {
		$order_data = $this->updates;

		$required_details = [
			'full_name',
			'email',
			'tickets',
		];

		foreach ( $required_details as $required_detail ) {
			// Detail is not set.
			if ( ! isset( $order_data[ $required_detail ] ) ) {
				throw new Usage_Error( sprintf( 'You must provide "%s" to create a new order.', $required_detail ) );
			}

			// Detail is empty.
			if ( empty( $order_data[ $required_detail ] ) ) {
				throw new Usage_Error( sprintf( 'Order field "%s" is empty.', $required_detail ) );
			}
		}

		$full_name         = $order_data['full_name'];
		$email             = $order_data['email'];
		$tickets           = $order_data['tickets'];
		$first_name        = Arr::get( $order_data, 'first_name' );
		$last_name         = Arr::get( $order_data, 'last_name' );
		$user_id           = (int) Arr::get( $order_data, 'user_id', 0 );
		$create_user       = (bool) Arr::get( $order_data, 'create_user', false );
		$use_existing_user = (bool) Arr::get( $order_data, 'use_existing_user', true );
		$send_emails       = (bool) Arr::get( $order_data, 'send_emails', false );
		$order_status      = Arr::get( $order_data, 'order_status', 'completed' );

		$order_status = strtolower( trim( $order_status ) );

		// Maybe set the first / last name.
		if ( null === $first_name || null === $last_name ) {
			$first_name = $full_name;
			$last_name  = '';

			// Get first name and last name.
			if ( false !== strpos( $full_name, ' ' ) ) {
				$name_parts = explode( ' ', $full_name );

				// First name is first text.
				$first_name = array_shift( $name_parts );

				// Last name is everything the first text.
				$last_name = implode( ' ', $name_parts );
			}
		}

		if ( 0 === $user_id ) {
			$user_args = [
				'use_existing_user' => $use_existing_user,
				'create_user'       => $create_user,
				'send_email'        => $send_emails,
				'display_name'      => $full_name,
				'first_name'        => $first_name,
				'last_name'         => $last_name,
			];

			$user_id = (int) $this->attendee_provider->maybe_setup_attendee_user_from_email( $email, $user_args );
		}

		$cart_items = [];

		// Build list of downloads and cart items to use.
		foreach ( $tickets as $ticket ) {
			$cart_item = [
				'id'       => 0,
				'quantity' => 0,
			];

			$cart_item = array_merge( $cart_item, $ticket );

			if ( $cart_item['id'] < 1 ) {
				throw new Usage_Error( 'Every ticket must have a valid id set to be added to an order.' );
			}

			// Skip empty quantities.
			if ( $cart_item['quantity'] < 1 ) {
				continue;
			}

			$cart_items[] = $cart_item;
		}

		$reset_actions = [];
		$added_filters = [];

		// Remove certain actions to ensure they don't fire when creating the orders and add filters to disable emails.
		if ( ! $send_emails ) {
			if ( ! has_filter( 'woocommerce_email_enabled_new_order', '__return_false' ) ) {
				$added_filters[] = [
					'action'   => 'woocommerce_email_enabled_new_order',
					'callback' => '__return_false',
				];
			}

			if ( ! has_filter( 'woocommerce_email_enabled_customer_completed_order', '__return_false' ) ) {
				$added_filters[] = [
					'action'   => 'woocommerce_email_enabled_customer_completed_order',
					'callback' => '__return_false',
				];
			}
		}

		if ( has_action( 'woocommerce_order_status_changed', [
			$this->attendee_provider,
			'delayed_ticket_generation',
		] ) ) {
			$reset_actions[] = [
				'action'   => 'woocommerce_order_status_changed',
				'callback' => [ $this->attendee_provider, 'delayed_ticket_generation' ],
				'priority' => 12,
			];
		}

		// Remove all of the actions needed.
		foreach ( $reset_actions as $action ) {
			call_user_func_array( 'remove_action', array_values( $action ) );
		}

		// Add all of the filters needed.
		foreach ( $added_filters as $filter ) {
			call_user_func_array( 'add_filter', array_values( $filter ) );
		}

		// Create the order.
		$order = wc_create_order( [
			'customer_id' => $user_id,
			'created_via' => 'admin',
		] );

		// Order creation failed.
		if ( is_wp_error( $order ) ) {
			return false;
		}

		// Add items to the order.
		foreach ( $cart_items as $cart_item ) {
			$product = wc_get_product( $cart_item['id'] );

			if ( empty( $product ) ) {
				throw new Usage_Error( 'Every ticket must exist to be added to an order.' );
			}

			// Force zero price because order has no real payment.
			$product->set_price( 0 );

			$order->add_product( $product, $cart_item['quantity'] );
		}

		$address = [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
		];

		// Set addresses.
		$order->set_address( $address, 'billing' );
		$order->set_address( $address, 'shipping' );

		// Set payment gateway.
		$payment_gateways = WC()->payment_gateways->payment_gateways();

		// Use bank transfer method for now.
		$order->set_payment_method( $payment_gateways['bacs'] );

		// Set the order status.
		$order->set_status( $order_status );

		// Calculate totals.
		$order->calculate_totals();

		// Force zero price because order has no real payment.
		$order->set_total( 0 );

		// Add order note.
		$order->add_order_note( __( 'Order created manually', 'event-tickets-plus' ) );

		// Force has Tickets data to 1.
		$order->update_meta_data( $this->attendee_provider->order_has_tickets, 1 );

		$order->save_meta_data();

		// Add actions that need to be reset.
		foreach ( $reset_actions as $action ) {
			call_user_func_array( 'add_action', array_values( $action ) );
		}

		// Remove filters that need to be reset.
		foreach ( $added_filters as $filter ) {
			call_user_func_array( 'remove_filter', array_values( $filter ) );
		}

		return get_post( $order->get_id() );
	}
}
