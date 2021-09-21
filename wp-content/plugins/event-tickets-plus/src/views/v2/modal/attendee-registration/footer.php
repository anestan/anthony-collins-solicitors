<?php
/**
 * Modal: Attendee Registration > Footer
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/attendee-registration/footer.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var bool $has_tpp True if it is tribe commerce.
 */
?>

<div class="tribe-tickets__attendee-tickets-footer">
	<?php if ( ! $has_tpp ) : ?>
		<button
			type="submit"
			class="tribe-common-c-btn-link tribe-common-c-btn--small tribe-tickets__attendee-tickets-submit tribe-tickets__attendee-tickets-footer-cart-button tribe-validation-submit"
			name="cart-button"
		>
			<?php esc_html_e( 'Save and View Cart', 'event-tickets-plus' ); ?>
		</button>
		<span class="tribe-tickets__attendee-tickets-footer-divider"><?php esc_html_e( 'or', 'event-tickets-plus' ); ?></span>
	<?php endif; ?>

	<button
		type="submit"
		class="tribe-common-c-btn tribe-common-c-btn--small tribe-tickets__attendee-tickets-submit tribe-tickets__attendee-tickets-footer-checkout-button tribe-validation-submit"
		name="checkout-button"
	>
		<?php esc_html_e( 'Checkout Now', 'event-tickets-plus' ); ?>
	</button>
</div>
