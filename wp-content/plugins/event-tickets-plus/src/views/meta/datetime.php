<?php
/**
 * Renders field
 *
 * Override this template in your own theme by creating a file at:
 *
 * [your-theme]/tribe/tickets-plus/meta/datetime.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   4.12.1
 * @since   5.1.0 Support the min/max extra arguments to manually set minimum/maximum date.
 *
 * @version 5.1.0
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Datetime $this
 */

$option_id = "tribe-tickets-meta_{$this->slug}" . ( $attendee_id ? '_' . $attendee_id : '' );

$classes = [
	'tribe-tickets-meta'          => true,
	'tribe-tickets-meta-datetime' => true,
	'tribe-tickets-meta-required' => $required,
];

$min = ! empty( $field['extra']['min'] ) ? $field['extra']['min'] : '1900-01-01';
$max = ! empty( $field['extra']['max'] ) ? $field['extra']['max'] : ( (int) date_i18n( 'Y' ) + 100 ) . '-12-31';
?>
<div <?php tribe_classes( $classes ) ?>>
	<label for="<?php echo esc_attr( $option_id ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
		<input
			type="date"
			id="<?php echo esc_attr( $option_id ); ?>"
			class="ticket-meta"
			name="tribe-tickets-meta[<?php echo esc_attr( $attendee_id ); ?>][<?php echo esc_attr( $this->slug ); ?>]"
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"
			<?php echo $required ? 'required' : ''; ?>
			<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
		>
</div>
