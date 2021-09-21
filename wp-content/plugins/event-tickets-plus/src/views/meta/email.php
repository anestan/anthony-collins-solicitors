<?php
/**
 * Renders field
 *
 * Override this template in your own theme by creating a file at:
 *
 * [your-theme]/tribe/tickets-plus/meta/email.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   4.12.1
 * @since   5.1.0 Updated template link.
 * @since   5.2.5 Added placeholder attribute support.
 *
 * @version 5.2.5
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Email $this
 */

$option_id = "tribe-tickets-meta_{$this->slug}" . ( $attendee_id ? '_' . $attendee_id : '' );

$classes = [
	'tribe-tickets-meta'          => true,
	'tribe-tickets-meta-email'    => true,
	'tribe-tickets-meta-required' => $required,
];
?>
<div <?php tribe_classes( $classes ); ?>>
	<label for="<?php echo esc_attr( $option_id ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
	<input
			type="email"
			id="<?php echo esc_attr( $option_id ); ?>"
			class="ticket-meta"
			placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
			name="tribe-tickets-meta[<?php echo esc_attr( $attendee_id ); ?>][<?php echo esc_attr( $this->slug ); ?>]"
			value="<?php echo esc_attr( $value ); ?>"
			<?php echo $required ? 'required' : ''; ?>
			<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
	>
</div>
