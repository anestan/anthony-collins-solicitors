<?php
/**
 * Admin Attendee Meta
 *
 * @since 4.1
 * @since 5.2.2 Use admin views to render this template.
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

use Tribe\Tickets\Plus\Meta\Field_Types_Collection;

$classes = [
	'eventtable',
	'tribe-tickets-attendee-info-form',
	'ticket_advanced',
	'ticket_advanced_meta',
];

?>
<div
	id="tribe-tickets-attendee-info-form"
	<?php tribe_classes( $classes ); ?>
>
	<table class="eventtable">
		<tr class="tribe-attendee-fields-box">
			<td class='tribe-attendee-fields-new'>
				<?php $this->template( 'attendee-meta/fields-new' ); ?>
			</td>
			<td class='tribe-attendee-fields-existing'>
				<?php $this->template( 'attendee-meta/fields-existing' ); ?>
			</td>
		</tr>
	</table>
</div>
