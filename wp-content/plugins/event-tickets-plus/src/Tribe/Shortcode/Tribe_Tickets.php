<?php

namespace Tribe\Tickets\Plus\Shortcode;

use Tribe\Shortcode\Shortcode_Abstract;
use Tribe__Tickets__Editor__Blocks__Tickets;
use Tribe__Tickets__Editor__Template;
use Tribe__Tickets__Tickets as Tickets;
use WP_Post;

/**
 * Class for Shortcode Tribe_Tickets.
 *
 * @package Tribe\Tickets\Plus\Shortcode
 *
 * @since   4.12.1
 */
class Tribe_Tickets extends Shortcode_Abstract {

	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_tickets';

	/**
	 * {@inheritDoc}
	 */
	protected $default_arguments = [
		'post_id' => null,
	];

	/**
	 * {@inheritDoc}
	 */
	public $validate_arguments_map = [
		'post_id' => 'tribe_post_exists',
	];

	/**
	 * {@inheritDoc}
	 */
	public function get_default_arguments() {
		$default_arguments = parent::get_default_arguments();

		/**
		 * Default to current Post ID, even if zero, since validation via tribe_post_exists() requires passing some
		 * value. Respect if the attribute got set via filter from parent method.
		 */
		$default_arguments['post_id'] = absint( $default_arguments['post_id'] );

		if ( empty( $default_arguments['post_id'] ) ) {
			$default_arguments['post_id'] = absint( get_the_ID() );
		}

		return $default_arguments;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Do not alter `global $post` anywhere during shortcode rendering or template files.
	 */
	public function get_html() {
		$context = tribe_context();

		if ( is_admin() && ! $context->doing_ajax() ) {
			return '';
		}

		$post_id = absint( $this->get_argument( 'post_id' ) );

		return $this->get_tickets_block( $post_id );
	}

	/**
	 * Returns the block template's content.
	 *
	 * @since 4.12.1
	 * @since 4.12.3 Update usage of get_event_ticket_provider().
	 *
	 * @param WP_Post|int $post
	 *
	 * @return string HTML.
	 */
	public function get_tickets_block( $post ) {
		if ( empty( $post ) ) {
			return '';
		}

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		// If password protected, then do not display content.
		if ( post_password_required( $post ) ) {
			return '';
		}

		$post_id  = $post->ID;
		$provider = Tickets::get_event_ticket_provider_object( $post_id );

		if ( empty( $provider ) ) {
			return '';
		}

		/** @var Tribe__Tickets__Editor__Template $template */
		$template = tribe( 'tickets.editor.template' );

		/** @var Tribe__Tickets__Editor__Blocks__Tickets $blocks_tickets */
		$blocks_tickets = tribe( 'tickets.editor.blocks.tickets' );

		/** @var Tribe__Settings_Manager $settings_manager */
		$settings_manager = tribe( 'settings.manager' );

		$threshold = $settings_manager::get_option( 'ticket-display-tickets-left-threshold', null );

		/**
		 * Overwrites the threshold to display "# tickets left".
		 *
		 * @since 4.11.1
		 *
		 * @param int   $threshold Stock threshold to trigger display of "# tickets left"
		 * @param int   $post_id   WP_Post/Event ID.
		 */
		$threshold = absint( apply_filters( 'tribe_display_tickets_block_tickets_left_threshold', $threshold, $post_id ) );

		/**
		 * Allow filtering of the button name for the tickets block.
		 *
		 * @since 4.11.0
		 *
		 * @param string $button_name The button name. Set to cart-button to send to cart on submit, or set to checkout-button to send to checkout on submit.
		 */
		$submit_button_name = apply_filters( 'tribe_tickets_ticket_block_submit', 'cart-button' );

		/**
		 * Show original price on sale.
		 *
		 * @param bool Whether the original price should be shown on sale or not. Default is true.
		 *
		 * @return bool Whether the original price should be shown on sale or not.
		 */
		$show_original_price_on_sale = apply_filters( 'tribe_tickets_show_original_price_on_sale', true );

		// Load assets manually.
		$blocks_tickets->assets();

		$tickets = $provider->get_tickets( $post_id );

		$args = [
			'post_id'                     => $post_id,
			'provider'                    => $provider,
			'provider_id'                 => $provider->class_name,
			'tickets'                     => $tickets,
			'cart_classes'                => [ 'tribe-block', 'tribe-tickets' ], // @todo: deprecate with V1.
			'tickets_on_sale'             => $blocks_tickets->get_tickets_on_sale( $tickets ),
			'has_tickets_on_sale'         => tribe_events_has_tickets_on_sale( $post_id ),
			'is_sale_past'                => $blocks_tickets->get_is_sale_past( $tickets ),
			'is_sale_future'              => $blocks_tickets->get_is_sale_future( $tickets ),
			'currency'                    => tribe( 'tickets.commerce.currency' ),
			'handler'                     => tribe( 'tickets.handler' ),
			'privacy'                     => tribe( 'tickets.privacy' ),
			'threshold'                   => $threshold,
			'must_login'                  => ! is_user_logged_in() && $provider->login_required(),
			'show_original_price_on_sale' => $show_original_price_on_sale,
			'is_mini'                     => null,
			'is_modal'                    => null,
			'submit_button_name'          => $submit_button_name,
			'cart_url'                    => method_exists( $provider, 'get_cart_url' ) ? $provider->get_cart_url() : '',
			'checkout_url'                => method_exists( $provider, 'get_checkout_url' ) ? $provider->get_checkout_url() : '',
		];

		// Enqueue assets.
		tribe_asset_enqueue_group( 'tribe-tickets-block-assets' );
		tribe_asset_enqueue( 'tribe-tickets-forms-style' );
		tribe_asset_enqueue( 'event-tickets-tickets-css' );

		if ( tribe_tickets_new_views_is_enabled() ) {
			$echo = false;
			$before_content = '';

			/**
			 * A flag we can set via filter, e.g. at the end of this method, to ensure this template only shows once.
			 *
			 * @since 4.5.6
			 *
			 * @param boolean $already_rendered Whether the order link template has already been rendered.
			 *
			 * @see Tribe__Tickets__Tickets_View::inject_link_template()
			 */
			$already_rendered = apply_filters( 'tribe_tickets_order_link_template_already_rendered', false );

			// Output order links / view link if we haven't already (for RSVPs).
			if ( ! $already_rendered ) {
				$before_content = $template->template( 'blocks/attendees/order-links', $args, $echo );

				if ( empty( $before_content ) ) {
					$before_content = $template->template( 'blocks/attendees/view-link', $args, $echo );
				}

				add_filter( 'tribe_tickets_order_link_template_already_rendered', '__return_true' );
			}

			return $before_content . $template->template( 'v2/tickets', $args, $echo );
		}

		return $template->template( 'blocks/tickets', $args, false );
	}

}
