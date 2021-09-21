<?php

class Tribe__Tickets_Plus__Meta {

	const ENABLE_META_KEY = '_tribe_tickets_meta_enabled';

	/**
	 * This meta key is used for 3 slightly different purposes depending on post_type
	 *
	 * product - the meta fields configuration for ticket
	 * shop_order - the meta values at the time of the order, not updated on future edits
	 * tribe_wooticket - the current meta values for each attendee
	 */
	const META_KEY = '_tribe_tickets_meta';

	private $path;
	private $meta_fieldset;
	private $rsvp_meta;
	private $render;

	/**
	 * @var Tribe__Tickets_Plus__Meta__Storage
	 */
	protected $storage;

	/**
	 * @var Tribe__Tickets_Plus__Meta__Export
	 */
	protected $export;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 *
	 */
	public static function instance() {
		return tribe( 'tickets-plus.meta' );
	}

	/**
	 * Tribe__Tickets_Plus__Meta constructor.
	 *
	 * @param string                                   $path
	 * @param Tribe__Tickets_Plus__Meta__Storage|null $storage An instance of the meta storage handler.
	 */
	public function __construct( $path = null, Tribe__Tickets_Plus__Meta__Storage $storage = null ) {
		$this->storage = $storage ? $storage : new Tribe__Tickets_Plus__Meta__Storage();

		if ( ! is_null( $path ) ) {
			$this->path = trailingslashit( $path );
		}

		/*
		 * Event Tickets compatibility filters.
		 *
		 * Set the tribe_tickets_has_meta_enabled filter to priority 9 for backwards compatible filter usage.
		 */
		add_filter( 'tribe_tickets_has_meta_enabled', [ $this, 'filter_ticket_has_meta_enabled' ], 9, 2 );
		add_filter( 'tribe_tickets_data_ticket_ids_have_meta_fields', [ $this, 'filter_data_ticket_ids_have_meta_fields' ], 10, 2 );

		add_action( 'event_tickets_after_save_ticket', [ $this, 'save_meta' ], 10, 3 );

		add_action( 'event_tickets_ticket_list_after_ticket_name', [ $this, 'maybe_render_custom_meta_icon' ] );
		add_action( 'event_tickets_ticket_list_after_ticket_name', [ $this, 'maybe_render_attendee_registration_fields_list' ] );

		add_action( 'tribe_events_tickets_metabox_edit_accordion_content', [ $this, 'accordion_content' ], 10, 2 );

		/* Ajax filters and actions */
		add_filter( 'tribe_events_tickets_metabox_edit_attendee', [ $this, 'ajax_attendee_meta' ], 10, 2 );
		add_action( 'wp_ajax_tribe-tickets-info-render-field', [ $this, 'ajax_render_fields' ] );
		add_action( 'wp_ajax_tribe-tickets-load-saved-fields', [ $this, 'ajax_render_saved_fields' ] );
		add_action( 'woocommerce_remove_cart_item', [ $this, 'clear_storage_on_remove_cart_item' ], 10, 2 );

		// Check if the attendee registration cart has required meta.
		add_filter( 'tribe_tickets_attendee_registration_has_required_meta', [ $this, 'filter_cart_has_required_meta' ], 20, 2 );

		// Commerce hooks.
		add_filter( 'tribe_tickets_commerce_cart_get_data', [ $this, 'get_cart_data' ], 10, 3 );
		add_filter( 'tribe_tickets_commerce_cart_get_ticket_meta', [ $this, 'get_ticket_meta' ], 10, 2 );
		add_action( 'tribe_tickets_commerce_cart_update_ticket_meta', [ $this, 'update_ticket_meta' ], 10, 5 );

		$this->meta_fieldset();
		$this->render();
		$this->rsvp_meta();
		$this->export();
	}

	public function meta_fieldset() {
		if ( ! $this->meta_fieldset ) {
			$this->meta_fieldset = new Tribe__Tickets_Plus__Meta__Fieldset;
		}

		return $this->meta_fieldset;
	}

	/**
	 * Object accessor method for the RSVP meta
	 *
	 * @return Tribe__Tickets_Plus__Meta__RSVP
	 */
	public function rsvp_meta() {
		if ( ! $this->rsvp_meta ) {
			$this->rsvp_meta = new Tribe__Tickets_Plus__Meta__RSVP;
			$this->rsvp_meta->hook();
		}

		return $this->rsvp_meta;
	}

	public function render() {
		if ( ! $this->render ) {
			$this->render = new Tribe__Tickets_Plus__Meta__Render;
		}

		return $this->render;
	}

	/**
	 * @return Tribe__Tickets_Plus__Meta__Export
	 */
	public function export() {
		if ( ! $this->export ) {
			$this->export = new Tribe__Tickets_Plus__Meta__Export;
		}

		return $this->export;
	}

	public function register_resources() {
		_deprecated_function( __METHOD__, '4.6', 'Tribe__Tickets_Plus__Assets::admin_enqueue_scripts' );
	}

	public function wp_enqueue_scripts() {
		_deprecated_function( __METHOD__, '4.6', 'Tribe__Tickets_Plus__Assets::admin_enqueue_scripts' );
	}

	/**
	 * Get the list of meta field objects for the ticket.
	 *
	 * @param int $ticket_id The ticket ID.
	 *
	 * @return Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] The list of meta field objects for the ticket.
	 */
	public function get_meta_fields_by_ticket( $ticket_id ) {
		$fields = [];

		if ( empty( $ticket_id ) ) {
			return $fields;
		}

		$field_meta = get_post_meta( $ticket_id, self::META_KEY, true );

		if ( empty( $field_meta ) || ! is_array( $field_meta ) ) {
			$field_meta = [];
		}

		$fields = [];

		foreach ( $field_meta as $field ) {
			if ( empty( $field['type'] ) ) {
				continue;
			}

			$field_object = $this->generate_field( $ticket_id, $field['type'], $field );

			if ( ! $field_object ) {
				continue;
			}

			$fields[] = $field_object;
		}

		/**
		 * Allow filtering the list of meta fields for a ticket.
		 *
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] $fields    List of meta field objects for the ticket.
		 * @param int                                                $ticket_id The ticket ID.
		 */
		return apply_filters( 'event_tickets_plus_meta_fields_by_ticket', $fields, $ticket_id );
	}

	/**
	 * Get the list of meta field objects for tickets on the post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] The list of meta field objects for tickets on the post.
	 */
	public function get_meta_fields_by_event( $post_id ) {
		$fields = [];

		$tickets = Tribe__Tickets__Tickets::get_event_tickets( $post_id );

		foreach ( $tickets as $ticket ) {
			$meta_fields = $this->get_meta_fields_by_ticket( $ticket->ID );

			if ( is_array( $meta_fields ) && ! empty( $meta_fields ) ) {
				$fields[] = $meta_fields;
			}
		}

		// Merge all of the field arrays together.
		if ( $fields ) {
			$fields = array_merge( ...$fields );
		}

		/**
		 * Allow filtering the list of meta fields for tickets on the post.
		 *
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] $fields  List of meta field objects for tickets on the post.
		 * @param int                                                $post_id The post ID.
		 */
		return apply_filters( 'tribe_tickets_plus_get_meta_fields_by_event', $fields, $post_id );
	}

	/**
	 * Metabox to output the Custom Meta fields
	 *
	 * @since 4.1
	 *
	 * @deprecated 4.6
	 */
	public function metabox( $unused_post_id, $unused_ticket_id ) {
		_deprecated_function( __METHOD__, '4.6', 'Tribe__Tickets_Plus__Meta::accordion_content' );

		$this->accordion_content( $unused_post_id, $unused_ticket_id );
	}

	/**
	 * Function to output accordion button & content to edit ticket panel
	 *
	 * @since 4.6
	 *
	 * @param int $unused_post_id ID of parent "event" post.
	 * @param int $ticket_id ID of ticket post.
	 */
	public function accordion_content( $unused_post_id, $ticket_id = null ) {
		$is_admin = tribe_is_truthy( tribe_get_request_var( 'is_admin', is_admin() ) );

		if ( ! $is_admin ) {
			return;
		}

		$args = [
			'ticket_id'     => $ticket_id,
			'enable_meta'   => $this->ticket_has_meta( $ticket_id ),
			'active_meta'   => $this->get_meta_fields_by_ticket( $ticket_id ),
			'templates'     => $this->meta_fieldset()->get_fieldsets(),
			'fieldset_form' => false,
			'meta_object'   => $this,
		];

		/** @var Tribe__Tickets_Plus__Admin__Views $plus_admin_views */
		$template = tribe( 'tickets-plus.admin.views' );

		$template->add_template_globals( $args );

		$template->template( 'attendee-meta', $args );
	}

	/**
	 * Function to output meta content to edit ticket panel
	 *
	 * @since 4.10
	 *
	 * @param int $unused_post_id ID of parent "event" post
	 * @param int $ticket_id ID of ticket post
	 */
	public function meta_content( $ticket_id = null ) {
		$is_admin = tribe_is_truthy( tribe_get_request_var( 'is_admin', is_admin() ) );

		if ( ! $is_admin ) {
			return;
		}

		/** @var Tribe__Tickets_Plus__Meta $meta_object */
		$meta_object = tribe( 'tickets-plus.meta' );

		$template_args = [
			'ticket_id'   => $ticket_id,
			'enable_meta' => $this->ticket_has_meta( $ticket_id ),
			'active_meta' => $this->get_meta_fields_by_ticket( $ticket_id ),
			'templates'   => $this->meta_fieldset()->get_fieldsets(),
			'meta_object' => $meta_object,
		];

		/** @var \Tribe__Tickets_Plus__Admin__Views $template */
		$template = tribe( 'tickets-plus.admin.views' );

		// Add the rendering attributes into global context.
		$template->add_template_globals( $template_args );

		$template->template( 'meta-content' );
	}

	/**
	 * Gets just the meta fields for insertion via ajax
	 *
	 * @param int $unused_post_id Post ID of ticket parent.
	 * @param int $ticket_id      Ticket post ID.
	 *
	 * @return string The custom field(s) HTML.
	 */
	public function ajax_attendee_meta( $unused_post_id, $ticket_id ) {
		$output      = '';
		$active_meta = $this->get_meta_fields_by_ticket( $ticket_id );
		$meta_object = Tribe__Tickets_Plus__Main::instance()->meta();

		foreach ( $active_meta as $meta ) {
			$field = $meta_object->generate_field( $ticket_id, $meta->type, (array) $meta );
			// Outputs HTML input field - no escaping.
			$output .= $field->render_admin_field();
		}

		return $output;
	}

	/**
	 * Returns whether or not custom meta is enabled for the given ticket
	 *
	 * @deprecated 5.1.0 Use `$meta->ticket_has_meta( $ticket_id )` instead.
	 *
	 * @param int $ticket_id ID of ticket post
	 *
	 * @return bool Whether a ticket has meta enabeld.
	 */
	public function meta_enabled( $ticket_id ) {
		return $this->ticket_has_meta( $ticket_id );
	}

	/**
	 * Saves meta configuration on a ticket
	 *
	 * @since 4.1
	 *
	 * @param int                           $unused_post_id ID of parent "event" post
	 * @param Tribe__Tickets__Ticket_Object $ticket         Ticket object
	 * @param array                         $data           Post data that was submitted
	 */
	public function save_meta( $unused_post_id, $ticket, $data ) {
		// Bail if we are not saving ticket input data.
		if ( ! isset( $data['tribe-tickets-input'] ) ) {
			return;
		}

		$data['tribe-tickets-input'] = array_filter( $data['tribe-tickets-input'] );

		if ( empty( $data['tribe-tickets-input'] ) ) {
			$meta = array();
		} else {
			$meta = $this->build_field_array( $ticket->ID, $data );
		}

		// this is for the meta fields configuration associated with the "product" post type
		update_post_meta( $ticket->ID, self::META_KEY, $meta );

		if ( ! $meta ) {
			// no meta? Do not enable meta on the ticket.
			delete_post_meta( $ticket->ID, self::ENABLE_META_KEY );

			return;
		}

		// if there is some meta enable meta for the ticket
		update_post_meta( $ticket->ID, self::ENABLE_META_KEY, 'yes' );

		// Save templates too
		if ( isset( $data['tribe-tickets-save-fieldset'] ) ) {
			$fieldset = wp_insert_post( array(
				'post_type'   => Tribe__Tickets_Plus__Meta__Fieldset::POSTTYPE,
				'post_title'  => empty( $data['tribe-tickets-saved-fieldset-name'] ) ? null : $data['tribe-tickets-saved-fieldset-name'],
				'post_status' => 'publish',
			) );

			// This is for the meta fields template
			update_post_meta( $fieldset, Tribe__Tickets_Plus__Meta__Fieldset::META_KEY, $meta );
		}

	}

	/**
	 * Builds an array of fields
	 *
	 * @param int $ticket_id ID of ticket post
	 * @param array $data field data
	 * @return array array of fields
	 */
	public function build_field_array( $ticket_id, $data ) {
		if ( empty( $data['tribe-tickets-input'] ) ) {
			return array();
		}

		$meta = array();

		foreach ( (array) $data['tribe-tickets-input'] as $field_id => $field ) {
			if ( empty( $field ) || ! is_array( $field ) ) {
				continue;
			}

			$field_object = $this->generate_field( $ticket_id, $field['type'], $field );

			if ( ! $field_object ) {
				continue;
			}

			$meta[] = $field_object->build_field_settings( $field );
		}

		return $meta;
	}

	/**
	 * Outputs ticket custom meta admin fields for an Ajax request
	 */
	public function ajax_render_fields() {

		$data = null;

		if ( empty( $_POST['type'] ) ) {
			wp_send_json_error( '' );
		}

		$field = $this->generate_field( null, $_POST['type'] );

		if ( $field ) {
			$data = $field->render_admin_field( true );
		}

		if ( empty( $data ) ) {
			wp_send_json_error( $data );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Outputs ticket custom meta admin fields loaded from a group of pre-saved fields for an Ajax request
	 */
	public function ajax_render_saved_fields() {

		$data = null;

		if ( empty( $_POST['fieldset'] ) ) {
			wp_send_json_error( '' );
		}

		$fieldset = get_post( $_POST['fieldset'] );

		if ( ! $fieldset ) {
			wp_send_json_error( '' );
		}

		$template = get_post_meta( $fieldset->ID, Tribe__Tickets_Plus__Meta__Fieldset::META_KEY, true );

		if ( ! $template ) {
			wp_send_json_error( '' );
		}

		foreach ( (array) $template as $field ) {
			$field_object = $this->generate_field( null, $field['type'], $field );

			if ( ! $field_object ) {
				continue;
			}

			$data .= $field_object->render_admin_field();
		}

		if ( empty( $data ) ) {
			wp_send_json_error( $data );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Generates a field object
	 *
	 * @since 4.1 Method introduced.
	 * @since 5.1.0 Added support for filtering the field class.
	 *
	 * @param int    $ticket_id ID of ticket post the field is attached to.
	 * @param string $type      Type of field being generated.
	 * @param array  $data      Field settings for the field.
	 *
	 * @return Tribe__Tickets_Plus__Meta__Field__Abstract_Field child class
	 */
	public function generate_field( $ticket_id, $type, $data = array() ) {
		$class = 'Tribe__Tickets_Plus__Meta__Field__' . ucwords( $type );

		/**
		 * Allow filtering the field class used so custom field classes can be supported.
		 *
		 * @since 5.1.0
		 *
		 * @param string $class Class name to use for the field.
		 * @param string $type  Type of field being generated.
		 * @param array  $data  Field settings for the field.
		 */
		$class = apply_filters( 'tribe_tickets_plus_meta_field_class', $class, $type, $data );

		if ( ! class_exists( $class ) ) {
			return null;
		}

		return new $class( $ticket_id, $data );
	}

	/**
	 * Generates a field objects for a list of fields.
	 *
	 * @since 5.1.0
	 *
	 * @param int   $ticket_id ID of ticket post the field is attached to.
	 * @param array $fields    List of field configurations.
	 *
	 * @return Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] List of field objects.
	 */
	public function generate_fields( $ticket_id, $fields ) {
		$field_objects = [];

		foreach ( $fields as $field ) {
			// Check if we already have a field object.
			if ( $field instanceof Tribe__Tickets_Plus__Meta__Field__Abstract_Field ) {
				$field_objects[] = $field;

				continue;
			}

			// Check if we have the required type.
			if ( empty( $field['type'] ) ) {
				continue;
			}

			// Set up the object.
			$field_object = $this->generate_field( $ticket_id, $field['type'], $field );

			// Skip if the field object class was not found / set up properly.
			if ( ! $field_object instanceof Tribe__Tickets_Plus__Meta__Field__Abstract_Field ) {
				continue;
			}

			$field_objects[] = $field_object;
		}

		return $field_objects;
	}

	/**
	 * Retrieves custom meta data from the cookie
	 *
	 * @since 4.1
	 *
	 * @param int  $product_id    Commerce provider product ID.
	 * @param bool $include_empty Whether to include empty values.
	 *
	 * @return array Custom meta data from the cookie.
	 */
	public function get_meta_cookie_data( $product_id, $include_empty = false ) {
		$meta_data = $this->storage->get_meta_data_for( $product_id );

		if ( ! $include_empty ) {
			$meta_data = $this->storage->remove_empty_values_recursive( $meta_data );
		}

		return $meta_data;
	}

	/**
	 * Builds the meta data structure for storage in orders.
	 *
	 * @since 4.1
	 *
	 * @param array $product_ids   Collection of Product IDs in an order.
	 * @param bool  $include_empty Whether to include empty values.
	 *
	 * @return array The meta data for an order.
	 */
	public function build_order_meta( $product_ids, $include_empty = false ) {
		if ( ! $product_ids ) {
			return array();
		}

		$meta_object = Tribe__Tickets_Plus__Main::instance()->meta();
		$meta        = [];

		foreach ( $product_ids as $product_id ) {
			$data = $meta_object->get_meta_cookie_data( $product_id, $include_empty );

			if ( ! $data ) {
				continue;
			}

			foreach ( $data as $id => $the_meta ) {
				if ( ! isset( $meta[ $id ] ) ) {
					$meta[ $id ] = array();
				}

				foreach ( $the_meta as $mid => $metadata ) {
					$metadata = array_filter( $metadata );

					$the_meta[ $mid ] = $metadata;
				}

				$meta[ $id ] = array_merge_recursive( $meta[ $id ], $the_meta );
			}
		}

		return $meta;
	}

	/**
	 * Clears the custom meta data stored in the cookie
	 *
	 * @since 4.1
	 *
	 * @param int $product_id Commerce product ID
	 */
	public function clear_meta_cookie_data( $product_id ) {
		$this->storage->delete_meta_data_for( $product_id );
	}

	/**
	 * If the given ticket has attendee meta, render an icon to indicate that
	 *
	 * @param Tribe__Tickets__Ticket_Object $ticket
	 */
	public function maybe_render_custom_meta_icon( $ticket ) {
		if ( ! is_admin() ) {
			return;
		}

		$meta = $this->get_meta_fields_by_ticket( $ticket->ID );
		if ( ! $meta ) {
			return;
		}
		?>
		<span title="<?php esc_html_e( 'This ticket has custom Attendee Information fields', 'event-tickets-plus' ); ?>" class="dashicons dashicons-id-alt"></span>
		<?php
	}

	/**
	 * If the given ticket has attendee meta, render a list of the fields.
	 *
	 * @since 5.2.5
	 *
	 * @param Tribe__Tickets__Ticket_Object $ticket The ticket object.
	 */
	public function maybe_render_attendee_registration_fields_list( $ticket ) {
		if ( ! is_admin() ) {
			return;
		}
		/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
		$iac            = tribe( 'tickets-plus.attendee-registration.iac' );
		$iac_for_ticket = $iac->get_iac_setting_for_ticket( $ticket->ID );
		$iac_enabled    = $iac_for_ticket === $iac::ALLOWED_KEY || $iac_for_ticket === $iac::REQUIRED_KEY;
		$meta           = $this->get_meta_fields_by_ticket( $ticket->ID );

		if ( empty( $meta ) && empty( $iac_enabled ) ) {
			return;
		}

		$fields = [];

		if ( ! empty( $iac_enabled ) ) {
			$fields[] = __( 'Name', 'event-tickets-plus' );
			$fields[] = __( 'Email', 'event-tickets-plus' );
		}

		foreach ( $meta as $field ) {
			if ( empty( $field->type ) ) {
				continue;
			}

			if ( empty( $field->label ) ) {
				$fields[] = '(' . $field->get_name() . ')';
				continue;
			}

			$fields[] = $field->label;
		}

		?>

		<div class="tribe-tickets__tickets-editor-ticket-name-attendee-registration-fields">
			<?php echo esc_html( implode( ', ', $fields ) ); ?>
		</div>
		<?php
	}
	/**
	 * Injects fieldsets into JSON data during ticket add ajax output
	 *
	 * @param array $return Data array to be output in the ajax response for ticket adds
	 * @param int $post_id ID of parent "event" post
	 * @return array $return output Data array with added fieldsets
	 */
	public function inject_fieldsets_in_json( $return, $unused_post_id ) {
		$return['fieldsets'] = $this->meta_fieldset()->get_fieldsets();
		return $return;
	}

	/**
	 * Checks if any of the cart tickets has meta
	 *
	 * @since 4.10.1
	 *
	 * @param array $cart_tickets
	 *
	 * @return bool
	 */
	public function cart_has_meta( $cart_tickets ) {
		// Bail if we don't receive an array
		if ( ! is_array( $cart_tickets ) ) {
			return false;
		}

		// Bail if we receive an empty array.
		if ( empty( $cart_tickets ) ) {
			return false;
		}

		foreach ( $cart_tickets as $ticket_id => $quantity ) {
			if ( $this->ticket_has_meta( $ticket_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle filtering `tribe_tickets_attendee_registration_has_required_meta` to determine whether any of the cart tickets has required meta.
	 *
	 * @since 4.11.0
	 *
	 * @param boolean $cart_has_required_meta Whether the cart has required meta.
	 * @param array   $tickets_in_cart        The array containing the cart elements. Format array( 'ticket_id' => 'quantity' ).
	 *
	 * @return bool Whether any of the cart tickets has required meta.
	 */
	public function filter_cart_has_required_meta( $cart_has_required_meta, $tickets_in_cart ) {
		return $this->cart_has_meta( $tickets_in_cart );
	}

	/**
	 * Checks whether any of the cart tickets has required meta.
	 *
	 * @since 4.9
	 *
	 * @param array $cart_tickets The array containing the cart elements. Format array( 'ticket_id' => 'quantity' ).
	 *
	 * @return bool Whether any of the cart tickets has required meta.
	 */
	public function cart_has_required_meta( $cart_tickets ) {
		// Bail if we don't receive an array
		if ( ! is_array( $cart_tickets ) ) {
			return false;
		}

		// Bail if we receive an empty array
		if ( empty( $cart_tickets ) ) {
			return false;
		}

		foreach ( $cart_tickets as $ticket_id => $quantity ) {
			if ( $this->ticket_has_required_meta( $ticket_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the ticket object has ticket has meta enabled.
	 *
	 * @since 5.1.0
	 *
	 * @param bool $has_meta  Whether the ticket has meta enabled.
	 * @param int  $ticket_id The ticket ID.
	 *
	 * @return bool Whether the ticket has meta enabled.
	 */
	public function filter_ticket_has_meta_enabled( $has_meta, $ticket_id ) {
		// Allow other filters to override first.
		if ( $has_meta ) {
			return $has_meta;
		}

		return $this->ticket_has_meta( $ticket_id );
	}

	/**
	 * Determine whether any of the ticket IDs have meta enabled and have fields.
	 *
	 * @since 5.1.0
	 *
	 * @param bool  $tickets_have_meta_fields Whether the ticket IDs have meta fields.
	 * @param array $ticket_ids               The ticket IDs.
	 *
	 * @return bool Whether any the tickets have meta enabled and have fields.
	 */
	public function filter_data_ticket_ids_have_meta_fields( $tickets_have_meta_fields, $ticket_ids ) {
		foreach ( $ticket_ids as $ticket_id ) {
			$meta_enabled = $this->ticket_has_meta( $ticket_id );

			if ( ! $meta_enabled ) {
				continue;
			}

			$meta_fields = $this->get_meta_fields_by_ticket( $ticket_id );

			if ( empty( $meta_fields ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Determine whether the ticket has ticket has meta enabled.
	 *
	 * @since 4.10.1
	 *
	 * @param int $ticket_id The ticket ID.
	 *
	 * @return bool Whether the ticket has meta enabled.
	 */
	public function ticket_has_meta( $ticket_id ) {
		$has_meta = get_post_meta( $ticket_id, self::ENABLE_META_KEY, true );
		$has_meta = ! empty( $has_meta ) && tribe_is_truthy( $has_meta );

		/**
		 * Filters whether the ticket has meta or not.
		 *
		 * @since 5.1.0
		 *
		 * @param bool $has_meta  Whether the ticket has meta enabled.
		 * @param int  $ticket_id The ticket ID.
		 */
		return (bool) apply_filters( 'tribe_tickets_plus_ticket_has_meta_enabled', $has_meta, $ticket_id );
	}

	/**
	 * See if a ticket has required meta
	 *
	 * @since 4.9
	 *
	 * @param int $ticket_id
	 * @return bool
	 */
	public function ticket_has_required_meta( $ticket_id ) {

		// Return false if ticket does not have meta
		if ( ! $this->ticket_has_meta( $ticket_id ) ) {
			return false;
		}

		return $this->meta_has_required_fields( $ticket_id );

	}

	/**
	 * Checks if a ticket has required meta.
	 *
	 * @since 4.9
	 *
	 * @param int $ticket_id The ticket ID.
	 *
	 * @return bool Whether the ticket has required meta.
	 */
	public function meta_has_required_fields( $ticket_id ) {
		// Get the meta fields for this ticket.
		$ticket_meta = $this->get_meta_fields_by_ticket( $ticket_id );

		foreach ( $ticket_meta as $meta ) {
			// If any meta is required, return true right away.
			if ( 'on' === $meta->required ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the meta field is required by slug, for a specific ticket.
	 *
	 * @since 4.9
	 *
	 * @param int    $ticket_id The ticket ID.
	 * @param string $slug      Meta field slug.
	 *
	 * @return bool Whether the ticket meta field has required meta.
	 */
	public function meta_is_required( $ticket_id, $slug ) {
		// Get the meta fields for this ticket.
		$ticket_meta = $this->get_meta_fields_by_ticket( $ticket_id );

		foreach ( $ticket_meta as $meta ) {
			// Skip if the slug is different from the one we want to check.
			if ( $slug !== $meta->slug ) {
				continue;
			}

			// Get the value and get out of the loop.
			return ( 'on' === $meta->required );
		}

		return false;
	}

	/************************
	 *                      *
	 *  Deprecated Methods  *
	 *                      *
	 ************************/
	// @codingStandardsIgnoreStart

	/**
	 * Injects additional elements into the main ticket admin panel "header"
	 *
	 * @deprecated 4.6.2
	 * @since 4.6
	 *
	 * @param int $post_id ID of parent "event" post
	 */
	public function tickets_post_capacity( $post_id ) {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets.admin.views' )->template( 'editor/button-view-orders' )" );
		tribe( 'tickets.admin.views' )->template( 'editor/button-view-orders' );
	}

	/**
	 * Injects "New Ticket" button into initial view
	 *
	 * @deprecated 4.6.2
	 * @since 4.6
	 */
	public function tickets_new_ticket_button() {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.admin.views' )->template( 'button-new-ticket' )" );
		tribe( 'tickets-plus.admin.views' )->template( 'editor/button-new-ticket' );
	}

	/**
	 * Injects additional columns into tickets table body
	 *
	 * @deprecated 4.6.2
	 * @since 4.6
	 *
	 * @param $ticket_ID (obj) the ticket object
	 * @param $provider_obj (obj) the ticket provider object
	 */
	public function ticket_table_add_tbody_column( $ticket, $provider_obj ) {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.editor' )->add_column_content_price( \$ticket, \$provider_obj )" );
		tribe( 'tickets-plus.editor' )->add_column_content_price( $ticket, $provider_obj );
	}

	/**
	 * Injects additional columns into tickets table header
	 *
	 * @deprecated 4.6.2
	 * @since 4.6
	 */
	public function ticket_table_add_header_column() {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.admin.views' )->template( 'editor/column-head-price' )" );
		tribe( 'tickets-plus.admin.views' )->template( 'editor/column-head-price' );
	}

	/**
	 * Creates and outputs the capacity table for the ticket settings panel
	 *
	 * @since 4.6
	 * @deprecated 4.6.2
	 *
	 * @param int $post_id ID of parent "event" post
	 *
	 * @return void
	 */
	public function tickets_settings_capacity_table( $post_id ) {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.admin.views' )->template( 'editor/capacity-table' )" );
		tribe( 'tickets-plus.admin.views' )->template( 'editor/capacity-table' );
	}

	/**
	 * Get the total capacity for the event, format it and display.
	 *
	 * @deprecated 4.6.2
	 * @since 4.6
	 *
	 * @param int $post_id ID of parent "event" post
	 *
	 * @return void
	 */
	public function display_tickets_capacity( $post_id ) {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.admin.views' )->template( 'editor/total-capacity' )" );
		tribe( 'tickets-plus.admin.views' )->template( 'editor/total-capacity' );
	}

	/**
	 * Injects additional fields into the event settings form below the capacity table
	 *
	 * @deprecated 4.6.2
	 * @since 4.6
	 *
	 * @param int $post_id - the post id of the parent "event" post
	 *
	 * @return void
	 */
	public function tickets_settings_content( $post_id ) {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.admin.views' )->template( 'editor/price-field' )" );
		tribe( 'tickets-plus.admin.views' )->template( 'editor/settings-content' );
	}

	/**
	 * Allows for the insertion of additional content into the ticket edit form - main section
	 *
	 * @since 4.6
	 * @deprecated 4.6.2
	 *
	 */
	public function tickets_edit_main() {
		_deprecated_function( __METHOD__, '4.6.2', "tribe( 'tickets-plus.admin.views' )->template( 'editor/price-field' )" );
		tribe( 'tickets-plus.admin.views' )->template( 'editor/price-field' );
	}

	// @codingStandardsIgnoreEnd

	/**
	 * Clear the storage allocated by a product if the product is removed from the cart.
	 *
	 * @since 4.7.1
	 *
	 * @param $cart_item_key
	 * @param $cart
	 */
	public function clear_storage_on_remove_cart_item( $cart_item_key = '', $cart = null ) {
		$product_id = null;

		if ( $cart instanceof WC_Cart ) {
			$product    = $cart->cart_contents[ $cart_item_key ];
			$product_id = empty( $product['product_id'] ) ? null : $product['product_id'];
		}

		if ( ! is_null( $product_id ) ) {
			$this->storage->delete_meta_data_for( $product_id );
		}
	}

	/**
	 * Get ticket meta for Attendee Registration.
	 *
	 * @since 4.11.0
	 *
	 * @param array $meta    List of meta for each ticket for Attendee Registration.
	 * @param array $tickets List of tickets with their ID and quantity.
	 *
	 * @return array List of meta for each ticket for Attendee Registration.
	 */
	public function get_ticket_meta( $meta, $tickets ) {
		/** @var Tribe__Tickets_Plus__Meta__Contents $contents */
		$contents = tribe( 'tickets-plus.meta.contents' );

		// Get ticket IDs.
		$tickets_for_meta = wp_list_pluck( $tickets, 'quantity', 'ticket_id' );

		$stored_meta = $contents->get_ticket_stored_meta( $tickets_for_meta );

		foreach ( $tickets as $ticket ) {
			$ticket_id = (int) $ticket['ticket_id'];

			$ticket_meta = isset( $stored_meta[ $ticket_id ] ) ? $stored_meta[ $ticket_id ] : [];

			$meta_to_be_added = [
				'ticket_id' => $ticket_id,
				'provider'  => $ticket['provider'],
				'items'     => [],
			];

			if ( ! is_array( $ticket_meta ) || empty( $ticket_meta[ $ticket_id ] ) ) {
				$meta[] = $meta_to_be_added;

				continue;
			}

			$meta_to_be_added['items'] = array_values( $ticket_meta[ $ticket_id ] );

			$meta[] = $meta_to_be_added;
		}

		return $meta;
	}

	/**
	 * Get cart data for Attendee Registration.
	 *
	 * @since 4.11.0
	 *
	 * @param array $data      Cart response data.
	 * @param array $providers List of cart providers.
	 * @param int   $post_id   Post ID for cart.
	 *
	 * @return array Cart data for Attendee Registration.
	 */
	public function get_cart_data( $data, $providers, $post_id ) {
		$data['is_stored_meta_up_to_date'] = 1;
		$data['attendee_registration_url'] = '';

		if ( empty( $data['tickets'] ) ) {
			return $data;
		}

		/** @var Tribe__Tickets_Plus__Meta__Contents $contents */
		$contents = tribe( 'tickets-plus.meta.contents' );
		$tickets  = $data['tickets'];

		// Get ticket IDs.
		$tickets_for_meta = wp_list_pluck( $tickets, 'quantity', 'ticket_id' );

		$data['is_stored_meta_up_to_date'] = (int) $contents->is_stored_meta_up_to_date( $tickets_for_meta );

		/** @var Tribe__Tickets__Attendee_Registration__Main $attendee_reg */
		$attendee_reg = tribe( 'tickets.attendee_registration' );

		$first_provider = current( $providers );

		$data['attendee_registration_url'] = add_query_arg( 'provider', $first_provider, $attendee_reg->get_url() );

		if ( ! empty( $post_id ) ) {
			$data['attendee_registration_url'] = add_query_arg( 'tribe_tickets_post_id', (int) $post_id, $data['attendee_registration_url'] );
		}

		return $data;
	}

	/**
	 * Update ticket meta from Attendee Registration.
	 *
	 * @since 4.11.0
	 *
	 * @param array   $meta     List of meta for each ticket to be saved for Attendee Registration.
	 * @param array   $tickets  List of tickets with their ID and quantity.
	 * @param string  $provider The cart provider.
	 * @param int     $post_id  Post ID for the cart.
	 * @param boolean $additive Whether to add or replace meta.
	 */
	public function update_ticket_meta( $meta, $tickets, $provider, $post_id, $additive ) {
		$ticket_meta = [];

		if ( $additive ) {
			$ticket_meta = $this->storage->get_meta_data();
		}

		foreach ( $meta as $ticket ) {
			$ticket_id = $ticket['ticket_id'];

			if ( ! isset( $ticket_meta[ $ticket_id ] ) ) {
				$ticket_meta[ $ticket_id ] = [];
			}

			foreach ( $ticket['items'] as $item ) {
				$ticket_meta[ $ticket_id ][] = $item;
			}
		}

		// Maybe set attendee meta cookie and handle saving of meta.
		$this->storage->maybe_set_attendee_meta_cookie( $ticket_meta, $provider );
	}

	/**
	 * Get the meta field value from field value key for the attendee.
	 *
	 * @since 5.1.0
	 *
	 * @param string   $key             The meta field value key.
	 * @param int      $ticket_id       The ticket ID.
	 * @param int|null $attendee_number The attendee number index value from the order, starting with zero.
	 * @param int      $order_id        The order ID.
	 *
	 * @return string|null The meta field value from field value key for the attendee.
	 */
	public function get_meta_field_value_from_key_for_attendee( $key, $ticket_id, $attendee_number, $order_id ) {
		// Require a ticket and order ID and attendee number.
		if ( ! $ticket_id || ! $order_id || null === $attendee_number ) {
			return null;
		}

		// Attempt to get the value from the POSTed information.
		if ( ! empty( $_POST['tribe-tickets-meta'][ $attendee_number ][ $key ] ) ) {
			return $_POST['tribe-tickets-meta'][ $attendee_number ][ $key ];
		}

		// Get saved meta field values from the order meta key.
		$meta = get_post_meta( $order_id, self::META_KEY, true );

		// Check if the ticket is in the saved meta field values.
		if ( ! is_array( $meta ) || ! isset( $meta[ $ticket_id ][ $attendee_number ][ $key ] ) ) {
			return null;
		}

		return $meta[ $ticket_id ][ $attendee_number ][ $key ];
	}
}
