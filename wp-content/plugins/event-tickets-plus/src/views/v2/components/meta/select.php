<?php
/**
 * The template for the select input.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/components/meta/select.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 5.0.0
 * @since 5.1.0 Added support for div HTML attributes.
 * @since 5.2.9 Added support for description.
 *
 * @version 5.2.9
 *
 * @var string $field_name The meta field name.
 * @var string $field_id The meta field id.
 * @var bool   $required A bool indicating if the meta field is required or not.
 * @var bool $disabled A bool indicating if the meta field is disabled or not.
 * @var string|int $attendee_id The attendee ID, to build the ID/name.
 * @var array $classes Array containing the CSS classes for the field.
 * @var array $attributes Array containing the HTML attributes for the field.
 * @var Tribe__Tickets__Ticket_Object $ticket The ticket object.
 * @var Tribe__Tickets_Plus__Meta__Field__Select $field
 * @var string $value The current field value.
 * @var string $description A user-defined description for meta field.
 *
 * @see Tribe__Tickets_Plus__Meta__Field__Select
 */

$slug    = $field->slug;
$options = $field->get_hashed_options_map();

// Bail if there are no options.
if ( ! $options ) {
	return;
}

?>
<div
	<?php tribe_classes( $classes ); ?>
	<?php tribe_attributes( $attributes ); ?>
>
	<label
		class="tribe-tickets__form-field-label"
		for="<?php echo esc_attr( $field_id ); ?>"
		><?php echo wp_kses_post( $field->label ); ?><?php tribe_required_label( $required ); ?>
	</label>
	<div class="tribe-tickets__form-field-input-wrapper">
		<select
			<?php tribe_disabled( $disabled ); ?>
			id="<?php echo esc_attr( $field_id ); ?>"
			class="tribe-common-form-control-text__input tribe-tickets__form-field-input"
			name="<?php echo esc_attr( $field_name ); ?>"
			<?php tribe_required( $required ); ?>
		>
			<option value=""><?php esc_html_e( 'Select an option', 'event-tickets-plus' ); ?></option>
			<?php foreach ( $options as $option => $label ) : ?>
				<option
					<?php selected( $label, $value ); ?> value="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php if ( ! empty( $description ) ) : ?>
			<div class="tribe-common-b3 tribe-tickets__form-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
