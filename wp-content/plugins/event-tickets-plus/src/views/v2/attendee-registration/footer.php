<?php
/**
 * Attendee registration
 * Footer
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/attendee-registration/footer.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1amp
 *
 * @since   5.1.0
 * @since   5.2.5 Added plural/singular support for the notice string content.
 *
 * @version 5.2.5
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
 */

/** @var Tribe__Tickets__Editor__Template $et_template */
$et_template = tribe( 'tickets.editor.template' );

?>
<div class="tribe-tickets__registration-footer">
	<?php
	$notice_classes = [
		'tribe-tickets__notice--non-ar',
		'tribe-common-a11y-hidden', // Set as hidden. JavaScript will show it if needed.
	];

	$et_template->template(
		'components/notice',
		[
			'notice_classes' => $notice_classes,
			'content'        => sprintf(
				esc_html(
					// Translators: %s HTML wrapped number of tickets.
					_nx(
						'There is %s other ticket in your cart that does not require attendee information.',
						'There are %s other tickets in your cart that do not require attendee information.',
						absint( $non_meta_count ),
						'Note that there are more tickets in the cart, %s is the html-wrapped number.',
						'event-tickets-plus'
					)
				),
				'<span id="tribe-tickets__non-ar-count">' . absint( $non_meta_count ) . '</span>'
			),
		]
	);

	$this->template( 'v2/attendee-registration/button/submit' );
	?>
</div>
