<?php
/**
 * AR: Mini-Cart
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/attendee-registration/mini-cart.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1amp
 *
 * @since   5.1.0
 * @since   5.2.0 Added $currency to the passed context for the tickets footer template.
 *
 * @version 5.1.1
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
 * @var Tribe__Tickets__Tickets|string                 $provider               [Global] The tickets provider class instance or slug string.
 * @var string                                         $cart_url               [Global] Link to Cart (could be empty).
 * @var Tribe__Tickets__Tickets_Handler                $handler                [Global] Tribe Tickets Handler object.
 */

$cart_provider  = $this->get_cart_provider( $provider );
$provider_class = ! empty( $cart_provider ) ? $cart_provider->class_name : '';

$cart_classes = [
	'tribe-common',
	'event-tickets',
	'tribe-tickets__mini-cart',
];

/** @var Tribe__Tickets__Editor__Template $et_template */
$et_template = tribe( 'tickets.editor.template' );

?>
<aside
	<?php tribe_classes( $cart_classes ); ?>
	id="tribe-tickets__mini-cart"
	data-provider="<?php echo esc_attr( $provider_class ); ?>"
>
	<?php

	$this->template( 'v2/attendee-registration/mini-cart/title' );

	foreach ( $events as $post_id => $tickets ) :

		foreach ( $tickets as $key => $ticket ) :
			if ( $provider_class !== $ticket['provider']->class_name ) {
				continue;
			}

			$currency_symbol = $currency->get_currency_symbol( $ticket['id'], true );
			$has_shared_cap  = $handler->has_shared_capacity( $ticket );

			$et_template->template(
				'v2/tickets/item',
				[
					'ticket'              => $cart_provider->get_ticket( $post_id, $ticket['id'] ),
					'data_available'      => 0 === $handler->get_ticket_max_purchase( $ticket['id'] ) ? 'false' : 'true',
					'has_shared_cap'      => $has_shared_cap,
					'data_has_shared_cap' => $has_shared_cap ? 'true' : 'false',
					'key'                 => $key,
					'is_mini'             => true,
					'currency'            => $currency,
					'currency_symbol'     => $currency_symbol,
					'provider'            => $cart_provider,
					'post_id'             => $post_id,
					'must_login'          => false,
				]
			);
		endforeach;
	endforeach;

	$et_template->template(
		'v2/tickets/footer',
		[
			'is_mini'  => true,
			'post_id'  => 0,
			'provider' => $cart_provider,
			'currency' => $currency,
		]
	);
	?>
</aside>

<?php foreach ( $events as $post_id => $tickets ) : ?>
	<?php
	$event_provider = Tribe__Tickets__Tickets::get_event_ticket_provider_object( $post_id );

	if (
		empty( $event_provider )
		|| $provider_class !== $event_provider->class_name
	) {
		continue;
	}

	$this->template(
		'v2/attendee-registration/content/attendees/content',
		[
			'post_id'  => $post_id,
			'tickets'  => $tickets,
			'provider' => $cart_provider,
			'currency' => $currency,
		]
	);
	?>
<?php endforeach; ?>
