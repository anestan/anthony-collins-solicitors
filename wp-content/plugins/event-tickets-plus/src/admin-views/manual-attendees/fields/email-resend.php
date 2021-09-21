<?php
/**
 * Manual Attendees: Form email resend template.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/manual-attendees/fields/email-resend.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://m.tri.be/1amp
 *
 * @since 5.2.0
 *
 * @version 5.2.0
 *
 * @var Tribe__Tickets_Plus__Admin__Views    $this                  [Global] Template object.
 * @var false|Tribe__Tickets__Tickets        $provider              [Global] The tickets provider class.
 * @var string                               $provider_class        [Global] The tickets provider class name.
 * @var string                               $provider_orm          [Global] The tickets provider ORM name.
 * @var null|Tribe__Tickets__Ticket_Object   $ticket                [Global] The ticket to add/edit.
 * @var null|int                             $ticket_id             [Global] The ticket ID to add/edit.
 * @var Tribe__Tickets__Ticket_Object[]      $tickets               [Global] List of tickets for the given post.
 * @var Tribe__Tickets__Commerce__Currency   $currency              [Global] Tribe Currency object.
 * @var bool                                 $is_rsvp               [Global] True if the ticket to add/edit an attendee is RSVP.
 * @var array                                $attendee              [Global] The attendee information.
 * @var int                                  $attendee_id           [Global] The attendee ID.
 * @var string                               $attendee_name         [Global] The attendee name.
 * @var string                               $attendee_email        [Global] The attendee email.
 * @var array                                $attendee_meta         [Global] The attendee meta field values.
 * @var int                                  $post_id               [Global] The post ID.
 * @var string                               $step                  [Global] The step the views are on.
 * @var bool                                 $multiple_tickets      [Global] If there's more than one ticket for the event.
 * @var bool                                 $allow_resending_email [Global] If resending email is allowed.
 */

// Bail if we're not on edit or there's no ticket.
if ( 'edit' !== $step || empty( $ticket_id ) ) {
	return;
}

// Bail if resending email is not allowed.
if ( empty( $allow_resending_email ) ) {
	return;
}

$label = ! empty( $is_rsvp ) ? __( 'Re-send RSVP email', 'event-tickets-plus' ) : __( 'Re-send ticket email', 'event-tickets-plus' );
?>
<div class="tribe-tickets__manual-attendees-resend-email tribe-common-form-control-checkbox-radio-group tribe-common-a11y-hidden">
	<div class="tribe-common-form-control-checkbox">
		<label
			class="tribe-common-form-control-checkbox__label"
			for="tribe-tickets__manual-attendees-resend-email"
		>
			<input
				type="checkbox"
				class="tribe-common-form-control-checkbox__input tribe-tickets__form-field-input tribe-tickets__form-field-input--checkbox tribe-tickets__manual-attendees-resend-email-input"
				name="tribe_tickets[<?php echo esc_attr( absint( $ticket_id ) ); ?>][attendees][<?php echo esc_attr( absint( $attendee_id ) ); ?>][tribe-tickets-plus-ma-email-resend]"
				id="tribe-tickets__manual-attendees-resend-email"
			/>
			<?php echo esc_html( $label ); ?>
		</label>
	</div>
</div>
