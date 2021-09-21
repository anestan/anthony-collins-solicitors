<?php
/**
 * Modal: Remove Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/item/remove.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since 5.1.0
 *
 * @version 5.1.0
 *
 * @var bool $is_modal True if it's in modal context.
 */

// Bail if it's not in modal context.
if ( empty( $is_modal ) ) {
	return;
}

?>
<div
	class="tribe-tickets__tickets-item-remove-wrap"
>
	<button
		type="button"
		class="tribe-tickets__tickets-item-remove"
	>
	</button>
</div>
