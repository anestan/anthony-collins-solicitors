<?php
/**
 * Manual attendees: Ticket price information.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/manual-attendees/ticket-information/price.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://m.tri.be/1amp
 *
 * @since   5.2.0
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

// Bail if it's RSVP.
if ( ! empty( $is_rsvp ) ) {
	return;
}

$has_suffix = ! empty( $ticket->price_suffix );
?>
<span class="tribe-tickets__manual-attendees-ticket-info-price">
	<?php if ( ! empty( $ticket->on_sale ) ) : ?>
		<span class="tribe-common-b2 tribe-tickets__manual-attendees-ticket-info-price-original">
			<?php
				// phpcs:ignore
				echo $currency->get_formatted_currency_with_symbol( $ticket->regular_price, $post_id, $provider->class_name );
			?>
		</span>
	<?php endif; ?>
	<span class="tribe-tickets__manual-attendees-ticket-info-price-sale">
		<?php echo $currency->get_formatted_currency_with_symbol( $ticket->price, $post_id, $provider->class_name ); ?>
		<?php if ( $has_suffix ) : ?>
			<span class="tribe-tickets__manual-attendees-ticket-info-price-suffix tribe-common-b2">
				<?php
				// This suffix contains HTML to be output.
				// phpcs:ignore
				echo $ticket->price_suffix;
				?>
			</span>
		<?php endif; ?>
	</span>
</span>
