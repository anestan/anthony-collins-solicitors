<?php

namespace Tribe\Tickets\Plus\Service_Providers;

use Tribe__Tickets_Plus__Main as Plugin;

/**
 * Class Resend_Tickets_Handler
 *
 * @package Tribe\Tickets\Plus\Service_Providers
 *
 * @since 5.2.5
 */

class Resend_Tickets_Handler extends \tad_DI52_ServiceProvider {

	/**
	 * Slug for JS and CSS handlers.
	 *
	 * @since 5.2.5
	 *
	 * @var string
	 */
	public static $slug = 'event-tickets-plus-resend_tickets';

	/**
	 * Slug for nonce key.
	 *
	 * @since 5.2.5
	 *
	 * @var string
	 */
	public static $nonce_key = 'event-tickets-plus-resend_tickets-nonce';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.2.5
	 */
	public function register() {
		// Load actions and filter on admin view only, as this is not designed for Community Tickets for now.
		if ( ! is_admin() ) {
			return;
		}

		$this->hook();
		$this->register_assets();
	}

	/**
	 * Register assets.
	 *
	 * @since 5.2.5
	 */
	public function register_assets() {

		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			self::$slug,
			'resend-tickets.js',
			[
				'jquery',
				'tribe-common',
				'tickets-attendees-js',
			],
			null,
			[
				'groups' => [
					'tribe-tickets-admin',
				],
			]
		);
	}

	/**
	 * Add localize script data.
	 *
	 * @since 5.2.5
	 *
	 * @param array $data Array of localized data.
	 *
	 * @return mixed
	 */
	public function add_localize_script_data( $data ) {

		$data['resend_ticket'] = [
			'nonce'          => wp_create_nonce( self::$nonce_key ),
			'default_label'  => __( 'Re-send Ticket', 'event-tickets-plus' ),
			'progress_label' => __( 'Sending', 'event-tickets-plus' ),
			'success_label'  => __( 'Ticket Sent', 'event-tickets-plus' ),
		];

		return $data;
	}

	/**
	 * Register hooks
	 *
	 * @since 5.2.5
	 */
	public function hook() {
		add_filter( 'event_tickets_attendees_table_row_actions', [ $this, 'add_resend_tickets_action' ], 20, 2 );
		add_filter( 'tribe_tickets_attendees_report_js_config', [ $this, 'add_localize_script_data' ] );
		add_action( 'wp_ajax_event-tickets-plus-resend-tickets', [ $this, 'handle_resend_ticket_request' ] );
	}

	/**
	 * Add Re-send tickets action item in the attendee list actions.
	 *
	 * @since 5.2.5
	 *
	 * @param array $row_actions Row action items.
	 * @param array $item Attendee data for the row.
	 *
	 * @return array
	 */
	public function add_resend_tickets_action( array $row_actions, array $item ) {

		if ( ! isset( $item['event_id'] ) ) {
			return $row_actions;
		}

		$event_id = $item['event_id'];

		/** @var \Tribe__Tickets__Attendees $attendees */
		$attendees = tribe( 'tickets.attendees' );

		if ( ! $attendees->user_can_manage_attendees( 0, $event_id ) ) {
			return $row_actions;
		}

		/**
		 * Allow alteration of max resend ticket count.
		 *
		 * @since 5.2.5
		 *
		 * @param int $max Maximum allowed nnumber of send tickets.
		 * @param array $item Attendee Item data.
		 */
		$ticket_sent_threshold = absint( apply_filters( 'event_tickets_attendee_resend_tickets_max_allowed', 10, $item ) );

		if ( $ticket_sent_threshold <= $item['ticket_sent'] ) {
			return $row_actions;
		}

		$label = __( 'Re-send Ticket', 'event-tickets-plus' );
		$link  = sprintf( '<button class="button-link re-send-ticket-action" type="button" data-attendee-id="%1$s" data-provider="%2$s">%3$s</button>', $item['attendee_id'], $item['provider'], $label );

		$row_actions[] = '<span class="inline re-send_ticket">' . $link . '</span>';

		return $row_actions;
	}

	/**
	 * Handles resend ticket request.
	 *
	 * @since 5.2.5
	 */
	public function handle_resend_ticket_request() {

		$nonce = tribe_get_request_var( 'nonce' ) ;

		if (
			empty( $nonce )
			|| ! wp_verify_nonce( $nonce, self::$nonce_key )
		) {
			wp_send_json_error( "Cheatin' huh?" );
		}

		$provider_class = tribe_get_request_var( 'provider' );
		$provider       = \Tribe__Tickets__Tickets::get_ticket_provider_instance( $provider_class );
		$attendee_id    = tribe_get_request_var( 'attendee_id' );

		$sent = $provider->send_tickets_email_for_attendees( [ $attendee_id ] );

		if ( ! tribe_is_truthy( $sent ) ) {
			wp_send_json_error( [ 'message' => __( 'Something Went Wrong! Re-sending ticket failed.', 'event-tickets-plus' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'Email was sent successfully!', 'event-tickets-plus' ) ]  );
	}
}