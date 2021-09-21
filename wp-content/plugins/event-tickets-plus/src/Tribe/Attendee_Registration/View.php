<?php

namespace Tribe\Tickets\Plus\Attendee_Registration;

use Tribe__Tickets__Tickets;
use Tribe__Tickets_Plus__Template;

/**
 * Class View
 *
 * @package Tribe\Tickets\Plus\Attendee_Registration
 *
 * @since   5.1.0
 */
class View extends Tribe__Tickets_Plus__Template {
	/**
	 * Get the Attendee Registration page content.
	 *
	 * @since 5.1.0
	 *
	 * @return string The Attendee Registration page content.
	 */
	public function get_page_content() {
		$q_provider       = tribe_get_request_var( 'provider', false );
		$tickets_in_cart  = Tribe__Tickets__Tickets::get_tickets_in_cart_for_provider( $q_provider );
		$events           = [];
		$providers        = [];
		$default_provider = [];
		$non_meta_count   = 0;

		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta = tribe( 'tickets-plus.meta' );

		foreach ( $tickets_in_cart as $ticket_id => $quantity ) {
			// Load the tickets in cart for each event, with their ID, quantity and provider.

			/** @var \Tribe__Tickets__Tickets_Handler $handler */
			$handler = tribe( 'tickets.handler' );

			/** @var \Tribe__Tickets__Ticket_Object $ticket */
			$ticket = $handler->get_object_connections( $ticket_id );

			if ( ! $ticket->provider instanceof Tribe__Tickets__Tickets ) {
				continue;
			}

			if ( ! $meta->ticket_has_meta( $ticket_id ) ) {
				$non_meta_count += $quantity;
			}

			$ticket_providers = [ $ticket->provider->attendee_object ];

			if ( ! empty( $ticket->provider->orm_provider ) ) {
				$ticket_providers[] = $ticket->provider->orm_provider;
			}

			// If we've got a provider and it doesn't match, skip the ticket.
			if ( ! in_array( $q_provider, $ticket_providers, true ) ) {
				continue;
			}

			$ticket_data = [
				'id'       => $ticket_id,
				'qty'      => $quantity,
				'iac'      => IAC::NONE_KEY,
				'provider' => $ticket->provider,
			];

			/**
			 * Allow filtering the ticket data used on the Attendee Registration page.
			 *
			 * @since 5.1.0
			 *
			 * @param array                   $ticket_data The ticket data to use for the Attendee Registration page.
			 * @param Tribe__Tickets__Tickets $provider    The provider object for the ticket.
			 */
			$ticket_data = apply_filters( 'tribe_tickets_plus_attendee_registration_view_ticket_data', $ticket_data, $ticket->provider );

			if ( empty( $default_provider ) ) {
				// One provider per instance.
				$default_provider[ $q_provider ] = $ticket->provider->class_name;
			}

			/** @var \Tribe__Tickets__Status__Manager $status */
			$status   = tribe( 'tickets.status' );
			$provider = $status->get_provider_slug( $ticket->provider->class_name );

			$providers[ $ticket->event ] = $provider;
			$events[ $ticket->event ][]  = $ticket_data;
		}

		/**
		 * Check if the cart has a ticket with required meta fields
		 *
		 * @since 5.1.0
		 *
		 * @param boolean $cart_has_required_meta Whether the cart has required meta.
		 * @param array   $tickets_in_cart        The array containing the cart elements. Format array( 'ticket_id' => 'quantity' ).
		 */
		$cart_has_required_meta = (bool) apply_filters( 'tribe_tickets_attendee_registration_has_required_meta', ! empty( $tickets_in_cart ), $tickets_in_cart );

		/** @var \Tribe__Tickets__Attendee_Registration__Main $attendee_registration */
		$attendee_registration = tribe( 'tickets.attendee_registration' );

		// Get the checkout URL, it'll be added to the checkout button.
		$checkout_url = $attendee_registration->get_checkout_url();

		/**
		 * Filter to check if there's any required meta that wasn't filled in
		 *
		 * @since 5.1.0
		 *
		 * @param bool
		 */
		$is_meta_up_to_date = (int) apply_filters( 'tribe_tickets_attendee_registration_is_meta_up_to_date', true );

		// Enqueue styles and scripts for this page.
		tribe_asset_enqueue_group( 'tribe-tickets-registration-page' );

		// One provider per instance.
		$currency        = tribe( 'tickets.commerce.currency' );
		$currency_config = $currency->get_currency_config_for_provider( $default_provider, null );

		/**
		 *  Set all the template variables
		 */
		$args = [
			'events'                 => $events,
			'checkout_url'           => $checkout_url,
			'is_meta_up_to_date'     => $is_meta_up_to_date,
			'cart_has_required_meta' => $cart_has_required_meta,
			'providers'              => $providers,
			'meta'                   => tribe( 'tickets-plus.meta' ),
			'field_render'           => tribe_callback( 'tickets-plus.attendee-registration.fields', 'render' ),
			'currency'               => $currency,
			'currency_config'        => $currency_config,
			'is_modal'               => null,
			'non_meta_count'         => $non_meta_count,
			'handler'                => tribe( 'tickets.handler' ),
		];

		if ( tribe_tickets_new_views_is_enabled() ) {
			$provider     = tribe_get_request_var( 'provider' );
			$provider_obj = null;

			if ( empty( $provider ) ) {
				$event_keys   = array_keys( $events );
				$event_key    = array_shift( $event_keys );
				$provider_obj = Tribe__Tickets__Tickets::get_event_ticket_provider_object( $event_key );
			} elseif ( is_string( $provider ) ) {
				$provider_obj = $this->get_cart_provider( $provider );
			} elseif ( $provider instanceof Tribe__Tickets__Tickets ) {
				$provider_obj = $provider;
			}

			if ( $provider_obj instanceof Tribe__Tickets__Tickets ) {
				$provider = $provider_obj->attendee_object;
			}

			// @todo We might need $provider_obj later on in the templating?
			// @todo Look throughout usage of $provider and see where we get object and change those.

			$args['provider'] = $provider;
			$args['cart_url'] = $this->get_cart_url( $provider );
		}

		$registration_page_script_handle = tribe_tickets_new_views_is_enabled() ? 'tribe-tickets-plus-registration-page' : 'tribe-tickets-registration-page-scripts';

		wp_localize_script(
			$registration_page_script_handle,
			'TribeCurrency',
			[
				'formatting' => wp_json_encode( $currency_config ),
			]
		);
		wp_localize_script(
			$registration_page_script_handle,
			'TribeCartEndpoint',
			[
				'url' => tribe_tickets_rest_url( '/cart/' ),
			]
		);

		wp_enqueue_style( 'dashicons' );

		// Check whether we use v1 or v2. We need to update this when we deprecate tickets v1.
		if ( ! tribe_tickets_new_views_is_enabled() ) {
			// Call the old template view class.
			/** @var \Tribe__Tickets__Attendee_Registration__View $template */
			$template = tribe( 'tickets.attendee_registration.view' );

			$template->add_template_globals( $args );

			return $template->template( 'registration-js/content', $args, false );
		}

		/**
		 * Add the rendering attributes into global context.
		 *
		 * Start with the following for template files loading this global context.
		 * Keep all templates with this starter block of comments updated if these global args update.
		 *
		 * @var \Tribe\Tickets\Plus\Attendee_Registration\View $this                   [Global] The AR View instance.
		 * @var array                                          $events                 [Global] Multidimensional array of post IDs with their ticket data.
		 * @var string                                         $checkout_url           [Global] The checkout URL.
		 * @var bool                                           $is_meta_up_to_date     [Global] True if the meta is up to date.
		 * @var bool                                           $cart_has_required_meta [Global] True if the cart has required meta.
		 * @var array                                          $providers              [Global] Array of providers, by event.
		 * @var \Tribe__Tickets_Plus__Meta                     $meta                   [Global] Meta object.
		 * @var \Closure                                       $field_render           [Global] Call to \Tribe\Tickets\Plus\Attendee_Registration\Fields::render().
		 * @var \Tribe__Tickets__Commerce__Currency            $currency               [Global] The tribe commerce currency object.
		 * @var mixed                                          $currency_config        [Global] Currency configuration for default provider.
		 * @var bool                                           $is_modal               [Global] True if it's in the modal context.
		 * @var int                                            $non_meta_count         [Global] Number of tickets without meta fields.
		 * @var string                                         $provider               [Global] The tickets provider slug.
		 * @var string                                         $cart_url               [Global] Link to Cart (could be empty).
		 */
		$this->add_template_globals( $args );

		return $this->template( 'v2/attendee-registration/content', [], false );
	}

	/**
	 * Get the provider Cart URL.
	 *
	 * @since 5.1.0
	 *
	 * @param string $provider Provider identifier.
	 *
	 * @return bool|string
	 */
	public function get_cart_url( $provider ) {
		if ( is_numeric( $provider ) ) {
			/** @var \Tribe__Tickets__Tickets_Handler $tickets_handler */
			$tickets_handler = tribe( 'tickets.handler' );
			$provider        = get_post_meta( absint( $provider ), $tickets_handler->key_provider_field, true );
		}

		if ( empty( $provider ) ) {
			return false;
		}

		$post_provider = $this->get_cart_provider( $provider );

		if ( empty( $post_provider ) ) {
			return false;
		}

		try {
			if ( 'Tribe__Tickets_Plus__Commerce__WooCommerce__Main' === get_class( $post_provider ) ) {
				/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $provider */
				$provider = tribe( 'tickets-plus.commerce.woo' );
			} elseif ( 'Tribe__Tickets_Plus__Commerce__EDD__Main' === get_class( $post_provider ) ) {
				/** @var \Tribe__Tickets_Plus__Commerce__EDD__Main $provider */
				$provider = tribe( 'tickets-plus.commerce.edd' );
			} else {
				return;
			}
		} catch ( RuntimeException $exception ) {
			return;
		}

		if ( ! $provider instanceof Tribe__Tickets__Tickets ) {
			return false;
		}

		return $provider->get_cart_url();
	}


	/**
	 * Get the cart provider class/object.
	 *
	 * @since 5.1.0
	 *
	 * @param string $provider A string indicating the desired provider.
	 *
	 * @return bool|object The provider object or boolean false if none found.
	 */
	public function get_cart_provider( $provider ) {
		if ( empty( $provider ) ) {
			return false;
		}

		$provider_obj = false;

		/**
		 * Allow providers to include themselves if they are not in the above.
		 *
		 * @since 4.11.0
		 *
		 * @param string $provider A string indicating the desired provider.
		 *
		 * @return boolean|object The provider object or boolean false if none found above.
		 */
		$provider_obj = apply_filters( 'tribe_attendee_registration_cart_provider', $provider_obj, $provider );

		if (
			! $provider_obj instanceof Tribe__Tickets__Tickets
			|| ! tribe_tickets_is_provider_active( $provider_obj )
		) {
			$provider_obj = false;
		}

		return $provider_obj;
	}

	/**
	 * Given a provider, get the class to be applied to the attendee registration form.
	 *
	 * @since 5.1.0
	 *
	 * @param string|Tribe__Tickets__Tickets $provider The provider/attendee object name indicating ticket provider.
	 *
	 * @return string The class string or empty string if provider not found or not active.
	 */
	public function get_form_class( $provider ) {
		$class = '';

		if ( is_string( $provider ) ) {
			$provider = Tribe__Tickets__Tickets::get_ticket_provider_instance( $provider );
		}

		if ( ! empty( $provider ) ) {
			$provider = $provider->attendee_object;
		}

		if ( empty( $provider ) ) {
			/**
			 * Allows filtering the class before returning it in the case of no provider.
			 *
			 * @since 4.10.4
			 *
			 * @param string $class The (empty) class string.
			 */
			return apply_filters( 'tribe_attendee_registration_form_no_provider_class', $class );
		}

		/**
		 * Allow providers to include their own strings/suffixes.
		 *
		 * @since 4.10.4
		 *
		 * @param array $provider_classes In the format of: $provider -> class suffix.
		 */
		$provider_classes = apply_filters( 'tribe_attendee_registration_form_classes', [] );

		if ( array_key_exists( $provider, $provider_classes ) ) {
			$prefix = tribe_tickets_new_views_is_enabled() ? 'tribe-tickets__attendee-tickets-form' : 'tribe-tickets__item__attendee__fields__form';
			$class  = $prefix . '--' . $provider_classes[ $provider ];
		}

		/**
		 * Allows filtering the class before returning it.
		 *
		 * @since 4.10.4
		 *
		 * @param string $class The class string.
		 */
		return apply_filters( 'tribe_attendee_registration_form_class', $class );
	}
}
