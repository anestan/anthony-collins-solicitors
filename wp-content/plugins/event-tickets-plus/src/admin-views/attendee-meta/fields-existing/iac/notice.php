<?php
/**
 * Admin Attendee Meta: IAC notice.
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

// If we are in the fieldset form and IAC is not allowed/required, don't show the notice.
if (
	! empty( $fieldset_form )
	&& ( $iac_default !== $iac::ALLOWED_KEY && $iac_default !== $iac::REQUIRED_KEY )
) {
	return;
}

$iac_for_ticket = $iac->get_iac_setting_for_ticket( $ticket_id );

/*
 * For now, that RSVP is not supported we hide it.
 *
 * @todo Revisit this once we add IAC for RSVPs.
 */
$should_hide_notice_if_no_ticket = empty( $ticket_id ) && empty( $fieldset_form );

$should_hide_notice_if_ticket = ! empty( $ticket_id ) && ( $iac_for_ticket !== $iac::ALLOWED_KEY && $iac_for_ticket !== $iac::REQUIRED_KEY );
$should_hide_notice           = $should_hide_notice_if_ticket || $should_hide_notice_if_no_ticket;

$classes = [
	'ticket-editor-notice',
	'info',
	'tribe-tickets__admin-attendees-info-iac-notice',
	'tribe-common-a11y-hidden' => $should_hide_notice,
];

?>
<div <?php tribe_classes( $classes ); ?>>
	<span class="dashicons dashicons-info"></span>
	<span class="message">
		<strong><?php esc_html_e( 'Individual Attendee Collection is active', 'event-tickets-plus' ); ?></strong>
		<p><?php esc_html_e( 'The name and email address of each attendee is collected by default and has been added to this fieldset.', 'event-tickets-plus' ); ?> <a href="https://theeventscalendar.com/knowledgebase/k/collecting-attendee-information-for-tickets-and-rsvp/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn More', 'event-tickets-plus' ); ?></a></p>
	</span>
</div>
