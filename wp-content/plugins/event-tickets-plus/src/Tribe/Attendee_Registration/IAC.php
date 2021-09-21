<?php

namespace Tribe\Tickets\Plus\Attendee_Registration;

/**
 * Class IAC
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration
 *
 * @since   5.1.0
 */
class IAC {

	/**
	 * The key to use for when IAC is off.
	 *
	 * @since 5.1.0
	 */
	const NONE_KEY = 'none';

	/**
	 * The key to use for when IAC is allowed.
	 *
	 * @since 5.1.0
	 */
	const ALLOWED_KEY = 'allowed';

	/**
	 * The key to use for when IAC is required.
	 *
	 * @since 5.1.0
	 */
	const REQUIRED_KEY = 'required';

	/**
	 * The option name used to store the default IAC setting.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $option_name = 'ticket_individual_attendee_collection';

	/**
	 * The meta key used to store the IAC setting on a ticket.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $ticket_meta_key = '_tribe_tickets_ar_iac';

	/**
	 * The field slug used for the IAC Name field.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $ticket_field_name_slug = 'tribe-tickets-plus-iac-name';

	/**
	 * The field slug used for the IAC Email field.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $ticket_field_email_slug = 'tribe-tickets-plus-iac-email';

	/**
	 * The field slug used for the IAC Re-send Email field.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $ticket_field_resend_email_slug = 'tribe-tickets-plus-iac-email-resend';

	/**
	 * Get the default IAC setting option name.
	 *
	 * @since 5.1.0
	 *
	 * @return string The default IAC setting option name.
	 */
	public function get_default_iac_setting_option_name() {
		return $this->option_name;
	}

	/**
	 * Get the IAC setting meta key used on a ticket.
	 *
	 * @since 5.1.0
	 *
	 * @return string The IAC setting meta key used on a ticket.
	 */
	public function get_iac_setting_ticket_meta_key() {
		return $this->ticket_meta_key;
	}

	/**
	 * Get the field slug to use for the IAC Name field.
	 *
	 * @since 5.1.0
	 *
	 * @return string The field slug to use for the IAC Name field.
	 */
	public function get_iac_ticket_field_slug_for_name() {
		return $this->ticket_field_name_slug;
	}

	/**
	 * Get the field slug to use for the IAC Email field.
	 *
	 * @since 5.1.0
	 *
	 * @return string The field slug to use for the IAC Email field.
	 */
	public function get_iac_ticket_field_slug_for_email() {
		return $this->ticket_field_email_slug;
	}

	/**
	 * Get the field slug to use for the IAC Re-send Email field.
	 *
	 * @since 5.1.0
	 *
	 * @return string The field slug to use for the IAC Re-send Email field.
	 */
	public function get_iac_ticket_field_slug_for_resend_email() {
		return $this->ticket_field_resend_email_slug;
	}

	/**
	 * Get the list of the IAC setting options.
	 *
	 * @since 5.1.0
	 *
	 * @return array The list of the IAC setting options.
	 */
	public function get_iac_setting_options() {
		$options = [
			self::NONE_KEY     => _x( 'No Individual Attendee Collection', 'Individual Attendee Collection setting choice', 'event-tickets-plus' ),
			self::ALLOWED_KEY  => _x( 'Allow Individual Attendee Collection', 'Individual Attendee Collection setting choice', 'event-tickets-plus' ),
			self::REQUIRED_KEY => _x( 'Require Individual Attendee Collection', 'Individual Attendee Collection setting choice', 'event-tickets-plus' ),
		];

		/**
		 * Allow filtering the list of the IAC setting options.
		 *
		 * @since 5.1.0
		 *
		 * @param array $options List of the IAC setting options.
		 */
		return apply_filters( 'tribe_tickets_plus_attendee_registration_iac_options', $options );
	}

	/**
	 * Get the default IAC setting for all tickets.
	 *
	 * @since 5.1.0
	 *
	 * @return string The default IAC setting for all tickets (none, allowed, required).
	 */
	public function get_default_iac_setting() {
		$option_name         = $this->get_default_iac_setting_option_name();
		$default_iac_setting = tribe_get_option( $option_name, self::NONE_KEY );

		if ( empty( $default_iac_setting ) ) {
			$default_iac_setting = self::NONE_KEY;
		}

		/**
		 * Allow filtering the default IAC setting for all tickets.
		 *
		 * @since 5.1.0
		 *
		 * @param string $default_iac_setting The default IAC setting for all tickets (none, allowed, required).
		 */
		return apply_filters( 'tribe_tickets_plus_attendee_registration_iac_default_setting_for_all_tickets', $default_iac_setting );
	}

	/**
	 * Get the IAC setting for a ticket.
	 *
	 * @since 5.1.0
	 *
	 * @param \Tribe__Tickets__Ticket_Object|int $ticket The ticket object or ID.
	 *
	 * @return string The IAC setting for a ticket (none, allowed, required).
	 */
	public function get_iac_setting_for_ticket( $ticket ) {
		$ticket_id = $ticket;

		if ( is_object( $ticket ) ) {
			$ticket_id = $ticket->ID;
		}

		if ( empty( $ticket_id ) ) {
			return self::NONE_KEY;
		}

		$ticket_meta_key = $this->get_iac_setting_ticket_meta_key();
		$iac_setting     = get_post_meta( $ticket_id, $ticket_meta_key, true );

		if ( empty( $iac_setting ) ) {
			$iac_setting = self::NONE_KEY;
		}

		/**
		 * Allow filtering the IAC setting for a ticket.
		 *
		 * @since 5.1.0
		 *
		 * @param string                             $iac_setting The IAC setting for a ticket (none, allowed, required).
		 * @param int                                $ticket_id   The ticket ID.
		 * @param \Tribe__Tickets__Ticket_Object|int $ticket      The ticket object or ID as passed to the method.
		 */
		return apply_filters( 'tribe_tickets_plus_attendee_registration_iac_setting_for_ticket', $iac_setting, $ticket_id, $ticket );
	}

	/**
	 * Get the IAC setting label for a ticket.
	 *
	 * @since 5.1.0
	 *
	 * @param \Tribe__Tickets__Ticket_Object|int $ticket    The ticket object or ID.
	 * @param bool                               $show_none Whether to show the label for 'none'.
	 *
	 * @return string The IAC setting label for a ticket.
	 */
	public function get_iac_setting_label_for_ticket( $ticket, $show_none = true ) {
		$label = '';

		$setting = $this->get_iac_setting_for_ticket( $ticket );

		// If setting is none and we don't want to show it.
		if ( ! $show_none && self::NONE_KEY === $setting ) {
			return $label;
		}

		$options = $this->get_iac_setting_options();
		$label   = isset( $options[ $setting ] ) ? $options[ $setting ] : $label;

		return apply_filters( 'tribe_tickets_plus_attendee_registration_iac_setting_label_for_ticket', $label, $ticket );
	}

	/**
	 * Get the IAC form field configurations to use in meta forms.
	 *
	 * @since 5.1.0
	 *
	 * @param int $ticket_id The ticket ID.
	 *
	 * @return array List of IAC form field configurations.
	 */
	public function get_field_configurations( $ticket_id ) {
		$ticket_iac_setting = $this->get_iac_setting_for_ticket( $ticket_id );

		if ( self::NONE_KEY === $ticket_iac_setting ) {
			return [];
		}

		$is_iac_allowed  = self::ALLOWED_KEY === $ticket_iac_setting;
		$is_iac_required = self::REQUIRED_KEY === $ticket_iac_setting;

		$field_required = $is_iac_required ? 'on' : '';
		$placeholder    = $is_iac_allowed ? __( 'Optional', 'event-tickets-plus' ) : '';

		$name_field = [
			'id'          => 0,
			'type'        => 'text',
			'label'       => __( 'Name', 'event-tickets-plus' ),
			'placeholder' => $placeholder,
			'slug'        => $this->get_iac_ticket_field_slug_for_name(),
			'required'    => $field_required,
			'classes'     => [
				'tribe-tickets__iac-field',
				'tribe-tickets__iac-field--name',
				'tribe-tickets__form-field--unique' => $is_iac_required,
			],
		];

		$email_field = [
			'id'          => 0,
			'type'        => 'email',
			'label'       => __( 'Email', 'event-tickets-plus' ),
			'placeholder' => $placeholder,
			'slug'        => $this->get_iac_ticket_field_slug_for_email(),
			'required'    => $field_required,
			'classes'     => [
				'tribe-tickets__iac-field',
				'tribe-tickets__iac-field--email',
				'tribe-tickets__form-field--unique' => $is_iac_required,
			],
			'extra'       => [
				'attributes' => [
					'data-resend-limit-reached' => '0',
				],
			],
		];

		$fields = [
			'name'  => $name_field,
			'email' => $email_field,
		];

		/**
		 * Allow filtering the list of IAC form field configurations.
		 *
		 * @since 5.1.0
		 *
		 * @param array  $fields             List of IAC form field configurations.
		 * @param string $ticket_iac_setting The IAC setting for the ticket (none, allowed, required).
		 * @param int    $ticket_id          The ticket ID.
		 */
		$fields = apply_filters( 'tribe_tickets_plus_attendee_registration_iac_fields', $fields, $ticket_iac_setting, $ticket_id );

		// Remove keys from the array.
		$fields = array_values( $fields );

		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta = tribe( 'tickets-plus.meta' );

		// Build the field configurations.
		$fields = $meta->generate_fields( $ticket_id, $fields );

		// Remove invalid fields.
		$fields = array_filter( $fields );

		return $fields;
	}

	/**
	 * Get the IAC attendee name from meta, if set.
	 *
	 * @since 5.1.0
	 *
	 * @param int|null                $attendee_number The attendee number index value from the order, starting with zero.
	 * @param int                     $order_id        The order ID.
	 * @param int                     $ticket_id       The ticket ID.
	 * @param int                     $post_id         The ID of the post associated to the ticket.
	 * @param Tribe__Tickets__Tickets $provider        The current ticket provider object.
	 *
	 * @return string The IAC attendee name from meta, if set.
	 */
	public function get_iac_attendee_name_from_attendee( $attendee_number, $order_id, $ticket_id, $post_id, $provider ) {
		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta = tribe( 'tickets-plus.meta' );

		$iac_attendee_name = $meta->get_meta_field_value_from_key_for_attendee( $this->get_iac_ticket_field_slug_for_name(), $ticket_id, $attendee_number, $order_id );

		if ( null === $iac_attendee_name ) {
			return '';
		}

		return $iac_attendee_name;
	}

	/**
	 * Get the IAC attendee email from meta, if set.
	 *
	 * @since 5.1.0
	 *
	 * @param int|null                $attendee_number The attendee number index value from the order, starting with zero.
	 * @param int                     $order_id        The order ID.
	 * @param int                     $ticket_id       The ticket ID.
	 * @param int                     $post_id         The ID of the post associated to the ticket.
	 * @param Tribe__Tickets__Tickets $provider        The current ticket provider object.
	 *
	 * @return string The IAC attendee email from meta, if set.
	 */
	public function get_iac_attendee_email_from_attendee( $attendee_number, $order_id, $ticket_id, $post_id, $provider ) {
		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta = tribe( 'tickets-plus.meta' );

		$iac_attendee_email = $meta->get_meta_field_value_from_key_for_attendee( $this->get_iac_ticket_field_slug_for_email(), $ticket_id, $attendee_number, $order_id );

		if ( null === $iac_attendee_email ) {
			return '';
		}

		return $iac_attendee_email;
	}

	/**
	 * Get the IAC override meta value for a specific field.
	 *
	 * @since 5.1.0
	 *
	 * @param int                                               $attendee_id The attendee ID.
	 * @param \Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field       The field object.
	 *
	 * @return null|string The value (default is null).
	 */
	public function get_iac_override_meta_value( $attendee_id, \Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field ) {
		/** @var \Tribe__Tickets__Data_API $data_api */
		$data_api = tribe( 'tickets.data_api' );

		/** @var \Tribe__Tickets__Tickets $provider */
		$provider = $data_api->get_ticket_provider( $attendee_id );

		// Provider not set/found.
		if ( empty( $provider ) ) {
			return null;
		}

		$attendees = $provider->get_all_attendees_by_attendee_id( $attendee_id );

		if ( empty( $attendees ) ) {
			return null;
		}

		$attendee = reset( $attendees );

		if ( $this->get_iac_ticket_field_slug_for_name() === $field->slug ) {
			return $attendee['holder_name'];
		}

		if ( $this->get_iac_ticket_field_slug_for_email() === $field->slug ) {
			return $attendee['holder_email'];
		}

		return null;
	}
}
