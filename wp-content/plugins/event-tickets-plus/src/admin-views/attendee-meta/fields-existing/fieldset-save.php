<?php
/**
 * Admin Attendee Meta: Checkbox to save fieldset.
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

// Bail if we're on the fieldset form.
if ( ! empty( $fieldset_form ) ) {
	return;
}

?>
<div class="tribe-tickets-input tribe-tickets-attendee-save-fieldset">
	<label>
		<input
			type="checkbox"
			name="tribe-tickets-save-fieldset"
			id="save_attendee_fieldset"
			value="on"
			class="ticket_field save_attendee_fieldset"
			data-tribe-toggle="tribe-tickets-attendee-saved-fieldset-name"
		>
		<?php
		echo esc_html(
			sprintf(
				// Translators: %s: Tickets (plural) text.
				__( 'Save this fieldset for use on other %s?', 'event-tickets-plus' ),
				tribe_get_ticket_label_plural_lowercase( 'fieldset' )
			)
		);
		?>
	</label>
</div>
<div id="tribe-tickets-attendee-saved-fieldset-name" class="tribe-tickets-input tribe-tickets-attendee-saved-fieldset-name">
	<label for="tribe-tickets-saved-fieldset-name"><?php esc_html_e( 'Name this fieldset:', 'event-tickets-plus' ); ?></label>
	<input type="text" class="ticket_field" name="tribe-tickets-saved-fieldset-name" value="">
</div>
