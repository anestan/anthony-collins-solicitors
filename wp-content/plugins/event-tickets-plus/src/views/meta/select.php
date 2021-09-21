<?php
/**
 * Renders select field
 *
 * Override this template in your own theme by creating a file at:
 *
 * [your-theme]/tribe/tickets-plus/meta/select.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 4.3.5
 * @since 4.10.7 Added var documentation for `$this`.
 * @since 5.1.0 Updated template link.
 *
 * @version 5.1.0
 *
 * @var Tribe__Tickets_Plus__Meta__Field__Select $this
 */
$options = null;
if ( isset( $field['extra'] ) && ! empty( $field['extra']['options'] ) ) {
	$options = $field['extra']['options'];
}

if ( ! $options ) {
	return;
}

$option_id = "tribe-tickets-meta_{$this->slug}" . ( $attendee_id ? '_' . $attendee_id : '' );

?>
<div class="tribe-tickets-meta tribe-tickets-meta-select <?php echo $required ? 'tribe-tickets-meta-required' : ''; ?>">
	<label for="<?php echo esc_attr( $option_id ); ?>" class="tribe-tickets-meta-field-header"><?php echo wp_kses_post( $field['label'] ); ?></label>
	<select	<?php disabled( $this->is_restricted( $attendee_id ) ); ?> id="<?php echo esc_attr( $option_id ); ?>" class="ticket-meta" name="tribe-tickets-meta[<?php echo $attendee_id ?>][<?php echo esc_attr( $this->slug ); ?>]" <?php echo $required ? 'required' : ''; ?>>
		<option></option>
		<?php
		foreach ( $options as $option ) {
			?>
			<option <?php selected( $option, $value ); ?>><?php echo esc_html( $option ); ?></option>
			<?php
		}
		?>
	</select>
</div>
