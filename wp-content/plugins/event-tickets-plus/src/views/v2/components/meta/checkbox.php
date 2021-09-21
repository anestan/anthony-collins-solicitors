<?php
/**
 * This template renders the Checkbox field.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/components/meta/checkbox.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 5.0.0
 * @since 5.1.0 Added support for div HTML attributes.
 * @since 5.2.0 Fixed handling of showing selected checkbox options.
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
 * @var Tribe__Tickets_Plus__Meta__Field__Checkbox $field.
 * @var string $value The current field value.
 * @var string $description A user-defined description for meta field.
 *
 * @see Tribe__Tickets_Plus__Meta__Field__Checkbox
 */

$options = $field->get_hashed_options_map();

if ( ! $options ) {
	return;
}

$formatted_value = $field->get_formatted_value( $value );

$field_slug = $field->slug;
?>
<div
	<?php tribe_classes( $classes ); ?>
	<?php tribe_attributes( $attributes ); ?>
>
	<header class="tribe-tickets__form-field-label">
		<span>
			<?php echo wp_kses_post( $field->label ); ?><?php tribe_required_label( $required ); ?>
		</span>
	</header>

	<div class="tribe-common-form-control-checkbox-radio-group">
		<?php
		foreach ( $options as $option ) :
			$option_slug = md5( sanitize_title( $option ) );
			$option_id   = tribe_tickets_plus_meta_field_id( $ticket->ID, $field_slug, $option_slug, $attendee_id );
			$slug        = $field_slug . '_' . $option_slug;
			$field_name  = tribe_tickets_plus_meta_field_name( $ticket->ID, $slug, $attendee_id );
			?>

		<div class="tribe-common-form-control-checkbox">
			<label
				class="tribe-common-form-control-checkbox__label"
				for="<?php echo esc_attr( $option_id ); ?>"
			>
				<input
					class="tribe-common-form-control-checkbox__input tribe-tickets__form-field-input tribe-tickets__form-field-input--checkbox"
					id="<?php echo esc_attr( $option_id ); ?>"
					name="<?php echo esc_attr( $field_name ); ?>"
					type="checkbox"
					value="<?php echo esc_attr( $option ); ?>"
					<?php checked( in_array( $option, $formatted_value, true ) ); ?>
					<?php disabled( $field->is_restricted( $attendee_id ) ); ?>
					<?php tribe_required( $required ); ?>
				/>
				<?php echo wp_kses_post( $option ); ?>
			</label>
		</div>
		<?php endforeach; ?>
		<?php if ( ! empty( $description ) ) : ?>
			<div class="tribe-common-b3 tribe-tickets__form-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</div>
	<input
		type="hidden"
		name="<?php echo esc_attr( tribe_tickets_plus_meta_field_name( $ticket->ID, null, $attendee_id ) . '[0]' ); ?>"
		<?php disabled( $field->is_restricted( $attendee_id ) ); ?>
		value=""
		autocomplete="off"
	>
</div>
