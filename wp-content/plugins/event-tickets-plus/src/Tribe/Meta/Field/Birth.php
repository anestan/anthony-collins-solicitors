<?php

/**
 * Class Tribe__Tickets_Plus__Meta__Field__Birth
 *
 * Adds a Birth Date field to RSVP and Tickets.
 *
 * To the user, it shows up as three selects: [ Month ] [ Day ] [ Year]. Internally, the values
 * are combined into a single field with the YYYY-MM-DD format to be stored in the database.
 *
 * @since 4.12.1
 */
class Tribe__Tickets_Plus__Meta__Field__Birth extends Tribe__Tickets_Plus__Meta__Field__Abstract_Field {
	/**
	 * Field type.
	 *
	 * @var string
	 */
	public $type = 'birth';

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return _x( 'Birth Date', 'Attendee Information Field Name', 'event-tickets-plus' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_identifier() {
		return 'birth';
	}

	/**
	 * Return an array whose values are from 01 to 31,
	 * with a leading zero on numbers bellow 10.
	 *
	 * To be used in the date format YYYY-MM-DD
	 *
	 * @since 4.12.1
	 *
	 * @return array
	 */
	public function get_days() {
		return array_map(
			static function ( $day ) {
				return str_pad( $day, 2, '0', STR_PAD_LEFT );
			},
			range( 1, 31 )
		);
	}

	/**
	 * Return an array whose keys are from 01 to 12,
	 * and the values are their translated month abbreviated names.
	 *
	 * @since 4.12.1
	 *
	 * @return array
	 */
	public function get_months() {
		/** @var WP_Locale $wp_locale */
		global $wp_locale;

		// Similar result to $wp_locale->month_genitive, but with abbreviated month names.
		// [ '1' => 'Jan', '2' => 'Feb', etc ]
		return array_combine( array_flip( $wp_locale->month ), $wp_locale->month_abbrev );
	}

	/**
	 * Return an array of years, starting
	 * from the current year and going down until 1900.
	 *
	 * @since 4.12.1
	 *
	 * @return array
	 */
	public function get_years() {
		return range( (int) date_i18n( 'Y' ), 1900 );
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
		$formatted_value = [
			'year'  => '',
			'month' => '',
			'day'   => '',
		];

		if ( ! empty( $value ) ) {
			// Format: YYYY-MM-DD.
			$value = explode( '-', $value );

			if ( 3 === count( $value ) ) {
				$formatted_value['year']  = $value[0];
				$formatted_value['month'] = $value[1];
				$formatted_value['day']   = $value[2];
			}
		}

		return $formatted_value;
	}
}
