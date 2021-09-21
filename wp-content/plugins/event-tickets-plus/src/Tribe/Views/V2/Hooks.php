<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Tickets\Plus\Views\V2\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'event-tickets-plus.views.v2.hooks' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Tickets\Plus\Views\V2\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'event-tickets-plus.views.v2.hooks' ), 'some_method' ] );
 *
 * @since   5.1.1
 *
 * @package Tribe\Tickets\Plus\Views\V2
 */

namespace Tribe\Tickets\Plus\Views\V2;

use Tribe__Customizer__Section as Customizer_Section;

/**
 * Class Hooks
 *
 * @since   5.1.1
 *
 * @package Tribe\Tickets\Plus\Views\V2
 */
class Hooks extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up the classes that should be hooked to filter and actions.
	 *
	 * @since 5.1.1
	 */
	public function register() {
		$this->container->singleton( Customizer::class, Customizer::class );

		$this->add_filters();
	}

	/**
	 * Hooks the filters required for the Views v2 integration to work.
	 *
	 * @since 5.1.1
	 */
	protected function add_filters() {
		// Customizer.
		add_filter( 'tribe_customizer_print_styles_action', [ $this, 'print_inline_styles_in_footer' ] );
		add_filter( 'tribe_customizer_global_elements_css_template', [ $this, 'filter_global_elements_css_template' ], 10, 3 );
		add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'customizer_inline_stylesheets' ], 12 );
	}

	/**
	 * Changes the action the Customizer should use to try and print inline styles to print the inline
	 * styles in the footer.
	 *
	 * @since 5.1.1
	 *
	 * @return string The action the Customizer should use to print inline styles.
	 */
	public function print_inline_styles_in_footer() {
		return 'wp_print_footer_scripts';
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.1.1
	 *
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Global Elements section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_global_elements_css_template( $css_template, $section, $customizer ) {
		if ( ! ( is_string( $css_template ) && $section instanceof Customizer_Section && $customizer instanceof \Tribe__Customizer ) ) {
			return $css_template;
		}

		return $this->container->make( Customizer::class )->filter_global_elements_css_template( $css_template, $section, $customizer );
	}

	/**
	 * Add views stylesheets to customizer styles array to check.
	 *
	 * @param array<string> $sheets Array of sheets to search for.
	 *
	 * @return array<string> Modified array of sheets to search for.
	 */
	public function customizer_inline_stylesheets( $sheets ) {
		return array_merge( $sheets, [ 'tribe-tickets-plus-registration-page-styles' ] );
	}
}
