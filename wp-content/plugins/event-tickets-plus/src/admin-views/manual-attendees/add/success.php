<?php
/**
 * Manual Attendees success message on add.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/manual-attendees/add/success.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1amp
 *
 * @since 5.2.0
 *
 * @version 5.2.0
 *
 * @var Tribe__Tickets_Plus__Admin__Views    $this             [Global] Template object.
 * @var false|Tribe__Tickets__Tickets        $provider         [Global] The tickets provider class.
 * @var string                               $provider_class   [Global] The tickets provider class name.
 * @var string                               $provider_orm     [Global] The tickets provider ORM name.
 * @var null|Tribe__Tickets__Ticket_Object   $ticket           [Global] The ticket to add/edit.
 * @var null|int                             $ticket_id        [Global] The ticket ID to add/edit.
 * @var Tribe__Tickets__Ticket_Object[]      $tickets          [Global] List of tickets for the given post.
 * @var Tribe__Tickets__Commerce__Currency   $currency         [Global] Tribe Currency object.
 * @var bool                                 $is_rsvp          [Global] True if the ticket to add/edit an attendee is RSVP.
 * @var int                                  $attendee_id      [Global] The attendee ID.
 * @var string                               $attendee_name    [Global] The attendee name.
 * @var string                               $attendee_email   [Global] The attendee email.
 * @var int                                  $post_id          [Global] The post ID.
 * @var string                               $step             [Global] The step the views are on.
 * @var bool                                 $multiple_tickets [Global] If there's more than one ticket for the event.
 */

$classes = [
	'tribe-tickets__manual-attendees-success',
	'tribe-common-b1',
	'tribe-common-b2--min-medium',
];
?>

<div <?php tribe_classes( $classes ); ?>>
	<p><?php esc_html_e( 'New attendee has been added for this event.', 'event-tickets-plus' ); ?></p>
	<p><?php esc_html_e( 'This page will refresh automatically. Do not leave or manually refresh this page.', 'event-tickets-plus' ); ?></p>
</div>

<?php
$this->template( 'manual-attendees/loader' );
