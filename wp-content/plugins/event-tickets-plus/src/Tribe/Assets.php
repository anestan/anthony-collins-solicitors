<?php
class Tribe__Tickets_Plus__Assets {
	/**
	 * Enqueue scripts for front end
	 *
	 * @since 4.6
	 * @since 4.11.1 Only load if in a tickets-enabled post context.
	 *
	 * @see   \tribe_tickets_is_enabled_post_context()
	 */
	public function enqueue_scripts() {
		$plugin = tribe( 'tickets-plus.main' );
		// Set up our base list of enqueues.
		$enqueue_array = [
			[ 'event-tickets-plus-tickets-css', 'tickets.css', [ 'tec-variables-full', 'dashicons' ] ],
			[ 'jquery-deparam', 'vendor/jquery.deparam/jquery.deparam.js', [ 'jquery' ] ],
			[ 'jquery-cookie', 'vendor/jquery.cookie/jquery.cookie.js', [ 'jquery' ] ],
			[ 'event-tickets-plus-attendees-list-js', 'attendees-list.js', [ 'event-tickets-attendees-list-js' ] ],
			[ 'event-tickets-plus-meta-js', 'meta.js', [ 'jquery-cookie', 'jquery-deparam' ] ],
		];

		$plugin = tribe( 'tickets-plus.main' );

		// and the engine...
		tribe_assets(
			$plugin,
			$enqueue_array,
			'wp_enqueue_scripts',
			[
				'localize'     => [
					'name' => 'TribeTicketsPlus',
					'data' => [
						'ajaxurl'                  => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
						'save_attendee_info_nonce' => wp_create_nonce( 'save_attendee_info' ),
					],
				],
				'conditionals' => tribe_callback( 'tickets.assets', 'should_enqueue_frontend' ),
			]
		);

		// Tickets meta validation library.
		tribe_asset(
			$plugin,
			'tribe-tickets-plus-attendee-meta',
			'v2/tickets-meta.js',
			[
				'jquery',
				'tribe-common',
			],
			null,
			[
				'groups' => [
					'tribe-tickets-block-assets',
					'tribe-tickets-modal',
					'tribe-tickets-rsvp',
					'tribe-tickets-registration-page',
					'tribe-tickets-admin',
					'tribe-tickets-forms',
				],
			]
		);

		if ( function_exists( 'tribe_tickets_new_views_is_enabled' ) && tribe_tickets_new_views_is_enabled() ) {

			/**
			 * Whether or not we should display the modal if no AR tickets in cart.
			 *
			 * @since 5.2.1
			 *
			 * @param boolean $show_modal (true) Whether or not to show the modal for this particular case.
			 */
			$show_modal_if_no_ar_in_cart = (bool) apply_filters( 'tribe_tickets_modal_show_if_no_ticket_with_ar_in_cart', true );

			// Tickets modal scripts.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-modal',
				'v2/tickets-modal.js',
				[
					'jquery',
					'tribe-common',
				],
				null,
				[
					'groups' => [
						'tribe-tickets-block-assets',
						'tribe-tickets-modal',
					],
					'localize' => (object) [
						'name' => 'TribeTicketsModal',
						'data' => [
							'ShowIfNoTicketWithArInCart' => $show_modal_if_no_ar_in_cart,
						],
					],
				]
			);

			// Tickets modal styles.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-modal-styles',
				'tickets-modal.css',
				[ 'tec-variables-full' ],
				null,
				[
					'groups' => [
						'tribe-tickets-block-assets',
						'tribe-tickets-modal',
					],
				]
			);

			// Tickets attendee ticket styles.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-attendee-tickets-styles',
				'tickets-attendee-tickets.css',
				[ 'tec-variables-full' ],
				null,
				[
					'groups' => [
						'tribe-tickets-block-assets',
						'tribe-tickets-modal',
						'tribe-tickets-registration-page',
					],
				]
			);

			// Tickets registration page scripts.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-registration-page',
				'v2/tickets-registration-page.js',
				[
					'jquery',
					'wp-util',
					'tribe-common',
				],
				null,
				[
					'groups' => [
						'tribe-tickets-registration-page',
					],
				]
			);

			// Tickets registration page styles.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-registration-page-styles',
				'tickets-registration-page.css',
				[ 'tec-variables-full' ],
				null,
				[
					'groups' => [
						'tribe-tickets-registration-page',
					],
				]
			);

			tribe_asset(
				$plugin,
				'tribe-tickets-plus-data',
				'v2/tickets-data.js',
				[
					'jquery',
					'tribe-common',
				],
				null,
				[
					'groups' => [
						'tribe-tickets-block-assets',
						'tribe-tickets-registration-page',
					],
				]
			);

			// @TODO: we should conditionally use this if IAC is being used.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-iac',
				'v2/tickets-iac.js',
				[
					'jquery',
					'wp-util',
					'tribe-common',
				],
				null,
				[
					'groups' => [
						'tribe-tickets-block-assets',
						'tribe-tickets-registration-page',
						'tribe-tickets-page-assets',
					],
				]
			);

			// Tickets IAC styles.
			// @TODO: we should conditionally use this if IAC is being used.
			tribe_asset(
				$plugin,
				'tribe-tickets-plus-iac-styles',
				'tickets-iac.css',
				[ 'tec-variables-full' ],
				null,
				[
					'groups' => [
						'tribe-tickets-block-assets',
						'tribe-tickets-registration-page',
					],
				]
			);
		}
	}

	/**
	 * Enqueue scripts for admin views
	 *
	 * @since 4.6
	 */
	public function admin_enqueue_scripts() {
		// Set up our base list of enqueues.
		$enqueue_array = [
			[ 'event-tickets-plus-meta-admin-css', 'meta.css', [ 'tec-variables-full' ] ],
			[ 'event-tickets-plus-meta-report-js', 'meta-report.js', [] ],
			[ 'event-tickets-plus-attendees-list-js', 'attendees-list.js', [ 'event-tickets-attendees-list-js' ] ],
			[ 'event-tickets-plus-meta-admin-js', 'meta-admin.js', [ 'tribe-common', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'event-tickets-admin-js' ] ],
			[ 'event-tickets-plus-admin-css', 'admin.css', [ 'event-tickets-admin-css' ] ],
			[ 'event-tickets-plus-admin-tables-js', 'tickets-tables.js', [ 'underscore', 'jquery', 'tribe-common' ] ],
			[ 'event-tickets-plus-admin-qr', 'qr.js', [ 'jquery' ] ],
		];

		/**
		 * Filter the array of module names.
		 *
		 * @since 4.6
		 *
		 * @param array the array of modules
		 */
		$modules = Tribe__Tickets__Tickets::modules();
		$modules = array_values( $modules );

		if ( in_array( 'WooCommerce', $modules )  ) {
			$enqueue_array[] = [
				'event-tickets-plus-wootickets-css',
				'wootickets.css',
				[ 'event-tickets-plus-meta-admin-css' ],
			];
		}

		// and the engine...
		tribe_assets(
			tribe( 'tickets-plus.main' ),
			$enqueue_array,
			'admin_enqueue_scripts',
			[
				'priority' => 0,
				'groups'       => 'event-tickets-plus-admin',
				'conditionals' => [ $this, 'should_enqueue_admin' ],
				'localize' => (object) [
					'name' => 'tribe_qr',
					'data' => [
						'generate_qr_nonce'   => wp_create_nonce( 'generate_qr_nonce' ),
					],
				],
			]
		);
	}

	/**
	 * Determine if the admin assets should be enqueued.
	 *
	 * @since 5.2.6
	 *
	 * @return bool
	 */
	public function should_enqueue_admin() {
		global $post;

		$et_should_enqueue = tribe( 'tickets.assets' )->should_enqueue_admin();

		$et_plus_should_enqueue = false;

		if ( ! empty( $_GET['post_type'] ) && in_array( $_GET['post_type'], tribe( 'tickets.main' )->post_types(), true ) ) {
			$et_plus_should_enqueue = true;
		}

		if ( ! empty( $post->post_type ) && \Tribe__Tickets_Plus__Meta__Fieldset::POSTTYPE === $post->post_type ) {
			$et_plus_should_enqueue = true;
		}

		return $et_should_enqueue || $et_plus_should_enqueue;
	}
}
