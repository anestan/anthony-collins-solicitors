<?php
/**
 * Admin Attendee Meta: Active fields.
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

?>
<div id="tribe-tickets-attendee-sortables" class="meta-box-sortables ui-sortable">
	<?php
	foreach ( $active_meta as $meta_field ) {
		$field = $meta_object->generate_field( $ticket_id, $meta_field->type, (array) $meta_field );

		// Outputs HTML input field - no escaping.
		echo $field->render_admin_field();
	}
	?>
</div>
