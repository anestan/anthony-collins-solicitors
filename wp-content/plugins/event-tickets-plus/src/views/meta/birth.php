<?php
/**
 * This template renders the Birth Date field for Attendee Information.
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe/tickets-plus/meta/birth.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @deprecated 4.12.3 Replaced by event-tickets/src/views/registration-js/attendees/fields/birth.php.
 *
 * @since 4.12.1 Introduced template.
 * @since 4.12.3 Add a separate label per input for screen readers.
 * @since 5.0.0 Set proper text domain.
 * @since 5.1.0 Updated template link.
 *
 * @version 5.1.0
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Birth $this
 */

$option_id = "tribe-tickets-meta_{$this->slug}" . ( $attendee_id ? '_' . $attendee_id : '' );

$classes = [
	'tribe-tickets-meta'          => true,
	'tribe-tickets-meta-birth'    => true,
	'tribe-tickets-meta-required' => $required,
];

?>
<div class="tribe_horizontal_datepicker__container">
	<div <?php tribe_classes( $classes ) ?>>
		<label>
			<?php echo wp_kses_post( $field['label'] ); ?>
		</label>

		<!-- Group of Month, Day, Year fields -->
		<div class="tribe_horizontal_datepicker__field_group tribe-common">
			<!-- Month -->
			<div class="tribe_horizontal_datepicker">
				<label
					for="<?php echo esc_attr( $option_id . '-month' ); ?>"
					class="tribe-common-a11y-hidden"
				>
					<?php echo wp_kses_post( $field['label'] ) . esc_html_x( ' Month', 'birthdate field', 'event-tickets-plus' ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $option_id . '-month' ); ?>"
					<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
					<?php tribe_required( $required ); ?>
					class="tribe_horizontal_datepicker__month"
				>
					<option value="" disabled selected><?php esc_html_e( 'Month', 'event-tickets-plus' ); ?></option>
					<?php foreach ( $this->get_months() as $month_number => $month_name ) : ?>
						<option value="<?php echo esc_attr( $month_number ); ?>"><?php echo esc_html( $month_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<!-- Day -->
			<div class="tribe_horizontal_datepicker">
				<label
					for="<?php echo esc_attr( $option_id . '-day' ); ?>"
					class="tribe-common-a11y-hidden"
				>
					<?php echo wp_kses_post( $field['label'] ) . esc_html_x( ' Day', 'birthdate field', 'event-tickets-plus' ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $option_id . '-day' ); ?>"
					<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
					<?php tribe_required( $required ); ?>
					class="tribe_horizontal_datepicker__day"
				>
					<option value="" disabled selected><?php esc_html_e( 'Day', 'event-tickets-plus' ); ?></option>
					<?php foreach ( $this->get_days() as $birth_day ) : ?>
						<option value="<?php echo esc_attr( $birth_day ); ?>"><?php echo esc_html( $birth_day ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<!-- Year -->
			<div class="tribe_horizontal_datepicker">
				<label
					for="<?php echo esc_attr( $option_id . '-year' ); ?>"
					class="tribe-common-a11y-hidden"
				>
					<?php echo wp_kses_post( $field['label'] ) . esc_html_x( ' Year', 'birthdate field', 'event-tickets-plus' ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $option_id . '-year' ); ?>"
					<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
					<?php tribe_required( $required ); ?>
					class="tribe_horizontal_datepicker__year"
				>
					<option value="" disabled selected><?php esc_html_e( 'Year', 'event-tickets-plus' ); ?></option>
					<?php foreach ( $this->get_years() as $birth_year ) : ?>
						<option value="<?php echo esc_attr( $birth_year ); ?>"><?php echo esc_html( $birth_year ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div>
			<input
				type="hidden"
				class="ticket-meta tribe_horizontal_datepicker__value"
				name="tribe-tickets-meta[<?php echo esc_attr( $attendee_id ); ?>][<?php echo esc_attr( $this->slug ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				<?php echo $required ? 'required' : ''; ?>
				<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
			/>
		</div>
	</div>
</div>
