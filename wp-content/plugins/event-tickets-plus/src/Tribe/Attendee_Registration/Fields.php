<?php

namespace Tribe\Tickets\Plus\Attendee_Registration;

use Tribe__Utils__Array as Arr;

/**
 * Class Fields
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration
 *
 * @since   5.1.0
 */
class Fields {
	/**
	 * Get the form fields for ticket meta.
	 *
	 * @since 5.1.0
	 *
	 * @param \Tribe__Tickets__Ticket_Object $ticket      The ticket object.
	 * @param int                            $post_id     The post ID.
	 * @param int|null                       $attendee_id The attendee ID.
	 *
	 * @return string The template HTML.
	 */
	public function get_html( $ticket, $post_id, $attendee_id = null ) {
		return $this->render( $ticket, $post_id, $attendee_id, true );
	}

	/**
	 * Renders the form fields for ticket meta.
	 *
	 * @since 5.1.0
	 * @since 5.2.0 Added $values parameter to pass along the values of each field to be rendered.
	 *
	 * @param \Tribe__Tickets__Ticket_Object $ticket      The ticket object.
	 * @param int                            $post_id     The post ID.
	 * @param int|null                       $attendee_id The attendee ID.
	 * @param boolean                        $echo        Whether to echo the fields.
	 * @param array                          $values      List of values for meta fields.
	 *
	 * @return string The template string, even if it's rendered on screen already.
	 */
	public function render( $ticket, $post_id, $attendee_id = null, $echo = true, $values = [] ) {
		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta = tribe( 'tickets-plus.meta' );

		$fields = $meta->get_meta_fields_by_ticket( $ticket->ID );

		if ( empty( $fields ) ) {
			return;
		}

		/** @var \Tribe__Tickets_Plus__Meta__Storage $storage */
		$storage = tribe( 'tickets-plus.meta.storage' );

		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		// Enforce attendee ID for templating and support JS placeholder.
		$attendee_id = tribe_tickets_plus_meta_field_get_attendee_id( $attendee_id );

		$template_args = [
			'post_id'     => $post_id,
			'ticket'      => $ticket,
			'attendee_id' => $attendee_id,
			'saved_meta'  => $storage->get_meta_data_for( $post_id ),
		];

		// Add the rendering attributes into global context.
		$template->add_template_globals( $template_args );

		$return = '';

		foreach ( $fields as $field ) {
			// Get value from $values[ $slug ]['value'] (direct from attendee meta as stored).
			$context = [
				'field'       => $field,
				'value'       => Arr::get( $values, [ $field->slug, 'value' ], null ),
				'field_name'  => tribe_tickets_plus_meta_field_name( $ticket->ID, $field->slug, $attendee_id ),
				'field_id'    => tribe_tickets_plus_meta_field_id( $ticket->ID, $field->slug, '', $attendee_id ),
				'required'    => $field->is_required(),
				'disabled'    => $field->is_restricted( $attendee_id ),
				'classes'     => $field->get_css_classes(),
				'attributes'  => $field->get_attributes(),
				'placeholder' => $field->get_placeholder(),
				'description' => $field->get_description(),
			];

			$return .= $template->template( 'v2/components/meta/' . $field->type, $context, $echo );
		}

		return $return;
	}

	/**
	 * Filter the tickets block ticket data attributes.
	 *
	 * @since 5.2.1
	 *
	 * @param array                         $attributes The HTML data attributes.
	 * @param Tribe__Tickets__Ticket_Object $ticket The ticket object.
	 *
	 * @return array The HTML data attributes.
	 */
	public function maybe_add_html_attribute_to_ticket( $attributes, $ticket ) {
		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta = tribe( 'tickets-plus.meta' );

		$fields = $meta->get_meta_fields_by_ticket( $ticket->ID );

		if ( empty( $fields ) ) {
			return $attributes;
		}

		$attributes['data-ticket-ar-fields'] = 'true';

		return $attributes;
	}
}
