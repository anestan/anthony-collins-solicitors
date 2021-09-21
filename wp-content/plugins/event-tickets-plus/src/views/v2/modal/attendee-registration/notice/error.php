<?php
/**
 * Modal: Attendee Registration > Notice > Error.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/attendee-registration/notice/error.php
 *
 * @link https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 5.1.0
 *
 * @version 5.1.0
 */

$notice_classes = [
	'tribe-tickets__notice--error',
	'tribe-tickets__validation-notice',
];

/** @var Tribe__Tickets__Editor__Template $et_template */
$et_template = tribe( 'tickets.editor.template' );

$et_template->template(
	'components/notice',
	[
		'id'             => 'tribe-tickets__notice-modal-attendee',
		'notice_classes' => $notice_classes,
		'content'        => sprintf(
			// Translators: %s: The HTML wrapped number of tickets.
			esc_html_x(
				'You have %s ticket(s) with a field that requires information.',
				'Note about missing required fields, %s is the html-wrapped number of tickets.',
				'event-tickets-plus'
			),
			'<span class="tribe-tickets__notice--error__count">1</span>'
		),
	]
);
