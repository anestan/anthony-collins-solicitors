<?php
/**
 * Manual Attendees: Add Form
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/manual-attendees/add.php
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
	'tribe-tickets__form',
	'tribe-tickets__manual-attendees-form',
	'tribe-tickets__manual-attendees-form--rsvp' => ! empty( $is_rsvp ),
	'tribe-tickets__manual-attendees-form--add'  => 'add' === $step,
];

?>

<?php $this->template( 'manual-attendees/add/message-full' ); ?>

<form
	<?php tribe_classes( $classes ); ?>
	id="tribe-tickets__manual-attendees-form"
	autocomplete="off"
	method="post"
	enctype='multipart/form-data'
	data-provider="<?php echo esc_attr( $provider_class ); ?>"
	data-provider-id="<?php echo esc_attr( $provider_orm ); ?>"
	data-post-id="<?php echo esc_attr( $post_id ); ?>"
	data-ticket-id="<?php echo esc_attr( $ticket_id ); ?>"
	novalidate
>

	<?php $this->template( 'manual-attendees/add/ticket-select' ); ?>

	<?php $this->template( 'manual-attendees/ticket-information' ); ?>

	<?php $this->template( 'manual-attendees/message-error' ); ?>

	<?php $this->template( 'manual-attendees/fields/name' ); ?>

	<?php $this->template( 'manual-attendees/fields/email' ); ?>

	<?php $this->template( 'manual-attendees/fields/meta-fields' ); ?>

	<?php $this->template( 'manual-attendees/add/message-price' ); ?>

	<?php $this->template( 'manual-attendees/add/buttons' ); ?>
</form>

<?php
$this->template( 'manual-attendees/loader' );
