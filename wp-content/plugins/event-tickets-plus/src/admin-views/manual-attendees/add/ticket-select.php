<?php
/**
 * Manual Attendees: Add Form, Ticket select.
 * Here, the user chooses which ticket/RSVP they would want to add an attendee for.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/manual-attendees/add/ticket-select.php
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

// Bail if there's only one ticket.
if ( empty( $multiple_tickets ) ) {
	return;
}

?>

<div class="tribe-tickets__manual-attendees-tickets-select">
	<div class="tribe-common-b1 tribe-common-b2--min-medium tribe-tickets__form-field tribe-tickets__form-field--select">
		<label
			class="tribe-tickets__form-field-label screen-reader-text tribe-common-a11y-visual-hide"
			for="tribe-tribe-tickets-manual-attendees-ticket-select"
		>
			<?php esc_html_e( 'Select a ticket', 'event-tickets-plus' ); ?>
		</label>
		<div class="tribe-tickets__form-field-input-wrapper">
			<select
				name="tribe-tickets-manual-attendees-ticket-select"
				id="tribe-tickets-manual-attendees-ticket-select"
				class="tribe-tickets__manual-attendees-add-attendee-ticket-select"
			>
				<option value=""><?php esc_html_e( 'Select a ticket', 'event-tickets-plus' ); ?></option>

				<?php foreach ( $tickets as $post_ticket ) : ?>
					<option
						value="<?php echo esc_attr( $post_ticket->ID ); ?>"
						<?php selected( $ticket_id, $post_ticket->ID ); ?>
					><?php echo wp_kses_post( $post_ticket->name ); ?></option>

				<?php endforeach; ?>

			</select>
		</div>
	</div>

	<?php $this->template( 'manual-attendees/add/availability' ); ?>
</div>
