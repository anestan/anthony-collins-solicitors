<?php
if ( ! function_exists( 'tribe_tickets_is_edd_active' ) ) {
	/**
	 * Check if Easy Digital Downloads is active.
	 *
	 * @since 4.7.3
	 * @since 4.12.3 Changed from class_exists() check to function_exists() check.
	 *
	 * @return bool Whether the core ecommerce plugin is active.
	 */
	function tribe_tickets_is_edd_active() {
		return function_exists( 'EDD' );
	}
}

if ( ! function_exists( 'tribe_tickets_is_woocommerce_active' ) ) {
	/**
	 * Check if WooCommerce is active.
	 *
	 * @since 4.12.3
	 *
	 * @return bool Whether the core ecommerce plugin is active.
	 */
	function tribe_tickets_is_woocommerce_active() {
		return function_exists( 'WC' );
	}
}

if ( ! function_exists( 'tribe_tickets_plus_meta_field_is_required' ) ) {
	/**
	 * Check if the AR field is required.
	 *
	 * @since 5.0.0
	 *
	 * @param object $field The field object.
	 *
	 * @return bool True if is required
	 */
	function tribe_tickets_plus_meta_field_is_required( $field ) {
		return $field->is_required();
	}
}

if ( ! function_exists( 'tribe_tickets_plus_meta_field_get_attendee_id' ) ) {
	/**
	 * Get the attendee ID for the meta field.
	 *
	 * @since 5.0.0
	 *
	 * @param string|null $attendee_id The attendee ID or null to default to dynamic ID.
	 *
	 * @return string The AR field name.
	 */
	function tribe_tickets_plus_meta_field_get_attendee_id( $attendee_id = null ) {
		if ( null === $attendee_id ) {
			return '{{data.attendee_id}}';
		}

		return $attendee_id;
	}
}

if ( ! function_exists( 'tribe_tickets_plus_meta_field_name' ) ) {
	/**
	 * Build the AR meta field name.
	 *
	 * @since 5.0.0
	 *
	 * @param int         $ticket_id   The ticket ID.
	 * @param string|null $field_slug  The field slug.
	 * @param string|null $attendee_id The attendee ID or null to default to dynamic ID.
	 *
	 * @return string The AR field name.
	 */
	function tribe_tickets_plus_meta_field_name( $ticket_id, $field_slug, $attendee_id = null ) {
		// Get attendee ID to use, possibly using default dynamic ID.
		$attendee_id = tribe_tickets_plus_meta_field_get_attendee_id( $attendee_id );

		$field_name = 'tribe_tickets[' . $ticket_id . '][attendees][' . $attendee_id . '][meta]';

		if ( null === $field_slug ) {
			return $field_name;
		}

		return $field_name . '[' . $field_slug . ']';
	}
}

if ( ! function_exists( 'tribe_tickets_plus_meta_field_id' ) ) {
	/**
	 * Build the AR field `id`.
	 *
	 * @since 5.0.0
	 *
	 * @param int         $ticket_id   The ticket ID.
	 * @param string      $field_slug  The field slug.
	 * @param string      $option_slug The field option slug (in case they need it).
	 * @param string|null $attendee_id The attendee ID or null to default to dynamic ID.
	 *
	 * @return string The AR field id.
	 */
	function tribe_tickets_plus_meta_field_id( $ticket_id, $field_slug, $option_slug = '', $attendee_id = null ) {
		// Get attendee ID to use, possibly using default dynamic ID.
		$attendee_id = tribe_tickets_plus_meta_field_get_attendee_id( $attendee_id );

		$field_id = "tribe-tickets_{$ticket_id}_{$field_slug}_{$attendee_id}";

		if ( ! empty( $option_slug ) ) {
			$field_id .= "_{$option_slug}";
		}

		return $field_id;
	}
}

if ( ! function_exists( 'tribe_tickets_ma_is_enabled' ) ) {
	/**
	 * Determine whether the MA feature is enabled.
	 *
	 * @todo Remove this function before release.
	 *
	 * In order: the function will check the constant, the environment variable, and then
	 * allow filtering.
	 *
	 * @since 5.2.0
	 *
	 * @return bool Whether the Manual Attendees feature is enabled.
	 */
	function tribe_tickets_ma_is_enabled() {
		// Check for constant.
		if ( defined( 'TRIBE_TICKETS_MA_ENABLED' ) ) {
			return (bool) TRIBE_TICKETS_MA_ENABLED;
		}

		// Check for env var.
		$env_var = getenv( 'TRIBE_TICKETS_MA_ENABLED' );

		if ( false !== $env_var ) {
			return (bool) $env_var;
		}

		/**
		 * Allows filtering whether the manual attendee is enabled.
		 *
		 * @since 5.2.0
		 *
		 * @param bool $enabled Whether the manual attendee is enabled.
		 *
		 * @var bool   $enabled Whether the manual attendee is enabled.
		 */
		return (bool) apply_filters( 'tribe_tickets_manual_attendees_enabled', true );
	}
}
