<?php
/**
 * Renders field
 *
 * Override this template in your own theme by creating a file at:
 *
 * [your-theme]/tribe/tickets-plus/meta/number.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   4.12.1
 * @since   5.1.0 Support the min/max/step extra arguments to manually set HTML attributes.
 *
 * @version 5.1.0
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Number $this
 */

$option_id = "tribe-tickets-meta_{$this->slug}" . ( $attendee_id ? '_' . $attendee_id : '' );

$classes = [
	'tribe-tickets-meta'          => true,
	'tribe-tickets-meta-number'   => true,
	'tribe-tickets-meta-required' => $required,
];

$min  = isset( $field['extra']['min'] ) && '' !== $field['extra']['min'] ? $field['extra']['min'] : '0';
$max  = isset( $field['extra']['max'] ) && '' !== $field['extra']['max'] ? $field['extra']['max'] : 'off';
$step = isset( $field['extra']['step'] ) && '' !== $field['extra']['step'] ? $field['extra']['step'] : '0.01';
?>
<div <?php tribe_classes( $classes ); ?>>
	<label for="<?php echo esc_attr( $option_id ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
	<input
		type="number"
		<?php if ( null !== $min && 'off' !== $min ) : ?>
			min="<?php echo esc_attr( $min ); ?>"
		<?php endif; ?>
		<?php if ( null !== $max && 'off' !== $max ) : ?>
			max="<?php echo esc_attr( $max ); ?>"
		<?php endif; ?>
		<?php if ( null !== $step && 'off' !== $step ) : ?>
			step="<?php echo esc_attr( $step ); ?>"
		<?php endif; ?>
		id="<?php echo esc_attr( $option_id ); ?>"
		class="ticket-meta ticket-meta-number-field"
		name="tribe-tickets-meta[<?php echo esc_attr( $attendee_id ); ?>][<?php echo esc_attr( $this->slug ); ?>]"
		value="<?php echo esc_attr( $value ); ?>"
		<?php echo $required ? 'required' : ''; ?>
		<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
	>
</div>
