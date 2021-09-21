<?php

class Tribe__Tickets_Plus__Meta__Field__Checkbox extends Tribe__Tickets_Plus__Meta__Field__Abstract_Options_Field {
	public $type = 'checkbox';

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return _x( 'Checkbox', 'Attendee Information Field Name', 'event-tickets-plus' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_identifier() {
		return 'checkbox';
	}

	public function save_value( $attendee_id, $field, $value ) {
	}

	/**
	 * Get the formatted value.
	 *
	 * @since 5.2.0
	 *
	 * @param string|mixed $value The current value.
	 *
	 * @return string|mixed The formatted value.
	 */
	public function get_formatted_value( $value ) {
		if ( empty( $value ) ) {
			$value = [];
		}

		return (array) $value;
	}
}
