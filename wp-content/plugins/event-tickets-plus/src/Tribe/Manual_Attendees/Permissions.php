<?php

namespace Tribe\Tickets\Plus\Manual_Attendees;

/**
 * Class Permissions
 * This handles all permissions and capability checks regarding Manual Attendee feature.
 *
 * @package Tribe\Tickets\Plus\Manual_Attendees
 *
 * @since 5.2.0
 */
class Permissions {

	/**
	 * Array of caps that are allowed for adding.
	 *
	 * @since 5.2.0
	 *
	 * @var array string[]
	 */
	protected $add_caps = [ 'edit_posts' ];

	/**
	 * Array of caps that are allowed for editing.
	 *
	 * @since 5.2.0
	 *
	 * @var array string[]
	 */
	protected $edit_caps = [ 'edit_posts' ];

	/**
	 * Check if given user is allowed to add attendees.
	 *
	 * @since 5.2.0
	 *
	 * @param int|\WP_User $user User to check permission for.
	 *
	 * @return bool Whether the user is allowed to add attendees.
	 */
	public function is_allowed_to_add( $user ) {
		/**
		 * Filter if the user is allowed to add attendees manually.
		 *
		 * @since 5.2.0
		 *
		 * @param bool         $allowed_to_add Whether the user is allowed to add attendees.
		 * @param int|\WP_User $user           User to check permission for.
		 */
		return apply_filters( 'tribe_tickets_plus_manual_attendees_user_allowed_to_add', $this->user_has_caps( $user, $this->get_add_caps() ), $user );
	}

	/**
	 * Check if given user is allowed to edit attendees.
	 *
	 * @since 5.2.0
	 *
	 * @param int|\WP_User $user User to check permission for.
	 *
	 * @return bool Whether the user is allowed to edit attendees.
	 */
	public function is_allowed_to_edit( $user ) {
		/**
		 * Filter if the user is allowed to edit attendees manually.
		 *
		 * @since 5.2.0
		 *
		 * @param bool         $allowed_to_edit Whether the user is allowed to edit attendees.
		 * @param int|\WP_User $user            User to check permission for.
		 */
		return apply_filters( 'tribe_tickets_plus_manual_attendees_user_allowed_to_edit', $this->user_has_caps( $user, $this->get_edit_caps() ), $user );
	}

	/**
	 * Get the capabilities that are allowed for adding attendees.
	 *
	 * @since 5.2.0
	 *
	 * @return array The list of capabilities that are allowed for adding attendees.
	 */
	public function get_add_caps() {
		/**
		 * Filter the allowed capabilities to add attendees manually.
		 *
		 * @since 5.2.0
		 *
		 * @param array List of capabilties.
		 */
		return apply_filters( 'tribe_tickets_plus_manual_attendees_add_capabilities', $this->add_caps );
	}

	/**
	 * Get the capabilities that are allowed for editing attendees.
	 *
	 * @since 5.2.0
	 *
	 * @return array The list of capabilities that are allowed for editing attendees.
	 */
	public function get_edit_caps() {
		/**
		 * Filter the allowed capabilities to edit attendees manually.
		 *
		 * @since 5.2.0
		 *
		 * @param array List of capabilties.
		 */
		return apply_filters( 'tribe_tickets_plus_manual_attendees_edit_capabilities', $this->edit_caps );
	}

	/**
	 * Check if user has any of provided capabilities.
	 *
	 * @since 5.2.0
	 *
	 * @param int|\WP_User $user User ID or Object.
	 * @param array        $caps List of capabilities.
	 *
	 * @return bool Whether the user has any of the provided capabilities.
	 */
	public function user_has_caps( $user, $caps ) {

		if ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		}

		if ( ! $user instanceof \WP_User ) {
			return false;
		}

		$has_caps = array_intersect( $caps, array_keys( $user->get_role_caps() ) );

		return ! empty( $has_caps );
	}
}
