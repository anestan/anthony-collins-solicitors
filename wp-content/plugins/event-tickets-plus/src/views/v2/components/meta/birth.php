<?php
/**
 * This template renders the Birth field.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/components/meta/birth.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 5.0.0
 * @since 5.1.0 Added support for div HTML attributes.
 * @since 5.2.0 Fixed handling of showing current value of field.
 * @since 5.2.9 Added support for description.
 *
 * @version 5.2.9
 *
 * @var string $field_name The meta field name.
 * @var string $field_d The meta field id.
 * @var bool   $required A bool indicating if the meta field is required or not.
 * @var bool $disabled A bool indicating if the meta field is disabled or not.
 * @var string|int $attendee_id The attendee ID, to build the ID/name.
 * @var array $classes Array containing the CSS classes for the field.
 * @var array $attributes Array containing the HTML attributes for the field.
 * @var Tribe__Tickets__Ticket_Object $ticket The ticket object.
 * @var Tribe__Tickets_Plus__Meta__Field__Birth $field.
 * @var string $value The current field value.
 * @var string $description A user-defined description for meta field.
 *
 * @see     Tribe__Tickets_Plus__Meta__Field__Birth
 */

$formatted_value = $field->get_formatted_value( $value );

$selected_month = $formatted_value['month'];
$selected_day   = $formatted_value['day'];
$selected_year  = $formatted_value['year'];
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
		<label
			for="<?php echo esc_attr( $field_id . '-month' ); ?>"
			class="tribe-common-a11y-visual-hide"
		>
			<?php echo wp_kses_post( $field->label ) . ' ' . esc_html_x( 'Month', 'birthdate field', 'event-tickets-plus' ); ?>
		</label>
		<select
			id="<?php echo esc_attr( $field_id . '-month' ); ?>"
			<?php tribe_disabled( $disabled ); ?>
			<?php tribe_required( $required ); ?>
			class="tribe-common-form-control-text__input tribe-tickets__form-field--birth-month"
		>
			<option
				value=""
				disabled
				<?php selected( $selected_month, '' ); ?>
			><?php esc_html_e( 'Month', 'event-tickets-plus' ); ?></option>
			<?php foreach ( $field->get_months() as $month_number => $month_name ) : ?>
				<option
					value="<?php echo esc_attr( $month_number ); ?>"
					<?php selected( $selected_month, $month_number ); ?>
				><?php echo esc_html( $month_name ); ?></option>
			<?php endforeach; ?>
		</select>

		<label
			for="<?php echo esc_attr( $field_id . '-day' ); ?>"
			class="tribe-common-a11y-visual-hide"
		>
			<?php echo wp_kses_post( $field->label ) . ' ' . esc_html_x( 'Day', 'birthdate field', 'event-tickets-plus' ); ?>
		</label>
		<select
			id="<?php echo esc_attr( $field_id . '-day' ); ?>"
			<?php tribe_disabled( $disabled ); ?>
			<?php tribe_required( $required ); ?>
			class="tribe-common-form-control-text__input tribe-tickets__form-field--birth-day"
		>
			<option
				value=""
				disabled
				<?php selected( $selected_day, '' ); ?>
			><?php esc_html_e( 'Day', 'event-tickets-plus' ); ?></option>
			<?php foreach ( $field->get_days() as $birth_day ) : ?>
				<option
					value="<?php echo esc_attr( $birth_day ); ?>"
					<?php selected( $selected_day, $birth_day ); ?>
				><?php echo esc_html( $birth_day ); ?></option>
			<?php endforeach; ?>
		</select>

		<label
			for="<?php echo esc_attr( $field_id . '-year' ); ?>"
			class="tribe-common-a11y-visual-hide"
		>
			<?php echo wp_kses_post( $field->label ) . ' ' . esc_html_x( 'Year', 'birthdate field', 'event-tickets-plus' ); ?>
		</label>
		<select
			id="<?php echo esc_attr( $field_id . '-year' ); ?>"
			<?php tribe_disabled( $disabled ); ?>
			<?php tribe_required( $required ); ?>
			class="tribe-common-form-control-text__input tribe-tickets__form-field--birth-year"
		>
			<option
				value=""
				disabled
				<?php selected( $selected_year, '' ); ?>
			><?php esc_html_e( 'Year', 'event-tickets-plus' ); ?></option>
			<?php foreach ( $field->get_years() as $birth_year ) : ?>
				<option
					value="<?php echo esc_attr( $birth_year ); ?>"
					<?php selected( $selected_year, $birth_year ); ?>
				><?php echo esc_html( $birth_year ); ?></option>
			<?php endforeach; ?>
		</select>
		<input
			type="hidden"
			class="tribe-tickets__form-field-input tribe-tickets__form-field--birth-value"
			name="<?php echo esc_attr( $field_name ); ?>"
			id="<?php echo esc_attr( $field_id ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php tribe_disabled( $disabled ); ?>
			<?php tribe_required( $required ); ?>
			autocomplete="off"
		/>
		<?php if ( ! empty( $description ) ) : ?>
			<div class="tribe-common-b3 tribe-tickets__form-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
