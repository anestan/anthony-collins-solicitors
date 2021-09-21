<?php
/**
 * Renders text field
 *
 * Override this template in your own theme by creating a file at:
 *
 * [your-theme]/tribe/tickets-plus/meta/text.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 4.3.5
 * @since 4.10.7 Added var documentation for `$this`.
 * @since 4.12.1    Updated PHP comment.
 * @since 5.1.0 Updated template link.
 * @since 5.2.5 Added placeholder attribute support.
 *
 * @version 5.2.5
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Text $this
 */
$multiline = isset( $field['extra'] ) && isset( $field['extra']['multiline'] ) ? $field['extra']['multiline'] : '';
$option_id = "tribe-tickets-meta_{$this->slug}" . ( $attendee_id ? '_' . $attendee_id : '' );
?>
<div class="tribe-tickets-meta tribe-tickets-meta-text <?php echo $required ? 'tribe-tickets-meta-required' : ''; ?>">
	<label for="<?php echo esc_attr( $option_id ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
	<?php
	if ( $multiline ) {
		?>
		<textarea
			id="<?php echo esc_attr( $option_id ); ?>"
			class="ticket-meta"
			placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
			name="tribe-tickets-meta[<?php echo esc_attr( $attendee_id ); ?>][<?php echo esc_attr( $this->slug ); ?>]"
			<?php echo $required ? 'required' : ''; ?>
			<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
		><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	} else {
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $option_id ); ?>"
			class="ticket-meta"
			placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
			name="tribe-tickets-meta[<?php echo esc_attr( $attendee_id ); ?>][<?php echo esc_attr( $this->slug ); ?>]"
			value="<?php echo esc_attr( $value ); ?>"
			<?php echo $required ? 'required' : ''; ?>
			<?php disabled( $this->is_restricted( $attendee_id ) ); ?>
		>
		<?php
	}
	?>
</div>
