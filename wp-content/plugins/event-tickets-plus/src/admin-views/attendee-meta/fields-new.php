<?php
/**
 * Admin Attendee Meta: Field types.
 * This template list the different field types that can be added as attendee registration meta.
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

use Tribe\Tickets\Plus\Meta\Field_Types_Collection;

$field_types = tribe( Field_Types_Collection::class )->get();
?>
<h5><?php esc_html_e( 'Add New Field:', 'event-tickets-plus' ); ?></h5>

<ul class="tribe-tickets-attendee-info-options">
	<?php
	foreach ( $field_types as $type => $name ) :
		?>
		<li id="tribe-tickets-add-<?php echo esc_attr( $type ); ?>" class="tribe-tickets-attendee-info-option">
			<a
				href="#"
				class="add-attendee-field"
				data-type="<?php echo esc_attr( $type ); ?>"
			><?php echo esc_html( $name ); ?> <span class="dashicons dashicons-plus-alt"></span></a>
		</li>
	<?php endforeach; ?>
</ul>
