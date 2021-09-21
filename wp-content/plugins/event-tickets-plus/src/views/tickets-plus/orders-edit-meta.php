<?php
/**
 * Renders the meta fields for order editing
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe/tickets-plus/orders-edit-meta.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   4.4.3
 * @since   4.10.2 Set global for whether or not a ticket has any meta fields to show.
 * @since   4.10.7 Rearranged some variables.
 * @since   4.11.2 Use customizable ticket name functions.
 * @since   5.1.0 Replaced usage of Tribe__Tickets_Plus__Meta::meta_enabled() with ticket_has_meta() method and add support for resending email.
 * @since   5.2.0 Added $allow_resending_email variable usage.
 *
 * @version 5.2.0
 *
 * @see \Tribe__Tickets__Tickets::get_attendee() Each ticket provider implements this method.
 * @var array        $attendee                    The attendee information.
 * @var string       $field_slug_for_resend_email The slug for the Re-send Email field.
 * @var WP_Post|null $ticket                      The ticket post object.
 * @var bool         $allow_resending_email       If resending email is allowed.
 */

global $tribe_my_tickets_have_meta;

/** @var Tribe__Tickets_Plus__Meta $meta */
$meta = tribe( 'tickets-plus.meta' );

if ( ! $ticket instanceof WP_Post ) {
	?>
	<p>
		<?php
		echo esc_html(
			sprintf(
				__( '%s deleted: attendee info cannot be updated.', 'event-tickets-plus' ),
				tribe_get_ticket_label_singular( 'orders_edit_meta' )
			)
		);
		?>
	</p>
	<?php

	return;
}

if ( $meta->ticket_has_meta( $ticket->ID ) ) {
	$tribe_my_tickets_have_meta = true;
	?>
	<div
		class="tribe-event-tickets-plus-meta"
		id="tribe-event-tickets-plus-meta-<?php echo esc_attr( $ticket->ID ); ?>"
		data-ticket-id="<?php echo esc_attr( $ticket->ID ); ?>"
		data-attendee-id="<?php echo esc_attr( $attendee['attendee_id'] ); ?>"
	>
		<a class="attendee-meta toggle show">
			<?php esc_html_e( 'Toggle attendee info', 'event-tickets-plus' ); ?>
		</a>

		<div class="attendee-meta-row">
			<?php
			$meta_fields = $meta->get_meta_fields_by_ticket( $ticket->ID );
			foreach ( $meta_fields as $field ) {
				echo $field->render( $attendee['attendee_id'] );
			}
			?>
		</div>
	</div>

	<?php
	/** @var \Tribe__Tickets_Plus__Template $template */
	$template = tribe( 'tickets-plus.template' );

	// Add the rendering attributes into global context.
	$template_globals = [
		'attendee'                    => $attendee,
		'ticket'                      => $ticket,
		'field_slug_for_resend_email' => $field_slug_for_resend_email,
		'allow_resending_email'       => $allow_resending_email,
	];

	$template->add_template_globals( $template_globals );
	$template->template( 'v2/iac/my-tickets/resend-email-template' );
}
