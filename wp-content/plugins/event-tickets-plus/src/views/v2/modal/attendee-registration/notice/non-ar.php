<?php
/**
 * Modal: Attendee Registration > Notice > Non AR tickets.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/attendee-registration/notice/non-ar.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since 5.1.0
 * @since   5.2.5 Added plural/singular support for the notice string content.
 *
 * @version 5.2.5
 *
 * @var int $non_meta_count The number of tickets, without meta fields.
 */

$this->template( 'v2/modal/attendee-registration/notice/non-ar/singular' );

$this->template( 'v2/modal/attendee-registration/notice/non-ar/plural' );
