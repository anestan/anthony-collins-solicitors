<?php

namespace Tribe\Tickets\Plus\Commerce\EDD;

use Tribe\Tickets\Promoter\Triggers\Models\Attendee as Ticket_Attendees;

class Attendee extends Ticket_Attendees {
	/**
	 * @inheritDoc
	 */
	public function required_fields() {
		return [
			'attendee_id',
			'purchaser_email',
			'event_id',
			'product_id',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function email() {
		return $this->data['purchaser_email'];
	}
}