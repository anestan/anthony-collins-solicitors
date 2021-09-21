<?php
/**
 * Placeholder input for Admin view for fields with placeholder support.
 *
 * @since 5.2.5
 *
 * @version 5.2.5
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field [Global] The field object.
 * @var int    $field_id  [Global] The ticket to add/edit.
 * @var string $placeholder [Global] The field placeholder.
 */

if ( ! $field->has_placeholder() ) {
	return;
}

$url     = 'https://theeventscalendar.com/knowledgebase/k/collecting-attendee-information-for-tickets-and-rsvp/';
$kb_link = sprintf( __( '<a href="%s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'event-tickets-plus' ), $url );
?>
<div class="tribe-tickets-input tribe-tickets-input-text">
	<div class="tribe-tickets-input-row">
		<div class="tribe-tickets-input-col1">
			<label for="tickets_attendee_info_field">
				<?php echo esc_html_x( 'Placeholder:', 'Attendee information fields', 'event-tickets-plus' ); ?>
			</label>
		</div>
		<div class="tribe-tickets-input-col2">
			<input
				type="text"
				class="ticket_field"
				name="tribe-tickets-input[<?php echo esc_attr( $field_id ); ?>][placeholder]"
				value="<?php echo esc_attr( $placeholder ); ?>"
			>
			<p>
				<?php echo esc_html_x( 'Placeholder text appears inside an empty field and describes the expected value of the input.', 'event-tickets-plus' ); ?>
			</p>
		</div>
	</div>
</div>