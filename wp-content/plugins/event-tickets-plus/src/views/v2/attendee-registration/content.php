<?php
/**
 * This template renders the registration/purchase attendee fields
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/attendee-registration/content.php
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
 */

$provider_class = $this->get_form_class( $provider );
$all_tickets    = [];
$classes        = [
	'tribe-common',
	'event-tickets',
	'tribe-tickets__registration',
];

/** @var Tribe__Tickets__Editor__Template $et_template */
$et_template = tribe( 'tickets.editor.template' );
?>
<div
	<?php tribe_classes( $classes ); ?>
	data-provider="<?php echo esc_attr( $provider ); ?>"
>
	<?php
	/**
	 * Before the output, whether $events is empty.
	 *
	 * @since 4.11.0
	 *
	 * @param string $provider       The 'provider' $_REQUEST var.
	 * @param string $provider_class The class string or empty string if ticket provider is not found.
	 * @param array  $events         The array of events, which might be empty.
	 */
	do_action( 'tribe_tickets_registration_content_before_all_events', $provider, $provider_class, $events );
	?>

	<div class="tribe-common-h8 tribe-common-h--alt tribe-tickets__registration-actions">
		<?php $this->template( 'v2/attendee-registration/button/back-to-cart' ); ?>
	</div>

	<?php $this->template( 'v2/attendee-registration/content/title' ); ?>
	<form
		method="post"
		id="tribe-tickets__registration-form"
		action="<?php echo esc_url( $checkout_url ); ?>"
		data-provider="<?php echo esc_attr( $provider ); ?>"
		novalidate
	>
		<div class="tribe-tickets__registration-grid">

			<?php
			$this->template( 'v2/attendee-registration/content/notice' );

			$context = [
				'provider_id' => $this->get( 'provider_id' ),
			];

			$this->template( 'v2/attendee-registration/mini-cart', $context );
			?>
			<div class="tribe-tickets__registration-content">
				<input type="hidden" name="tribe_tickets_saving_attendees" value="1" />
				<input type="hidden" name="tribe_tickets_ar" value="1" />
				<input type="hidden" name="tribe_tickets_ar_page" value="1" />
				<input type="hidden" name="tribe_tickets_ar_data" value="" id="tribe_tickets_ar_data" />
				<input type="hidden" name="tribe_tickets_provider" value="<?php echo esc_attr( $provider ); ?>" />

				<?php foreach ( $events as $post_id => $tickets ) : ?>

					<?php
					$this->template(
						'v2/attendee-registration/content/event',
						[
							'post_id'  => $post_id,
							'tickets'  => $tickets,
							'provider' => $provider,
						]
					);
					?>

				<?php endforeach; ?>
			</div>
		</div>

		<?php $this->template( 'v2/attendee-registration/footer' ); ?>

	</form>
	<?php $et_template->template( 'v2/components/loader/loader' ); ?>
</div>
