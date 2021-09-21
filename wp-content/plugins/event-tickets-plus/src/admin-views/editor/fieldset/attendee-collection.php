<?php
/**
 * Template for showing Attendee Collection settings for the Classic Editor Ticket metabox.
 *
 * @since 5.1.0
 *
 * @var int      $post_id     The post ID.
 * @var int|null $ticket_id   The ticket ID.
 * @var array    $iac_options Available IAC options.
 * @var string   $selected    Current IAC option for the ticket.
 */

?>
<div
	data-depends="#Tribe__Tickets__RSVP_radio"
	data-condition-is-not-checked
>
	<button class="accordion-header tribe_attendee-collection_meta">
		<?php esc_html_e( 'Attendee Collection', 'event-tickets-plus' ); ?>
	</button>
	<section id="ticket_form_attendee_collection" class="attendee-collection accordion-content">
		<h4 class="accordion-label screen_reader_text"><?php esc_html_e( 'Attendee Collection Settings', 'event-tickets-plus' ); ?></h4>

		<p><?php esc_html_e( 'Select the default way to sell tickets. Enabling Individual Attendee Collection will allow purchasers to enter a name and email for each ticket.', 'event-tickets-plus' ); ?></p>

		<?php foreach ( $iac_options as $value => $label ) : ?>
			<div class="input_block">
				<input
					type="radio"
					name="ticket_iac"
					id="ticket_iac_setting_<?php echo esc_attr( sanitize_title_with_dashes( $value ) ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					<?php checked( $value, $selected ); ?>
				/>

				<label for="ticket_iac_setting_<?php echo esc_attr( sanitize_title_with_dashes( $value ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			</div>
		<?php endforeach; ?>

		<div id="attendee_collection_fields">
			<?php
			/**
			 * Allows for the insertion of additional content into the ticket edit form - Attendee Collection section.
			 *
			 * @since 5.1.0
			 */
			$this->do_entry_point( 'additional_fields' );
			?>
		</div>
	</section><!-- #ticket_form_attendee-collection -->
</div>
