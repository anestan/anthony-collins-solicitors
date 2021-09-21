<?php
/**
 * Handles Views v2 Customizer settings.
 *
 * @since   5.1.1
 *
 * @package Tribe\Tickets\Plus\Views\V2
 */

namespace Tribe\Tickets\Plus\Views\V2;

/**
 * Class Customizer
 *
 * @since   5.1.1
 *
 * @package Tribe\Tickets\Plus\Views\V2
 */
class Customizer {
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
		if ( $customizer->has_option( $section->ID, 'event_title_color' ) ) {
			// Attendee registration page event title override.
			$css_template .= '
				body.page-tribe-attendee-registration .tribe-common .tribe-tickets__registration-title a,
				body.page-tribe-attendee-registration .event-tickets .tribe-tickets__registration-title a {
					color: <%= global_elements.event_title_color %>;
				}
			';

			$css_template .= '
				body.page-tribe-attendee-registration .tribe-common .tribe-tickets__registration-title a:active,
				body.page-tribe-attendee-registration .tribe-common .tribe-tickets__registration-title a:focus,
				body.page-tribe-attendee-registration .tribe-common .tribe-tickets__registration-title a:hover,
				body.page-tribe-attendee-registration .event-tickets .tribe-tickets__registration-title a:active,
				body.page-tribe-attendee-registration .event-tickets .tribe-tickets__registration-title a:focus,
				body.page-tribe-attendee-registration .event-tickets .tribe-tickets__registration-title a:hover {
					box-shadow: inset 0 -2px 0 0 <%= global_elements.event_title_color %>;
				}
			';
		}

		if ( $customizer->has_option( $section->ID, 'event_date_time_color' ) ) {
			// Attendee registration page event datetime override.
			$css_template .= '
				body.page-tribe-attendee-registration .tribe-common .tribe-tickets__registration-description,
				body.page-tribe-attendee-registration .event-tickets .tribe-tickets__registration-description {
					color: <%= global_elements.event_date_time_color %>;
				}
			';
		}

		if (
			$customizer->has_option( $section->ID, 'background_color_choice' ) &&
			'custom' === $customizer->get_option( [ $section->ID, 'background_color_choice' ] ) &&
			$customizer->has_option( $section->ID, 'background_color' )
		) {
			// Attendee registration page background color override.
			$css_template .= '
				body.page-tribe-attendee-registration:not(.page-tribe-attendee-registration--shortcode) .tribe-tickets__registration {
					background-color: <%= global_elements.background_color %>;
				}
			';
		}

		return $css_template;
	}
}
