<?php
/**
 * Attendee information.
 *
 * @since 4.1
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

<button class="accordion-header tribe_attendee_meta">
	<?php esc_html_e( 'Attendee Information', 'event-tickets-plus' ); ?>
</button>
<section class="accordion-content">
	<?php $this->template( 'meta-content' ); ?>
</section>
