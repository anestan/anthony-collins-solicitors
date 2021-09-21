<?php
/**
 * Block: Tickets
 * Form Opt-Out
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/item/opt-out.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since 5.1.0
 * @since 5.2.9 Updated HTML to display the opt in AR button.
 *
 * @version 5.2.9
 *
 * @var null|bool                          $is_modal                    [Global] Whether the modal is enabled.
 * @var Tribe__Tickets__Ticket_Object      $ticket                      The ticket object.
 * @var Tribe__Tickets__Privacy            $privacy                     [Global] Tribe Tickets Privacy object.
 * @var int                                $post_id                     [Global] The current Post ID to which tickets are attached.
 */

// Bail if we are not in the "modal" context.
if ( empty( $is_modal ) ){
	return;
}

/**
 * Use this filter to hide the Attendees List Opt-out.
 *
 * @since 4.9
 *
 * @param bool $hide_attendee_list_optout Whether to hide attendees list opt-out.
 * @param int  $post_id                   The post ID this ticket belongs to.
 */
$hide_attendee_list_optout = apply_filters( 'tribe_tickets_plus_hide_attendees_list_optout', false, $post_id );

if ( $hide_attendee_list_optout ) {
	// Force opt-out.
	?>
	<input
		name="attendee[optout]"
		value="1"
		type="hidden"
	/>
	<?php
	return;
}
$modal_field_id = 'tribe-tickets-attendees-list-optout-' . $ticket->ID . '--modal';

?>
<div class="tribe-common-form-control-checkbox tribe-tickets-attendees-list-optout--wrapper">
	<label
		class="tribe-common-form-control-checkbox__label"
		for="<?php echo esc_attr( $modal_field_id ); ?>"
	>
		<input
			class="tribe-common-form-control-checkbox__input tribe-tickets__tickets-item-optout"
			id="<?php echo esc_attr( $modal_field_id ); ?>"
			name="attendee[optout]"
			type="checkbox"
			<?php checked( true ); ?>
		/>
		<?php echo wp_kses_post( $privacy->get_opt_out_text() ); ?>
	</label>
</div>
