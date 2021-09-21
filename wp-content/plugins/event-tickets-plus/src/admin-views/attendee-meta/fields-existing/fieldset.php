<?php
/**
 * Admin Attendee Meta: Select for existing fieldsets.
 *
 * @since 5.2.2
 * @since 5.2.3 Add classes to represent if the saved fields has IAC enabled (to show without outline).
 *
 * @version 5.2.3
 *
 * @var Tribe__Tickets_Plus__Admin__Views                  $this          [Global] Template object.
 * @var WP_Post[]                                          $templates     [Global] Array with the saved fieldsets.
 * @var array                                              $meta          [Global] Array containing the meta.
 * @var null|int                                           $ticket_id     [Global] The ticket ID.
 * @var bool                                               $fieldset_form [Global] True if in fieldset form context.
 * @var Tribe__Tickets_Plus__Meta                          $meta_object   [Global] The meta object.
 * @var Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] $active_meta   [Global] Array containing objects of active meta.
 */


/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
$iac = tribe( 'tickets-plus.attendee-registration.iac' );

$iac_default = $iac->get_default_iac_setting();

$iac_for_ticket = $iac->get_iac_setting_for_ticket( $ticket_id );

$iac_enabled_if_ticket    = ! empty( $ticket_id ) && ( $iac_for_ticket === $iac::ALLOWED_KEY || $iac_for_ticket === $iac::REQUIRED_KEY );
$iac_enabled_if_no_ticket = empty( $ticket_id ) && ( $iac_default === $iac::ALLOWED_KEY || $iac_default === $iac::REQUIRED_KEY );
$iac_enabled              = $iac_enabled_if_ticket || $iac_enabled_if_no_ticket;

$classes = [
	'tribe-tickets-attendee-saved-fields',
	'tribe-tickets__admin-attendees-saved-fields',
	'tribe-tickets__admin-attendees-saved-fields--has-iac' => $iac_enabled,
];

?>
<div <?php tribe_classes( $classes ); ?>>
	<div class="tribe-tickets-saved-fields-select">
		<p>
			<span class="tribe-tickets__admin-attendees-saved-fields-select-name-message">
				<?php
				echo esc_html(
					sprintf(
						/* Translators: %s: Plural tickets label lowercase. */
						_x(
							'The name and contact info of the person acquiring %s is collected by default.',
							'Attendee Info',
							'event-tickets-plus'
						),
						tribe_get_ticket_label_plural_lowercase( 'attendee_info' )
					)
				);
				?>
				<br />
			</span>
			<span class="tribe-tickets-add-new-fields">
				<?php if ( empty( $fieldset_form ) ) : ?>
					<?php esc_html_e( 'Collect additional info by adding fields from the menu at left or choosing a saved fieldset below.', 'event-tickets-plus' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Collect additional info by adding fields from the menu at left.', 'event-tickets-plus' ); ?>
				<?php endif; ?>
			</span>
		</p>
		<select
			class="chosen ticket-attendee-info-dropdown"
			name="ticket-attendee-info[MetaID]"
			id="saved_ticket-attendee-info"
			title="<?php esc_attr_e( 'Select an existing fieldset', 'event-tickets-plus' ); ?>"
		>

			<option selected value="0"><?php esc_html_e( 'Select an existing fieldset', 'event-tickets-plus' ); ?></option>
				<?php foreach ( $templates as $template ) : ?>
					<option data-attendee-group="<?php echo esc_attr( $template->post_title ); ?>"
						value="<?php echo esc_attr( $template->ID ); ?>"><?php echo esc_html( $template->post_title ); ?></option>
				<?php endforeach; ?>
		</select>
	</div>
</div>
