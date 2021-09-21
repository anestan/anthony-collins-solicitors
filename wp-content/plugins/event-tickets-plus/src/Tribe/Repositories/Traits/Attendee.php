<?php
/**
 * The Attendees trait that contains all of the necessary ORM functionality to be used by any repository.
 *
 * @since   5.2.0
 *
 * @package Tribe\Tickets\Plus\Repositories\Traits
 */

namespace Tribe\Tickets\Plus\Repositories\Traits;

use Tribe__Tickets_Plus__Meta;

/**
 * Class Attendee
 *
 * @since 5.2.0
 */
trait Attendee {

	/**
	 * Get the list of Attendee-specific update fields aliases.
	 *
	 * @since 5.2.0
	 *
	 * @return array List of Attendee-specific update fields aliases.
	 */
	public function get_attendee_update_fields_aliases() {
		$update_fields_aliases = [];

		// Meta saving.
		$update_fields_aliases['attendee_meta'] = Tribe__Tickets_Plus__Meta::META_KEY;

		// Attendee source / author.
		$update_fields_aliases['attendee_source']   = '_tribe_attendee_source';
		$update_fields_aliases['attendee_added_by'] = '_tribe_attendee_added_by';

		return $update_fields_aliases;
	}

	/**
	 * Set up the arguments to set for the attendee for this provider.
	 *
	 * @since 5.2.0
	 *
	 * @param array                              $args          List of arguments to set for the attendee.
	 * @param array                              $attendee_data List of additional attendee data.
	 * @param null|Tribe__Tickets__Ticket_Object $ticket        The ticket object or null if not relying on it.
	 *
	 * @return array List of arguments to set for the attendee.
	 */
	public function handle_setup_attendee_args( $args, $attendee_data, $ticket = null ) {
		if ( isset( $args['attendee_meta'] ) ) {
			/**
			 * Allow filtering the attendee meta to be saved to the attendee by the repository.
			 *
			 * @since 5.2.0
			 *
			 * @param array                              $attendee_meta The attendee meta to be saved to the attendee.
			 * @param array                              $args          List of arguments to set for the attendee.
			 * @param array                              $attendee_data List of additional attendee data.
			 * @param null|Tribe__Tickets__Ticket_Object $ticket        The ticket object or null if not relying on it.
			 */
			$args['attendee_meta'] = apply_filters( 'tribe_tickets_plus_repositories_traits_attendee_meta', $args['attendee_meta'], $args, $attendee_data, $ticket );
		}

		return $args;
	}
}
