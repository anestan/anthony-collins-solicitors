<?php
/**
 * Attendee registration
 * Content > Event > Summary > Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/attendee-registration/content/event/summary/title.php
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
 */
?>
<div class="tribe-tickets__registration-title">
	<header>
		<h2 class="tribe-common-h4 tribe-common-h3--min-medium">
			<a href="<?php the_permalink( $post_id ); ?>">
				<?php echo get_the_title( $post_id ); ?>
			</a>
		</h2>
	</header>
</div>
