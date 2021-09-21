<?php
/**
 * Description input for Admin view for fields with Description support.
 *
 * @since 5.2.9
 *
 * @version 5.2.9
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field [Global] The field object.
 * @var int    $field_id  [Global] The ticket to add/edit.
 * @var string $description [Global] The field Description.
 */

?>
<div class="tribe-tickets-input tribe-tickets-input-textarea">
	<div class="tribe-tickets-input-row">
		<div class="tribe-tickets-input-col1">
			<label for="tickets_attendee_info_field">
				<?php echo esc_html_x( 'Description:', 'Attendee information fields', 'event-tickets-plus' ); ?>
			</label>
		</div>
		<div class="tribe-tickets-input-col2">
			<textarea
				class="ticket_field"
				name="tribe-tickets-input[<?php echo esc_attr( $field_id ); ?>][description]"
			><?php echo esc_textarea( $description ); ?></textarea>
			<p>
				<?php echo esc_html__( 'The description appears below the field and provides additional context.', 'event-tickets-plus' ); ?>
			</p>
		</div>
	</div>
</div>