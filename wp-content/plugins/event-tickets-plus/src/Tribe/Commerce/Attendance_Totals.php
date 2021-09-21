<?php
/**
 * Calculates attendance totals for a specified event (ie, how many tickets
 * have been sold and how many are pending further action, etc).
 *
 * Also has the capability to print this information as HTML, intended for
 * use in the attendee summary screen.
 *
 * Note that the totals are calculated upon instantiation, effectively making
 * the object a snapshot in time. Therefore if the status of one or more tickets
 * is modified or if tickets are added/deleted later in the request, it would be
 * necessary to obtain a new object of this type to get accurate results.
 */
class Tribe__Tickets_Plus__Commerce__Attendance_Totals extends Tribe__Tickets__Abstract_Attendance_Totals {
	protected $total_paid      = 0;
	protected $total_pending   = 0;
	protected $total_cancelled = 0;
	protected $total_refunded  = 0;

	/**
	 * Calculate totals for the current event.
	 */
	protected function calculate_totals() {
		$tickets = Tribe__Tickets__Tickets::get_event_tickets( $this->event_id );

		foreach ( $tickets as $ticket ) {
			/** @var Tribe__Tickets__Ticket_Object $ticket */
			if ( ! $this->should_count( $ticket ) ) {
				continue;
			}

			$this->total_paid      += $ticket->qty_sold();
			$this->total_pending   += $ticket->qty_pending();
			$this->total_cancelled += $ticket->qty_cancelled();
			$this->total_refunded  += $ticket->qty_refunded();
		}
	}

	/**
	 * Indicates if the ticket should be factored into our sales counts.
	 *
	 * @param Tribe__Tickets__Ticket_Object $ticket
	 *
	 * @return bool
	 */
	protected function should_count( Tribe__Tickets__Ticket_Object $ticket ) {
		$should_count = 'Tribe__Tickets__RSVP' !== $ticket->provider_class;

		/**
		 * Determine if the provided ticket object should be used when building
		 * sales counts.
		 *
		 * By default, tickets belonging to the Tribe__Tickets__RSVP provider
		 * are not to be counted.
		 *
		 * @param bool $should_count
		 * @param Tribe__Tickets__Ticket_Object $ticket
		 */
		return (bool) apply_filters( 'tribe_tickets_plus_should_use_ticket_in_sales_counts', $should_count, $ticket );
	}

	/**
	 * Prints an HTML (unordered) list of attendance totals.
	 */
	public function print_totals() {
		$args = [
			'total_sold_label'        => esc_html( sprintf( _x( 'Total %s:', 'attendee summary', 'event-tickets-plus' ), tribe_get_ticket_label_plural( 'attendance_totals' ) ) ),
			'total_complete_label'    => _x( 'Complete:', 'attendee summary', 'event-tickets-plus' ),
			'total_cancelled_label'   => _x( 'Cancelled:', 'attendee summary', 'event-tickets-plus' ),
			'total_sold'              => $this->get_total_sold(),
			'total_complete'          => $this->get_total_complete(),
			'total_cancelled'         => $this->get_total_cancelled(),
			'total_refunded'          => $this->get_total_refunded(),
			'total_sold_tooltip'      => $this->get_total_sold_tooltip(),
			'total_completed_tooltip' => $this->get_total_completed_tooltip(),
			'total_cancelled_tooltip' => $this->get_total_cancelled_tooltip(),
			'total_refunded_tooltip'  => $this->get_total_refunded_tooltip(),
		];

		/** @var Tribe__Tickets__Admin__Views $admin_views */
		$admin_views = tribe( 'tickets.admin.views' );
		$admin_views->template( 'attendees-totals-list', $args, true );
	}

	/**
	 * The total number of tickets sold for this event.
	 *
	 * @since 4.3
	 *
	 * @return int
	 */
	public function get_total_sold() {
		$total_sold = $this->get_total_paid() + $this->get_total_pending();

		/**
		 * Returns the total tickets sold for an event.
		 *
		 * @since 4.3
		 *
		 * @param int $total_sold          Total number of tickets sold.
		 * @param int $original_total_sold Original total number of tickets sold.
		 * @param int $event_id            Event ID.
		 */
		return (int) apply_filters( 'tribe_tickets_plus_get_total_sold', $total_sold, $total_sold, $this->event_id );
	}

	/**
	 * The total number of tickets pending further action for this event.
	 *
	 * @since 4.3
	 *
	 * @return int
	 */
	public function get_total_pending() {
		/**
		 * Returns the total tickets pending further action for an event.
		 *
		 * @since 4.6
		 *
		 * @param int $total_pending          Total number of tickets pending.
		 * @param int $original_total_pending Original total number of tickets pending.
		 * @param int $event_id               Event ID.
		 */
		return (int) apply_filters( 'tribe_tickets_plus_get_total_pending', $this->total_pending, $this->total_pending, $this->event_id );
	}

	/**
	 * The total number of tickets sold and paid for, minus cancelled and refunded, for this event.
	 *
	 * @since 4.3
	 *
	 * @return int
	 */
	public function get_total_complete() {
		$total_complete = $this->get_total_paid() - $this->get_total_cancelled() - $this->get_total_refunded();

		/**
		 * Returns the total tickets completed for an event.
		 *
		 * @since 4.10.7
		 *
		 * @param int $total_complete          Total number of tickets completed.
		 * @param int $original_total_complete Original total number of tickets completed.
		 * @param int $event_id                Event ID.
		 */
		return (int) apply_filters( 'tribe_tickets_plus_get_total_complete', $total_complete, $total_complete, $this->event_id );
	}

	/**
	 * The total number of tickets sold and paid for, for this event.
	 *
	 * @since  4.6
	 *
	 * @return int
	 */
	public function get_total_paid() {
		/**
		 * Returns the total tickets sold and paid for, for an event.
		 *
		 * @since 4.3
		 *
		 * @param int $total_paid          Total number of tickets paid.
		 * @param int $original_total_paid Original total number of tickets paid.
		 * @param int $event_id            Event ID.
		 */
		return (int) apply_filters( 'tribe_tickets_plus_get_total_paid', $this->total_paid, $this->total_paid, $this->event_id );
	}

	/**
	 * The total number of tickets sold then cancelled, for this event.
	 *
	 * @since  4.10.4
	 *
	 * @return int
	 */
	public function get_total_cancelled() {
		/**
		 * Returns the total tickets cancelled, for an event.
		 *
		 * @since 4.10.4
		 *
		 * @param int $total_cancelled          Total number of tickets cancelled.
		 * @param int $original_total_cancelled Original total number of tickets cancelled.
		 * @param int $event_id                 Event ID.
		 */
		return (int) apply_filters( 'tribe_tickets_plus_get_total_cancelled', $this->total_cancelled, $this->total_cancelled, $this->event_id );
	}

	/**
	 * The total number of tickets sold then refunded, for this event.
	 *
	 * @since 4.10.7
	 *
	 * @return int Total number of tickets refunded.
	 */
	public function get_total_refunded() {
		/**
		 * Returns the total tickets refunded, for an event.
		 *
		 * @since 4.10.7
		 *
		 * @param int $total_refunded          Total number of tickets refunded.
		 * @param int $original_total_refunded Original total number of tickets refunded.
		 * @param int $event_id                Event ID.
		 */
		return (int) apply_filters( 'tribe_tickets_plus_get_total_refunded', $this->total_refunded, $this->total_refunded, $this->event_id );
	}
}
