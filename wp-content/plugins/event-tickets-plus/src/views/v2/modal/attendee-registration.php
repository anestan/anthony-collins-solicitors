<?php
/**
 * Modal: Attendee Registration.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/attendee-registration.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var string $provider_class The class name for the provider.
 */

$non_meta_count = 0;

// Set the CSS classes.
$classes = [
	'tribe-tickets__attendee-tickets-form',
	sanitize_html_class( $provider_class ),
	'tribe-validation',
];
?>
<div class="tribe-tickets__attendee-tickets">

	<?php $this->template( 'v2/modal/attendee-registration/title' ); ?>

	<?php $this->template( 'v2/modal/attendee-registration/notice/error' ); ?>

	<div
		id="tribe-modal__attendee-registration"
		<?php tribe_classes( $classes ); ?>
		method="post"
		name="event<?php echo esc_attr( $post_id ); ?>"
		autocomplete="off"
		novalidate
	>
		<?php foreach ( $tickets as $ticket ) : ?>
			<?php
			// Only include tickets with meta.
			if ( ! $ticket->has_meta_enabled() ) {
				$non_meta_count++;
				continue;
			}
			?>
			<div class="tribe-tickets__attendee-tickets-container" data-ticket-id="<?php echo esc_attr( $ticket->ID ); ?>">
				<h3 class="tribe-common-h5 tribe-common-h5--min-medium tribe-common-h--alt tribe-ticket__tickets-heading">
					<?php echo esc_html( get_the_title( $ticket->ID ) ); ?>
				</h3>
			</div>
		<?php endforeach; ?>

		<?php $this->template( 'v2/modal/attendee-registration/notice/non-ar', [ 'non_meta_count' => $non_meta_count ] ); ?>

		<input type="hidden" name="tribe_tickets_saving_attendees" value="1" />
		<input type="hidden" name="tribe_tickets_ar" value="1" />
		<input type="hidden" name="tribe_tickets_ar_data" value="" id="tribe_tickets_ar_data" />

		<?php $this->template( 'v2/modal/attendee-registration/footer' ); ?>

		</form>

	</div>
