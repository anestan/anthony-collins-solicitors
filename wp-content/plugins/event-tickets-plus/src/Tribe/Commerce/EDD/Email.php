<?php

if ( class_exists( 'Tribe__Tickets_Plus__Commerce__EDD__Email' ) ) {
	return;
}

class Tribe__Tickets_Plus__Commerce__EDD__Email {

	private $default_subject;

	public function __construct() {

		$this->default_subject = esc_html( sprintf( __( 'Your %s from {sitename}', 'event-tickets-plus' ), tribe_get_ticket_label_plural_lowercase( 'edd_email_default_subject' ) ) );

		// Triggers for this email
		add_action( 'eddtickets-send-tickets-email', [ $this, 'trigger' ] );

		add_filter( 'edd_settings_emails', [ $this, 'settings' ] );
	}

	/**
	 * Register the email settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function settings( $settings ) {
		$email_settings = [
			'tribe_ticket_email_heading' => [
				'id' => 'tribe_ticket_email_heading',
				'name' => '<strong>' . esc_html( sprintf( __( 'Tribe %s Emails', 'event-tickets-plus' ), tribe_get_ticket_label_singular( 'edd_email_heading_name' ) ) ) . '</strong>',
				'desc' => esc_html( sprintf( __( 'Configure the %s receipt emails', 'event-tickets-plus' ), tribe_get_ticket_label_singular_lowercase( 'edd_email_heading_desc' ) ) ),
				'type' => 'header',
			],
			'ticket_subject' => [
				'id' => 'ticket_subject',
				'name' => esc_html( sprintf( __( '%s Email Subject', 'event-tickets-plus' ), tribe_get_ticket_label_plural( 'edd_email_subject_name' ) ) ),
				'desc' => esc_html( sprintf( __( 'Enter the subject line for the %s receipt email', 'event-tickets-plus' ), tribe_get_ticket_label_plural_lowercase( 'edd_email_subject_desc' ) ) ),
				'type' => 'text',
				'std'  => $this->default_subject,
			],
		];

		return array_merge( $settings, $email_settings );
	}

	/**
	 * Trigger the tickets email
	 *
	 * @param int $payment_id
	 */
	public function trigger( $payment_id = 0 ) {
		/**
		 * Whether to allow ticket email receipt for Easy Digital Download orders.
		 *
		 * @param bool $edd_email_receipt_enabled Whether the ticket email receipt is enabled.
		 */
		$edd_email_receipt_enabled = apply_filters( 'edd_email_ticket_receipt', true );

		if ( ! $edd_email_receipt_enabled ) {
			return;
		}

		/** @var Tribe__Tickets_Plus__Commerce__EDD__Main $commerce_edd */
		$commerce_edd = tribe( 'tickets-plus.commerce.edd' );

		// Get the attendees for the order.
		$attendees = $commerce_edd->get_attendees_by_id( $payment_id );

		$send_args = [
			'order_id'           => $payment_id,
			'send_purchaser_all' => true,
		];

		// Send the emails.
		$commerce_edd->send_tickets_email_for_attendees( $attendees, $send_args );
	}

	/**
	 * Retrieve the full HTML for the tickets email
	 *
	 * @param int $payment_id
	 *
	 * @return string
	 */
	public function get_content_html( $payment_id = 0 ) {

		$eddtickets = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance();

		$attendees = $eddtickets->get_attendees_by_id( $payment_id );

		return $eddtickets->generate_tickets_email_content( $attendees );
	}
}
