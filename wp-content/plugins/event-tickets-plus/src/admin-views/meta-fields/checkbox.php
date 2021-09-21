<?php

/**
 * @see Tribe__Tickets_Plus__Meta__Field__Checkbox
 */

$options = '';

if ( $extra && ! empty( $extra['options'] ) ) {
	$options = implode( "\n", $extra['options'] );
}

?>
<div class="tribe-tickets-input tribe-tickets-input-textarea">
	<div class="tribe-tickets-input-row">
		<div class="tribe-tickets-input-col1">
			<label for="tickets_attendee_info_field"><?php echo esc_html_x( 'Options:', 'Attendee information fields', 'event-tickets-plus' ); ?></label>
		</div>
		<div class="tribe-tickets-input-col2">
			<textarea name="tribe-tickets-input[<?php echo $field_id; ?>][extra][options]" class="ticket_field" value="" rows="5"><?php echo esc_textarea( $options ); ?></textarea>
			<p>
				<?php echo esc_html_x( 'Add one option per line separated by a return.', 'event-tickets-plus' ); ?>
			</p>
		</div>
	</div>
</div>

