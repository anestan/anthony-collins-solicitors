<?php

namespace Tribe\Tickets\Plus\Repositories;

use Tribe\Tickets\Repositories\Order as ET_Order;
use Tribe__Repository__Void_Query_Exception as Void_Query_Exception;
use Tribe__Utils__Array as Arr;

/**
 * The repository functionality for Event Tickets Plus Orders.
 *
 * @since 5.2.0
 */
class Order extends ET_Order {

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		/*
		 * Hook into the status filtering before parent::__construct() runs.
		 *
		 * These can be moved in the future into each commerce provider class
		 * when we add order status filters to the Attendees repository.
		 */
		add_filter( 'tribe_tickets_repositories_order_statuses', [ $this, 'register_statuses' ] );
		add_filter( 'tribe_tickets_repositories_order_public_statuses', [ $this, 'register_public_statuses' ] );

		parent::__construct();

		$this->schema = array_merge( $this->schema, [
			'order_status' => [ $this, 'filter_by_order_status' ],
		] );
	}

	/**
	 * Filtering the list of all order statuses to add ET+ provider order statuses.
	 *
	 * This can be moved in the future into each commerce provider class
	 * when we add order status filters to the Attendees repository.
	 *
	 * @since 5.2.0
	 *
	 * @param array $statuses List of all order statuses.
	 */
	public function register_statuses( $statuses ) {
		/** @var Tribe__Tickets__Status__Manager $status_mgr */
		$status_mgr = tribe( 'tickets.status' );

		$has_wc  = class_exists( 'WooCommerce' );
		$has_edd = defined( 'EDD_VERSION' ) && class_exists( 'Easy_Digital_Downloads' );

		if ( $has_wc ) {
			$statuses = array_merge( $statuses, $status_mgr->get_statuses_by_action( 'all', 'woo' ) );
		}

		if ( $has_edd ) {
			$edd_statuses = $status_mgr->get_statuses_by_action( 'all', 'edd' );

			// Remove complete status.
			$edd_statuses = array_diff( [ 'Complete' ], $edd_statuses );

			$statuses = array_merge( $statuses, $edd_statuses );
		}

		// Enforce lowercase for comparison purposes.
		$statuses = array_map( 'strtolower', $statuses );

		// Prevent unnecessary duplicates.
		$statuses = array_unique( $statuses );

		return $statuses;
	}

	/**
	 * Filtering the list of public order statuses to add ET+ provider order statuses.
	 *
	 * This can be moved in the future into each commerce provider class
	 * when we add order status filters to the Attendees repository.
	 *
	 * @since 5.2.0
	 *
	 * @param array $public_order_statuses List of public order statuses.
	 */
	public function register_public_statuses( $public_order_statuses ) {
		// WooCommerce orders.
		$public_order_statuses[] = 'wc-completed';

		// Easy Digital Downloads orders.
		$public_order_statuses[] = 'publish';

		return $public_order_statuses;
	}

	/**
	 * Filters the map relating Order repository slugs to service container bindings.
	 *
	 * @since 5.2.0
	 *
	 * @param array $map The map in the shape [ <repository_slug> => <service_name> ].
	 *
	 * @return array The map in the shape [ <repository_slug> => <service_name> ].
	 */
	public function filter_order_repository_map( $map ) {
		// Easy Digital Downloads repository.
		$map['edd'] = 'tickets-plus.repositories.order.edd';

		// WooCommerce repository.
		$map['woo'] = 'tickets-plus.repositories.order.woo';

		return $map;
	}

	/**
	 * Filters attendee to only get those related to orders with a specific status.
	 *
	 * @since 5.2.0
	 *
	 * @param string|array $order_status Order status.
	 *
	 * @throws Tribe__Repository__Void_Query_Exception If the requested statuses are not accessible by the user.
	 */
	public function filter_by_order_status( $order_status ) {
		$statuses = Arr::list_to_array( $order_status );

		$has_manage_access = current_user_can( 'edit_users' ) || current_user_can( 'tribe_manage_attendees' );

		// Map the `any` meta-status.
		if ( 1 === count( $statuses ) && 'any' === $statuses[0] ) {
			if ( ! $has_manage_access ) {
				$statuses = [ 'public' ];
			} else {
				// No need to filter if the user can read all posts.
				return;
			}
		}

		// Allow the user to define singular statuses or the meta-status "public".
		if ( in_array( 'public', $statuses, true ) ) {
			$statuses = array_unique( array_merge( $statuses, self::$public_order_statuses ) );
		}

		// Allow the user to define singular statuses or the meta-status "private".
		if ( in_array( 'private', $statuses, true ) ) {
			$statuses = array_unique( array_merge( $statuses, self::$private_order_statuses ) );
		}

		// Remove any status the user cannot access.
		if ( ! $has_manage_access ) {
			$statuses = array_intersect( $statuses, self::$public_order_statuses );
		}

		if ( empty( $statuses ) ) {
			throw Void_Query_Exception::because_the_query_would_yield_no_results( 'The user cannot access the requested attendee order statuses.' );
		}

		$this->by( 'post_status', $statuses );
	}

}
