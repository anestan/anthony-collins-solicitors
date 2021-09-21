<?php

namespace Tribe\Tickets\Plus\Repositories\Attendee;

use Tribe__Repository__Decorator;
use Tribe\Tickets\Plus\Repositories\Traits\Attendee;

/**
 * The Event Tickets Plus ORM/Repository decorator for Tribe Commerce attendees.
 *
 * @since 5.2.0
 *
 * @property \Tribe__Tickets__Repositories__Attendee__Commerce $decorated
 */
class Commerce extends Tribe__Repository__Decorator {

	use Attendee;

	/**
	 * Tribe Commerce constructor.
	 *
	 * Gets the current ET Tribe Commerce repository instance to extend.
	 *
	 * @since 5.2.0
	 */
	public function __construct() {
		$this->decorated = tribe( 'tickets.attendee-repository.commerce' );

		// Set up the update field aliases.
		$update_fields_aliases = $this->get_attendee_update_fields_aliases();

		foreach ( $update_fields_aliases as $alias => $field_name ) {
			$this->decorated->add_update_field_alias( $alias, $field_name );
		}
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
	public function setup_attendee_args( $args, $attendee_data, $ticket = null ) {
		return $this->decorated->setup_attendee_args(
			$this->handle_setup_attendee_args( $args, $attendee_data, $ticket ),
			$attendee_data,
			$ticket
		);
	}
}
