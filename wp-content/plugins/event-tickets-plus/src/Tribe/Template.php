<?php
/**
 * Allow including of Event Tickets Plus Template.
 *
 * @since 5.0.0
 */
class Tribe__Tickets_Plus__Template extends Tribe__Template {
	/**
	 * Building of the Class template configuration.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		$this->set_template_origin( Tribe__Tickets_Plus__Main::instance() );

		$this->set_template_folder( 'src/views' );

		// Configures this templating class to extract variables.
		$this->set_template_context_extract( true );

		// Uses the public folders.
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Return the attributes of the template.
	 *
	 * @since 5.0.0
	 *
	 * @param array $default_attributes The default attributes.
	 *
	 * @return array
	 */
	public function attributes( $default_attributes = [] ) {
		return wp_parse_args(
			$this->get( 'attributes', [] ),
			$default_attributes
		);
	}

	/**
	 * Return a specific attribute.
	 *
	 * @since 5.0.0
	 *
	 * @param  mixed $index The index.
	 * @param  mixed $default The default.
	 * @return mixed
	 */
	public function attr( $index, $default = null ) {
		$attribute = $this->get( array_merge( [ 'attributes' ], (array) $index ), $default );

		return $attribute;
	}
}
