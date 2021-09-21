<?php

use Tribe\Tickets\Plus\Meta\Field\Field_Information_Interface;
use Tribe\Tickets\Plus\Meta\Field_Types_Collection;

/**
 * Class Tribe__Tickets_Plus__Meta__Field__Abstract_Field
 *
 * The base class for Attendee Information Meta Fields.
 *
 * @see src/views/meta/{field_identifier}.php
 *         Used at least in these places:
 *          - When filling a RSVP on the event single page. [RSVP]
 *          - When editing an existing RSVP entry on the "My RSVPS" page. [RSVP]
 *
 * @see src/admin-views/meta-fields/{field_identifier}.php
 *         Used at least in these places:
 *          - When the admin is adding/editing the field in the event page on the admin panel. [RSVP/Ticket]
 *
 * @see [EVENT-TICKETS] src/views/registration-js/attendees/fields/{field_identifier}.php
 *         Used at least in these places:
 *          - When filling additional information for a Ticket [Ticket]
 *
 * @version 4.12.1 Implemented Field_Information_Interface.
 */
abstract class Tribe__Tickets_Plus__Meta__Field__Abstract_Field implements Field_Information_Interface {
	const META_PREFIX = '_tribe_tickets_meta_';
	public $id;
	public $label;
	public $slug;
	public $required;
	public $ticket_id;
	public $post;
	public $type;
	public $extra = [];
	public $classes = [];
	public $attributes = [];

	/**
	 * The placeholder value to be used (for fields that support it).
	 *
	 * @since 5.1.1
	 *
	 * @var string
	 */
	public $placeholder;

	/**
	 * The description text to be shown.
	 *
	 * @since 5.2.9
	 *
	 * @var string
	 */
	public $description;

	// @todo Future: Look into why this is not used at all and if it's intention is still needed.
	abstract public function save_value( $attendee_id, $field, $value );

	/**
	 * Constructor
	 *
	 * @see \Tribe__Tickets_Plus__Meta::generate_field
	 */
	public function __construct( $ticket_id, $data = array() ) {
		$this->ticket_id = $ticket_id;
		$this->post      = tribe_events_get_ticket_event( $this->ticket_id );

		$this->initialize_data( $data );
	}

	/**
	 * Given a data set, populate the relevant field object properties
	 *
	 * @since 4.1
	 *
	 * @param array $data Field data
	 */
	public function initialize_data( $data ) {
		if ( ! $data ) {
			return;
		}

		$this->id          = isset( $data['id'] ) ? $data['id'] : null;
		$this->label       = isset( $data['label'] ) ? $data['label'] : null;
		$this->slug        = isset( $data['slug'] ) ? $data['slug'] : null;
		$this->required    = isset( $data['required'] ) ? $data['required'] : null;
		$this->extra       = isset( $data['extra'] ) ? $data['extra'] : [];
		$this->classes     = isset( $data['classes'] ) ? $data['classes'] : [];
		$this->attributes  = isset( $data['attributes'] ) ? $data['attributes'] : [];
		$this->placeholder = isset( $data['placeholder'] ) ? $data['placeholder'] : '';
		$this->description = isset( $data['description'] ) ? $data['description'] : '';

		if ( $this->label && null === $this->slug ) {
			$this->slug = sanitize_title( $this->label );
		}

		// Run config hooks.
		$this->run_config_hooks( $data );
	}

	/**
	 * Handle running the config hooks for the field object.
	 *
	 * @param array $data The initial data provided for the object.
	 */
	public function run_config_hooks( $data = [] ) {
		/**
		 * Allow filtering the field placeholder text.
		 *
		 * @since 5.1.1
		 *
		 * @param string                                           $placeholder The field placeholder text.
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field       The field object.
		 * @param array                                            $data        The initial data provided for the object.
		 */
		$this->placeholder = apply_filters( 'tribe_tickets_plus_meta_field_placeholder', $this->placeholder, $this, $data );

		/**
		 * Allow customizing the field object after it has been set up.
		 *
		 * @since 5.1.1
		 *
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field The field object.
		 * @param array                                            $data  The initial data provided for the object.
		 */
		do_action( 'tribe_tickets_plus_meta_field_after_setup', $this, $data );
	}

	/**
	 * Applies a filter to check if this field is restricted
	 *
	 * @param  int  $attendee_id Which attendee are we dealing with
	 * @return boolean
	 */
	public function is_restricted( $attendee_id = null ) {
		/**
		 * Allow developers to prevent users to update a specific field
		 * @param boolean $is_meta_field_restricted If is allowed or not
		 * @param int     $attendee_id              Which attendee this update will be done to
		 * @param self    $this                     This Field instance
		 */
		$is_meta_field_restricted = (bool) apply_filters( 'event_tickets_plus_is_meta_field_restricted', false, $attendee_id, $this );

		return $is_meta_field_restricted;
	}

	/**
	 * Renders the field on the front end
	 *
	 * @since 4.1
	 *
	 * @param int $attendee_id ID number of the attendee post
	 *
	 * @return string
	 */
	public function render( $attendee_id = null ) {
		$field = $this->get_field_settings();
		$field = $this->sanitize_field_options_for_render( $field );
		$value = $this->get_field_value( $attendee_id );

		return $this->render_field( $field, $value, $attendee_id );
	}

	/**
	 * Constructs a field meta data array for the meta field
	 *
	 * @since 4.1
	 *
	 * @param array $data Field data
	 *
	 * @return array
	 */
	public function build_field_settings( $data ) {
		$id = isset( $data['id'] ) ? $data['id'] : 0;

		// translators: %s: The field ID.
		$label = isset( $data['label'] ) ? $data['label'] : sprinf( __( 'Field %s', 'event-tickets-plus' ), $id );
		$slug  = isset( $data['slug'] ) ? $data['slug'] : sanitize_title( $label );

		$meta = [
			'id'          => $id,
			'type'        => isset( $data['type'] ) ? $data['type'] : 'text',
			'required'    => isset( $data['required'] ) ? $data['required'] : '',
			'label'       => $label,
			'slug'        => $slug,
			'extra'       => isset( $data['extra'] ) ? $data['extra'] : [],
			'classes'     => isset( $data['classes'] ) ? $data['classes'] : [],
			'attributes'  => isset( $data['attributes'] ) ? $data['attributes'] : [],
			'placeholder' => isset( $data['placeholder'] ) ? $data['placeholder'] : '',
			'description' => isset( $data['description'] ) ? $data['description'] : '',
		];

		return $this->build_extra_field_settings( $meta, $data );
	}

	public function build_extra_field_settings( $meta, $data ) {
		return $meta;
	}

	/**
	 * Retrieves the field's settings from post meta
	 *
	 * @since 4.1
	 *
	 * @return array
	 */
	public function get_field_settings() {
		$meta_object    = Tribe__Tickets_Plus__Main::instance()->meta();
		$meta_settings  = (array) $meta_object->get_meta_fields_by_ticket( $this->ticket_id );
		$field_settings = array();

		// loop over the meta field settings attached to the ticket until we find the settings that
		// go with $this specific field
		foreach ( $meta_settings as $setting ) {
			// if the setting label doesn't match $this label, it is a different field. Skip to the next
			// element in the settings array
			if ( $this->label !== $setting->label ) {
				continue;
			}

			// the label matches. Set the field settings that we'll return to the settings from the
			// meta settings stored in the ticket meta
			$field_settings = $setting;
			break;
		}

		/**
		 * Filters the field settings for the instantiated field object
		 *
		 * @var array of field settings
		 * @var Tribe__Tickets_Plus__Meta__Field__Abstract_Field instance
		 */
		$field_settings = apply_filters( 'event_tickets_plus_field_settings', $field_settings, $this );

		return $field_settings;
	}

	/**
	 * Retrieves the value set on the given attendee ticket for the field
	 *
	 * @since 4.1
	 *
	 * @param int $attendee_id ID number of attendee post
	 *
	 * @return array
	 */
	public function get_field_value( $attendee_id ) {
		if ( ! $attendee_id ) {
			return null;
		}

		/**
		 * Allow filtering of the value before it is retrieved from the database to allow a total override.
		 *
		 * @since 5.1.0
		 *
		 * @param null|string                                       $value       The value (default is null).
		 * @param int                                               $attendee_id The attendee ID.
		 * @param \Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field       The field object.
		 */
		$value = apply_filters( 'tribe_tickets_plus_meta_field_pre_value', null, $attendee_id, $this );

		// Check if value was overridden and return that.
		if ( null !== $value ) {
			return $value;
		}

		$value  = null;
		$values = (array) get_post_meta( $attendee_id, Tribe__Tickets_Plus__Meta::META_KEY, true );

		if (
			'checkbox' === $this->type
			|| 'radio' === $this->type
		) {
			$value              = [];
			$hashed_options_map = $this->get_hashed_options_map();

			// Account for Checkboxes and Radios that had their keys md5 hashed (since v4.10.2)
			foreach ( $hashed_options_map as $option_hash => $option_value ) {
				if ( array_key_exists( $option_hash, $values ) ) {
					$value[] = $option_value;
				}
			}
		}

		if ( 'checkbox' === $this->type ) {
			// Process multiple strings into Checkbox array (having values like `something_a = a + something_b = b` that would check 2 boxes for the "Something" checkboxes option)
			foreach ( $values as $v_key => $v_value ) {
				if ( $v_key === $this->slug . '_' . sanitize_title( $v_value ) ) {
					$value[] = $v_value;
				}
			}
		} elseif ( 'radio' === $this->type ) {

			if ( isset( $values[ $this->slug ] ) ) {
				$value = $values[ $this->slug ];
			} else {
				$value  = null;
			}

		} else {
			if ( isset( $values[ $this->slug ] ) ) {
				$value = $values[ $this->slug ];
			}
		}

		return $value;
	}

	/**
	 * Create a lookup map to use when rendering front-end input names as well as saving submitted data, allowing us
	 * to find which submitted/hashed field option should be assigned the value.
	 *
	 * Used for Checkbox and Radio inputs in /wp-content/plugins/event-tickets-plus/src/views/meta/...
	 *
	 * @since 4.10.7
	 *
	 * @return array Key is the hash. Value is the text displayed to user. Key and value get saved (serialized) to
	 *               post_meta so need to stay in this format for backwards compatibility (since 4.10.2 on Checkboxes
	 *               and Radios).
	 */
	public function get_hashed_options_map() {
		$map = [];

		if ( ! empty( $this->extra['options'] ) ) {
			foreach ( $this->extra['options'] as $option ) {
				$hash = esc_attr(
					$this->slug
					. '_'
					. md5( sanitize_title( $option ) )
				);

				$map[ $hash ] = wp_kses_post( $option );
			}
		}

		return $map;
	}

	/**
	 * Returns a version of checkbox, radio, and dropdown fields with any blank/empty options
	 * removed. All other fields are ignored/unaltered by this.
	 *
	 * @since 4.5.5
	 *
	 * @param object $field The meta field being rendered.
	 *
	 * @return object The same meta field with its "options" cleaned of any empty values.
	 */
	public function sanitize_field_options_for_render( $field ) {
		if ( ! isset( $field->extra['options'] ) || ! is_array( $field->extra['options'] ) ) {
			return $field;
		}

		$field->extra['options'] = array_filter( $field->extra['options'] );
		$field->extra['options'] = array_values( $field->extra['options'] );

		return $field;
	}

	/**
	 * Renders the field as it would be displayed on the front end
	 *
	 * @since 4.1
	 *
	 * @param array $field Field settings
	 * @param string|int|array $value Value of the field
	 *
	 * @return string
	 */
	public function render_field( $field, $value = null, $attendee_id = null ) {
		ob_start();

		$template = sanitize_file_name( "{$field->type}.php" );
		$required = isset( $field->required ) && tribe_is_truthy( $field->required ) ? true : false;

		$field = (array) $field;

		include Tribe__Tickets_Plus__Main::instance()->get_template_hierarchy( "meta/{$template}" );

		return ob_get_clean();
	}

	/**
	 * Renders the field settings in the dashboard
	 *
	 * @since 4.1
	 *
	 * @since 5.2.2 Use admin view to return the field.
	 *
	 * @param bool $open True if the field should be open.
	 *
	 * @return string
	 */
	public function render_admin_field( $open = false ) {
		$tickets_plus = Tribe__Tickets_Plus__Main::instance();
		$name         = $tickets_plus->plugin_path . 'src/admin-views/meta-fields/' . sanitize_file_name( $this->type ) . '.php';

		if ( ! file_exists( $name ) ) {
			return '';
		}

		$data                     = (array) $this;
		$ticket_specific_settings = $this->get_field_settings();
		$ticket_specific_settings = $this->sanitize_field_options_for_render( $ticket_specific_settings );
		$data                     = array_merge( $data, (array) $ticket_specific_settings );

		// Alias $this to $field for usage in templates.
		$field = $this;
		$label = ! empty( $this->label ) ? $this->label : '';
		$placeholder = ! empty( $this->get_placeholder() ) ? $this->get_placeholder() : '';
		$description = ! empty( $this->get_description() ) ? $this->get_description() : '';

		/** @var \Tribe__Tickets_Plus__Admin__Views $view */
		$template = tribe( 'tickets-plus.admin.views' );

		$args = [
			'type'      => $this->type,
			'type_name' => tribe( Field_Types_Collection::class )->get_name_by_id( $this->type ),
			'field'     => $field,
			'field_id'  => wp_rand(),
			'label'     => $label,
			'required'  => ! empty( $this->required ) ? $this->required : '',
			'slug'      => ! empty( $this->slug ) ? $this->slug : sanitize_title( $label ),
			'extra'     => $this->extra,
			'open'      => $open,
			'placeholder' => $placeholder,
			'description' => $description,
		];

		$template->add_template_globals( $args );

		return $template->template( 'meta-fields/_field', $args, false );
	}

	/**
	 * Check if the field is required.
	 *
	 * @since 5.1.0
	 *
	 * @return boolean True if it's required.
	 */
	public function is_required() {
		return isset( $this->required ) && 'on' === $this->required;
	}

	/**
	 * Get field CSS classes.
	 * This is for the V2 of the fields.
	 *
	 * @since 5.1.0
	 *
	 * @return array The array containing the field CSS classes.
	 */
	public function get_css_classes() {
		$classes = [
			'tribe-common-b1',
			'tribe-common-b2--min-medium',
			'tribe-tickets__form-field',
			'tribe-tickets__form-field--' . $this->type,
			'tribe-tickets__form-field--required' => $this->is_required(),
		];

		return array_merge( $classes, $this->classes );
	}

	/**
	 * Get field HTML attributes.
	 * This is for the V2 of the fields.
	 *
	 * @since 5.1.0
	 *
	 * @return array The array containing the field HTML attributes.
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Get the placeholder value for field.
	 *
	 * @since 5.1.1
	 *
	 * @return string
	 */
	public function get_placeholder() {
		return $this->placeholder;
	}

	/**
	 * Get the description value for field.
	 *
	 * @since 5.2.9
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the formatted value.
	 *
	 * @since 5.2.0
	 *
	 * @param string|mixed $value The current value.
	 *
	 * @return string|mixed The formatted value.
	 */
	public function get_formatted_value( $value ) {
		return $value;
	}

	/**
	 * Check if the field has placeholder enabled.
	 *
	 * @since 5.2.5
	 *
	 * @return bool
	 */
	public function has_placeholder() {
		$placeholder_types = [
			'text',
			'telephone',
			'url',
			'email',
		];

		/**
		 * Allow to filter the supported Placeholders AR fields.
		 *
		 * @param array                                            $placeholder_types List of types that support Placeholder.
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field             The field object.
		 *
		 * @since 5.2.5
		 */
		$placeholder_types = (array) apply_filters( 'event_tickets_plus_placeholder_enabled_ar_fields', $placeholder_types, $this );

		return in_array( $this->type, $placeholder_types, true );
	}
}
