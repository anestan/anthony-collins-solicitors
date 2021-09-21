<?php

namespace Tribe\Tickets\Plus\Attendee_Registration;

/**
 * Class Modal
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration
 *
 * @since   5.1.0
 */
class Modal {
	/**
	 * Set up the hooks needed for the class.
	 *
	 * @since 5.1.0
	 */
	public function hook() {
		add_action( 'tribe_tickets_plus_render_ar_modal_template', [ $this, 'append_modal_ar_template' ] );
		add_action( 'tribe_template_entry_point:tickets/v2/tickets:after_form', [ $this, 'render_modal_target' ] );
		add_action( 'tribe_template_before_include:tickets/v2/tickets/item/content', [ $this, 'render_modal_item_remove' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets/v2/tickets/item/opt-out', [ $this, 'render_modal_item_total' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets/v2/tickets/item/opt-out', [ $this, 'render_modal_item_opt_out' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets/v2/tickets/submit/button', [ $this, 'render_modal_submit_button' ], 10, 3 );
	}

	/**
	 * Render the modal target needed for the AR modal after the form in the Tickets block.
	 *
	 * @since 5.1.0
	 */
	public function render_modal_target() {
		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		$template->template( 'v2/modal/target' );
	}

	/**
	 * Render the modal item remove template.
	 *
	 * @since 5.1.0
	 *
	 * @param string           $file        Complete path to include the PHP File.
	 * @param array            $name        Template name.
	 * @param \Tribe__Template $et_template Current instance of the Tribe__Template.
	 */
	public function render_modal_item_remove( $file, $name, $et_template ) {
		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		$context = [
			'is_modal' => $et_template->get( 'is_modal', false ),
		];

		$template->template( 'v2/modal/item/remove', $context );
	}

	/**
	 * Render the modal item total template.
	 *
	 * @since 5.1.0
	 *
	 * @param string           $file        Complete path to include the PHP File.
	 * @param array            $name        Template name.
	 * @param \Tribe__Template $et_template Current instance of the Tribe__Template.
	 */
	public function render_modal_item_total( $file, $name, $et_template ) {
		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		$context = [
			'is_modal' => $et_template->get( 'is_modal', false ),
			'is_mini'  => $et_template->get( 'is_mini', false ),
			'ticket'   => $et_template->get( 'ticket' ),
			'currency' => $et_template->get( 'currency' ),
			'post_id'  => $et_template->get( 'post_id' ),
			'provider' => $et_template->get( 'provider' ),
		];

		$template->template( 'v2/modal/item/total', $context );
	}

	/**
	 * Render the modal item opt-out template.
	 *
	 * @since 5.1.0
	 *
	 * @param string           $file        Complete path to include the PHP File.
	 * @param array            $name        Template name.
	 * @param \Tribe__Template $et_template Current instance of the Tribe__Template.
	 */
	public function render_modal_item_opt_out( $file, $name, $et_template ) {
		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		$context = [
			'is_modal' => $et_template->get( 'is_modal', false ),
			'ticket'   => $et_template->get( 'ticket' ),
			'privacy' => $et_template->get( 'privacy' ),
			'post_id' => $et_template->get( 'post_id' ),
		];

		$template->template( 'v2/modal/item/opt-out', $context );
	}

	/**
	 * Render the modal submit button template.
	 *
	 * @since 5.1.0
	 *
	 * @param string           $file        Complete path to include the PHP File.
	 * @param array            $name        Template name.
	 * @param \Tribe__Template $et_template Current instance of the Tribe__Template.
	 */
	public function render_modal_submit_button( $file, $name, $et_template ) {
		/** @var \Tribe__Tickets_Plus__Template $template */
		$template = tribe( 'tickets-plus.template' );

		/** @var \Tribe__Tickets__Tickets $provider */
		$provider = $et_template->get( 'provider' );

		/** @var \Tribe\Tickets\Plus\Attendee_Registration\View $view */
		$view = tribe( 'tickets-plus.attendee-registration.view' );

		$args = [
			'post_id'             => $et_template->get( 'post_id' ),
			'tickets'             => $et_template->get( 'tickets', [] ),
			'provider'            => $provider,
			'provider_id'         => $et_template->get( 'provider_id' ),
			'provider_class'      => $view->get_form_class( $provider ),
			'cart_url'            => $et_template->get( 'cart_url' ),
			'tickets_on_sale'     => $et_template->get( 'tickets_on_sale' ),
			'has_tickets_on_sale' => $et_template->get( 'has_tickets_on_sale' ),
			'is_sale_past'        => $et_template->get( 'is_sale_past' ),
			'must_login'          => $et_template->get( 'must_login' ),
			'is_modal'            => true,
			'has_tpp'             => false,
			'meta'                => tribe( 'tickets-plus.meta' ),
			'view'                => tribe( 'tickets-plus.attendee-registration.view' ),
			'currency'            => tribe( 'tickets.commerce.currency' ),
			'field_render'        => tribe_callback( 'tickets-plus.attendee-registration.fields', 'render' ),
		];

		$providers = array_map( static function( $ticket ) {
			/** @var \Tribe__Tickets__Ticket_Object $ticket */
			return $ticket->get_provider();
		}, $args['tickets'] );
		$providers = array_unique( array_filter( $providers ) );

		$args['providers'] = $providers;

		$tribe_commerce_attendee_object = \Tribe__Tickets__Commerce__PayPal__Main::ATTENDEE_OBJECT;

		// Determine if we have a Tribe Commerce provider.
		if (
			$tribe_commerce_attendee_object === $provider->attendee_object
			|| in_array( $tribe_commerce_attendee_object, $providers, true )
		) {
			$args['has_tpp'] = true;
		}

		$template->add_template_globals( $args );

		$template->template( 'v2/tickets/submit/button-modal' );
	}
}
