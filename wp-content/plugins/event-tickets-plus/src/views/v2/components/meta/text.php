<?php
/**
 * This template renders the Text or Textarea field.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/components/meta/text.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 5.0.0
 * @since 5.1.0 Added support for div HTML attributes.
 * @since 5.1.1 Added support for placeholders.
 * @since 5.2.9 Added support for description.
 *
 * @version 5.2.9
 *
 * @var string $field_name The meta field name.
 * @var string $field_id The meta field id.
 * @var bool   $required A bool indicating if the meta field is required or not.
 * @var string|int $attendee_id The attendee ID, to build the ID/name.
 * @var array $classes Array containing the CSS classes for the field.
 * @var array $attributes Array containing the HTML attributes for the field.
 * @var string $placeholder The field placeholder text.
 * @var bool $disabled Field is disabled or not.
 * @var string $value Value for field.
 * @var Tribe__Tickets__Ticket_Object $ticket The ticket object.
 * @var Tribe__Tickets_Plus__Meta__Field__Text $field.
 * @var string $description A user-defined description for meta field.
 *
 * @see Tribe__Tickets_Plus__Meta__Field__Text
 */

$multiline = Tribe__Utils__Array::get( $field, [ 'extra', 'multiline' ], null );
?>
<div
	<?php tribe_classes( $classes ); ?>
	<?php tribe_attributes( $attributes ); ?>
>
	<label
		class="tribe-tickets__form-field-label"
		for="<?php echo esc_attr( $field_id ); ?>"
	><?php echo wp_kses_post( $field->label ); ?><?php tribe_required_label( $required ); ?></label>
	<div class="tribe-tickets__form-field-input-wrapper">
		<?php if ( $multiline ) : ?>
			<textarea
				id="<?php echo esc_attr( $field_id ); ?>"
				class="tribe-common-form-control-text__input tribe-tickets__form-field-input"
				name="<?php echo esc_attr( $field_name ); ?>"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				<?php tribe_required( $required ); ?>
				<?php tribe_disabled( $disabled ); ?>
			><?php echo esc_textarea( $value ); ?></textarea>
		<?php else : ?>
			<input
				type="text"
				id="<?php echo esc_attr( $field_id ); ?>"
				class="tribe-common-form-control-text__input tribe-tickets__form-field-input"
				name="<?php echo esc_attr( $field_name ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				<?php tribe_required( $required ); ?>
				<?php tribe_disabled( $disabled ); ?>
			/>
		<?php endif; ?>
		<?php if ( ! empty( $description ) ) : ?>
			<div class="tribe-common-b3 tribe-tickets__form-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
