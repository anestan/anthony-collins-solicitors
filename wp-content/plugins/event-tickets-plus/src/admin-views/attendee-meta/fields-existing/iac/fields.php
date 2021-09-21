<?php
/**
 * Admin Attendee Meta: IAC mocked name field.
 *
 * @since 5.2.2
 *
 * @version 5.2.2
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

if (
	! empty( $fieldset_form )
	&& ( $iac_default !== $iac::ALLOWED_KEY && $iac_default !== $iac::REQUIRED_KEY )
) {
	return;
}

$iac_for_ticket = $iac->get_iac_setting_for_ticket( $ticket_id );

$should_hide_fields_if_ticket    = ! empty( $ticket_id ) && ( $iac_for_ticket !== $iac::ALLOWED_KEY && $iac_for_ticket !== $iac::REQUIRED_KEY );
$should_hide_fields_if_no_ticket = empty( $ticket_id ) && empty( $fieldset_form );
$should_hide_fields              = $should_hide_fields_if_ticket || $should_hide_fields_if_no_ticket;

$classes = [
	'tribe-tickets__admin-attendees-info-iac-fields',
	'tribe-common-a11y-hidden' => $should_hide_fields,
];

$iac_is_required = ( ! empty( $fieldset_form ) && $iac_default === $iac::REQUIRED_KEY ) || $iac_for_ticket === $iac::REQUIRED_KEY;

$iac_required_classes = [
	'tribe-tickets__admin-attendee-info-field-title-label',
	'tribe-tickets__admin-attendee-info-field-title-label--required',
	'tribe-common-a11y-hidden' => ! $iac_is_required,
];

?>
<div <?php tribe_classes( $classes ); ?>>
	<div class="tribe-tickets__admin-attendee-info-field tribe-tickets__admin-attendee-info-field--iac meta-postbox">
		<div
			class="postbox-header"
			title="<?php esc_attr_e( 'Name and email are collected by default because IAC is enabled.', 'event-tickets-plus' ); ?>"
		>
			<h2 class="tribe-tickets__admin-attendee-info-field-title">
				<span>
					<span class="tribe-tickets-attendee-info-field-type tribe-tickets__admin-attendee-info-field-title-type">
						<?php esc_html_e( 'Name', 'event-tickets-plus' ); ?>
					</span>
					<span <?php tribe_classes( $iac_required_classes ); ?>>
						<?php esc_html_e( 'Required', 'event-tickets-plus' ); ?>
					</span>
				</span>
			</h2>
			<div class="handle-actions tribe-tickets__admin-attendee-info-field-handle-actions">
				<?php esc_html_e( 'Collected by default', 'event-tickets-plus' ); ?>
				<i class="dashicons dashicons-lock"></i>
			</div>
		</div>
	</div>

	<div class="tribe-tickets__admin-attendee-info-field tribe-tickets__admin-attendee-info-field--iac meta-postbox">
		<div
			class="postbox-header"
			title="<?php esc_attr_e( 'Name and email are collected by default because IAC is enabled.', 'event-tickets-plus' ); ?>"
		>
			<h2 class="tribe-tickets__admin-attendee-info-field-title">
				<span>
					<span class="tribe-tickets-attendee-info-field-type tribe-tickets__admin-attendee-info-field-title-type">
						<?php esc_html_e( 'Email', 'event-tickets-plus' ); ?>
					</span>
					<span <?php tribe_classes( $iac_required_classes ); ?>>
						<?php esc_html_e( 'Required', 'event-tickets-plus' ); ?>
					</span>
				</span>
			</h2>
			<div class="handle-actions tribe-tickets__admin-attendee-info-field-handle-actions">
				<?php esc_html_e( 'Collected by default', 'event-tickets-plus' ); ?>
				<i class="dashicons dashicons-lock"></i>
			</div>
		</div>
	</div>
</div>
