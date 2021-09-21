<?php
/**
 * Manual Attendees: Form email input.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/manual-attendees/fields/email.php
 *
 * See more documentation about our Blocks Editor templating system.
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
 * @var array                                $attendee_meta    [Global] The attendee meta field values.
 * @var int                                  $post_id          [Global] The post ID.
 * @var string                               $step             [Global] The step the views are on.
 * @var bool                                 $multiple_tickets [Global] If there's more than one ticket for the event.
 */

if ( 'add' === $step && empty( $ticket_id ) ) {
	return;
}

?>
<div class="tribe-common-b1 tribe-common-b2--min-medium tribe-tickets__form-field tribe-tickets__form-field--required">
	<label
		class="tribe-tickets__form-field-label"
		for="tribe-tickets-rsvp-email-<?php echo esc_attr( $ticket_id ); ?>"
	>
		<?php esc_html_e( 'Email', 'event-tickets-plus' ); ?><span class="screen-reader-text"><?php esc_html_e( 'required', 'event-tickets-plus' ); ?></span>
		<span class="tribe-required" aria-hidden="true" role="presentation">*</span>
	</label>
	<div class="tribe-tickets__form-field-input-wrapper">
		<input
			type="email"
			class="tribe-common-form-control-text__input tribe-tickets__form-field-input tribe-tickets__manual-attendees-form-field-email"
			name="tribe_tickets[<?php echo esc_attr( absint( $ticket_id ) ); ?>][attendees][<?php echo esc_attr( absint( $attendee_id ) ); ?>][tribe-tickets-plus-ma-email]"
			id="tribe-tickets-rsvp-email-<?php echo esc_attr( $ticket_id ); ?>"
			value="<?php echo esc_attr( $attendee_email ); ?>"
			required
			placeholder="<?php esc_attr_e( 'attendee@email.com', 'event-tickets-plus' ); ?>"
		>
	</div>

	<?php $this->template( 'manual-attendees/fields/email-resend' ); ?>

</div>
