<?php
/**
 * Admin Attendee Meta: Active fields section.
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
<?php $this->template( 'attendee-meta/fields-existing/iac/notice' ); ?>

<h5><?php esc_html_e( 'Active Fields:', 'event-tickets-plus' ); ?></h5>

<?php $this->template( 'attendee-meta/fields-existing/iac/fields' ); ?>

<?php $this->template( 'attendee-meta/fields-existing/fieldset' ); ?>

<?php $this->template( 'attendee-meta/fields-existing/fields' ); ?>

<?php $this->template( 'attendee-meta/fields-existing/fieldset-save' ); ?>

<input type="hidden" name="tribe-tickets-input[0]" value="">
