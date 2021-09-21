<?php
/**
 * Modal: Attendee Registration > Notice > Non AR tickets (Plural).
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/attendee-registration/notice/non-ar/plural.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since 5.2.5
 *
 * @version 5.2.5
 *
 * @var int $non_meta_count The number of tickets, without meta fields.
 */

$notice_classes = [
	'tribe-tickets__notice--non-ar',
	'tribe-tickets__notice--non-ar-plural',
	'tribe-common-a11y-hidden',
];

/** @var Tribe__Tickets__Editor__Template $et_template */
$et_template = tribe( 'tickets.editor.template' );

$et_template->template(
	'components/notice',
	[
		'notice_classes' => $notice_classes,
		'content'        => sprintf(
			// Translators: %s: The HTML wrapped number of tickets.
			esc_html_x(
				'There are %s other tickets in your cart that do not require attendee information.',
				'Note that there are more tickets in the cart, %s is the html-wrapped number.',
				'event-tickets-plus'
			),
			'<span class="tribe-tickets__non-ar-count">' . absint( $non_meta_count ) . '</span>'
		),
	]
);
