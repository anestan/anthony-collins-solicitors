<?php
/**
 * This template renders a single attendee's registration/purchase fields.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/attendee-registration/content/attendees/content.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var \Tribe\Tickets\Plus\Attendee_Registration\View $this                   [Global] The AR View instance.
 * @var array                                          $events                 [Global] Multidimensional array of post IDs with their ticket data.
 * @var string                                         $checkout_url           [Global] The checkout URL.
 * @var bool                                           $is_meta_up_to_date     [Global] True if the meta is up to date.
 * @var bool                                           $cart_has_required_meta [Global] True if the cart has required meta.
 * @var array                                          $providers              [Global] Array of providers, by event.
 * @var \Tribe__Tickets_Plus__Meta                     $meta                   [Global] Meta object.
 * @var \Closure                                       $field_render           [Global] Call to \Tribe\Tickets\Plus\Attendee_Registration\Fields::render().
 * @var \Tribe__Tickets__Commerce__Currency            $currency               [Global] The tribe commerce currency object.
 * @var mixed                                          $currency_config        [Global] Currency configuration for default provider.
 * @var bool                                           $is_modal               [Global] True if it's in the modal context.
 * @var int                                            $non_meta_count         [Global] Number of tickets without meta fields.
 * @var string                                         $provider               [Global] The tickets provider slug.
 * @var string                                         $cart_url               [Global] Link to Cart (could be empty).
 * @var int                                            $post_id                The event/post ID.
 */

// Bail if there are no tickets.
if ( empty( $tickets ) ) {
	return;
}

$storage = new Tribe__Tickets_Plus__Meta__Storage();
?>
<?php foreach ( $tickets as $ticket ) : ?>
	<?php
	// Sometimes we get an array - let's handle that.
	if ( is_array( $ticket ) ) {
		$ticket = $provider->get_ticket( $post_id, $ticket['id'] );
	}

	// Only include tickets with meta.
	if ( ! $ticket->has_meta_enabled() ) {
		continue;
	}
	?>
	<script
		type="text/html"
		class="registration-js-attendees-content"
		id="tmpl-tribe-registration--<?php echo esc_attr( $ticket->ID ); ?>"
	>
		<?php
		$ticket_qty = 1;
		$post       = get_post( $ticket->ID );
		$fields     = $meta->get_meta_fields_by_ticket( $post->ID );
		$saved_meta = $storage->get_meta_data_for( $post->ID );
		?>
		<?php // go through each attendee. ?>
		<?php while ( 0 < $ticket_qty ) : ?>
			<?php
			$args = [
				'post_id'    => $post_id,
				'ticket'     => $post,
				'fields'     => $fields,
				'saved_meta' => $saved_meta,
			];

			$this->template( 'v2/attendee-registration/content/attendees/fields', $args );
			$ticket_qty--;
			?>
		<?php endwhile; ?>
	</script>
	<?php
endforeach;
