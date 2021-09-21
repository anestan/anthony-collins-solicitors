<?php
/**
 * Modal: Cart
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/cart.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1amp
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var Tribe__Tickets_Plus__Template      $this                Template object.
 * @var Tribe__Tickets__Ticket_Object[]    $tickets             List of tickets.
 * @var Tribe__Tickets__Ticket_Object[]    $tickets_on_sale     List of tickets on sale.
 * @var bool                               $has_tickets_on_sale Whether there are tickets on sale.
 * @var Tribe__Tickets__Commerce__Currency $currency            The commerce currency instance.
 * @var bool                               $is_mini             True if it's in mini cart context.
 * @var bool                               $is_modal            True if it's in modal context.
 * @var Tribe__Tickets__Tickets            $provider            The tickets provider class.
 * @var string                             $provider_id         The tickets provider class name.
 * @var string                             $cart_url            The cart URL.
 */

// We don't display anything if there is no provider or tickets.
if ( ! $provider || empty( $tickets ) ) {
	return;
}

$cart_classes = [
	'tribe-modal-cart',
	'tribe-modal__cart',
	'tribe-common',
	'event-tickets',
];

/** @var Tribe__Tickets__Editor__Template $et_template */
$et_template = tribe( 'tickets.editor.template' );

$etp_context = $this->get_values();

$etp_context_footer = array_merge( $etp_context, [ 'is_modal' => true ] );
?>
<div
	id="tribe-modal__cart"
	action="<?php echo esc_url( $cart_url ); ?>"
	<?php tribe_classes( $cart_classes ); ?>
	method="post"
	enctype='multipart/form-data'
	data-provider="<?php echo esc_attr( $provider->class_name ); ?>"
	autocomplete="off"
	novalidate
>
	<?php
	// Load ETP's Fields, based on Provider.
	$this->template(
		'v2/tickets/commerce/fields',
		[
			'provider'    => $provider,
			'provider_id' => $provider_id,
		]
	);

	if ( $has_tickets_on_sale ) :
		foreach ( $tickets_on_sale as $key => $ticket ) :
			$currency_symbol = $currency->get_currency_symbol( $ticket->ID, true );

			$available_count = $ticket->available();

			/**
			 * Allows hiding of "unlimited" to be toggled on/off conditionally.
			 *
			 * @since 5.1.0
			 *
			 * @var bool                          $show_unlimited  Whether to show the "unlimited" text.
			 * @var int                           $available_count The quantity of Available tickets based on the Attendees number.
			 * @var Tribe__Tickets__Ticket_Object $ticket          The ticket object.
			 */
			$show_unlimited = apply_filters( 'tribe_tickets_plus_show_unlimited_availability', true, $available_count, $ticket );

			// Load from ET.
			$et_template->template(
				'v2/tickets/item',
				array_merge(
					$etp_context,
					[
						'ticket'          => $ticket,
						'key'             => $key,
						'is_modal'        => true,
						'currency_symbol' => $currency_symbol,
						'show_unlimited'  => $show_unlimited,
						'available_count' => $available_count,
					]
				)
			);
		endforeach;
	endif;

	// Load from ET.
	$et_template->template( 'v2/components/loader/loader', $etp_context );

	// Load from ET.
	$et_template->template( 'v2/tickets/footer', $etp_context_footer );
	?>
</div>
