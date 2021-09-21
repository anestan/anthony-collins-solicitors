<?php
/**
 * Attendee registration
 * Content > Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/attendee-registration/content/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1amp
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
 * @var Tribe__Tickets__Ticket_Object[]                $tickets                List of tickets for the particular event.
 */

$providers      = wp_list_pluck( $tickets, 'provider' );
$providers_arr  = array_unique( wp_list_pluck( $providers, 'attendee_object' ) );
$provider_class = $this->get_form_class( $provider );

if (
	empty( $provider_class )
	&& ! empty( $providers_arr[ $post_id ] )
) :
	$provider_class = 'tribe-tickets__attendee-tickets-form--' . $providers_arr[ $post_id ];
endif;

$has_tpp = Tribe__Tickets__Commerce__PayPal__Main::ATTENDEE_OBJECT === $provider || in_array( Tribe__Tickets__Commerce__PayPal__Main::ATTENDEE_OBJECT, $providers_arr, true );

$classes = [
	'tribe-tickets__attendee-tickets-form',
	'tribe-validation',
	$provider_class,
]

?>
<div
	class="tribe-tickets__registration-event"
	data-event-id="<?php echo esc_attr( $post_id ); ?>"
	data-is-meta-up-to-date="<?php echo absint( $is_meta_up_to_date ); ?>"
>
	<?php $this->template( 'v2/attendee-registration/content/event/summary', [ 'post_id' => $post_id, 'tickets' => $tickets ] ); ?>

	<div class="tribe-tickets__attendee-tickets">

		<?php $this->template( 'v2/attendee-registration/content/attendees/error', [ 'post_id' => $post_id, 'tickets' => $tickets ] ); ?>

		<div
			<?php tribe_classes( $classes ); ?>
			name="event<?php echo esc_attr( $post_id ); ?>"
		>
			<?php
			foreach ( $tickets as $ticket ) :
				$all_tickets[] = $ticket;

				if ( ! $meta->ticket_has_meta( $ticket['id'] ) ) {
					continue;
				}
				?>
				<div
					class="tribe-tickets__attendee-tickets-container"
					data-ticket-id="<?php echo esc_attr( $ticket['id'] ); ?>"
				>
					<h3 class="tribe-common-h5 tribe-common-h5--min-medium tribe-common-h--alt tribe-ticket__tickets-heading">
						<?php echo esc_html( get_the_title( $ticket['id'] ) ); ?>
					</h3>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php $this->template( 'v2/attendee-registration/content/attendees/content', [ 'post_id' => $post_id, 'tickets' => $tickets, 'provider' => $providers[0] ] ); ?>
