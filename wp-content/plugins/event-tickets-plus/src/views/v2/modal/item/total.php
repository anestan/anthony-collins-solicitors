<?php
/**
 * Modal: Item Total
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/item/total.php
 *
 * @link https://evnt.is/1amp
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var bool                               $is_mini  True if it's in mini cart context.
 * @var bool                               $is_modal True if it's in modal context.
 * @var int                                $post_id  The post/event ID.
 * @var Tribe__Tickets__Commerce__Currency $currency The currency class.
 * @var Tribe__Tickets__Tickets            $provider The tickets provider class.
 */

// Bail if it's NOT in modal and mini cart context.
if (
	empty( $is_modal )
	&& empty( $is_mini )
) {
	return;
}

?>
<div class="tribe-common-b2 tribe-tickets__tickets-item-total-wrap">
	<span class="tribe-tickets__tickets-item-total">
		<?php echo $currency->get_formatted_currency_with_symbol( 0, $post_id, $provider->class_name ); ?>
	</span>
</div>
