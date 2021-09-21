<?php

namespace Tribe\Tickets\Plus\Repositories\Order;

use Tribe\Tickets\Plus\Repositories\Order;
use Tribe__Repository__Usage_Error as Usage_Error;
use Tribe__Tickets_Plus__Commerce__EDD__Main;
use Tribe__Utils__Array as Arr;

/**
 * The ORM/Repository class for EDD orders.
 *
 * @since 4.10.5
 *
 * @property Tribe__Tickets_Plus__Commerce__EDD__Main $attendee_provider
 */
class EDD extends Order {

	/**
	 * Key name to use when limiting lists of keys.
	 *
	 * @since 5.2.0
	 *
	 * @var string
	 */
	protected $key_name = 'edd';

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		parent::__construct();

		$this->attendee_provider = tribe( 'tickets-plus.commerce.edd' );

		// Set the order post type.
		$this->default_args['post_type'] = $this->attendee_provider->order_object;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.2.0
	 *
	 * @return WP_Post|false The new post object or false if unsuccessful.
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
		$order_status      = Arr::get( $order_data, 'order_status', 'publish' );

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

		$downloads  = [];
		$cart_items = [];

		// Build list of downloads and cart items to use.
		foreach ( $tickets as $ticket ) {
			$cart_item = [
				'id'         => 0,
				'quantity'   => 0,
				'price_id'   => null,
				'tax'        => 0,
				// Force zero price because order has no real payment.
				'item_price' => 0,
				'fees'       => [],
				'discount'   => 0,
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

			$downloads[] = [
				'id'       => $cart_item['id'],
				'quantity' => $cart_item['quantity'],
			];
		}

		// Set up payment data to use.
		$payment_data = [
			// Force zero price because order has no real payment.
			'price'        => 0,
			'date'         => current_time( 'mysql' ),
			'user_email'   => $email,
			'purchase_key' => uniqid( 'edd-ticket-', true ),
			'currency'     => edd_get_currency(),
			'status'       => $order_status,
			'downloads'    => $downloads,
			'user_info'    => [
				'id'         => $user_id,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'email'      => $email,
			],
			'cart_details' => $cart_items,
		];

		$reset_actions = [];

		// Remove certain actions to ensure they don't fire when creating the payments.
		if ( ! $send_emails ) {
			if ( has_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt' ) ) {
				$reset_actions[] = [
					'action'   => 'edd_complete_purchase',
					'callback' => 'edd_trigger_purchase_receipt',
					'priority' => 999,
				];
			}

			if ( has_action( 'edd_admin_sale_notice', 'edd_admin_email_notice' ) ) {
				$reset_actions[] = [
					'action'   => 'edd_admin_sale_notice',
					'callback' => 'edd_admin_email_notice',
				];
			}

			// Remove all of the actions needed.
			foreach ( $reset_actions as $action ) {
				call_user_func_array( 'remove_action', array_values( $action ) );
			}
		}

		// Record the pending payment.
		$order_id = edd_insert_payment( $payment_data );

		// Add actions that need to be reset.
		if ( ! empty( $reset_actions ) ) {
			foreach ( $reset_actions as $action ) {
				call_user_func_array( 'add_action', array_values( $action ) );
			}
		}

		if ( ! $order_id ) {
			return false;
		}

		return get_post( $order_id );
	}
}
