var tribe_event_tickets_plus = tribe_event_tickets_plus || {};
tribe_event_tickets_plus.meta = tribe_event_tickets_plus.meta || {};
tribe_event_tickets_plus.meta.admin = tribe_event_tickets_plus.meta.admin || {};
tribe_event_tickets_plus.meta.admin.event = tribe_event_tickets_plus.meta.admin.event || {};

( function ( window, document, $, obj ) {
	'use strict';

	/*
	 * Selectors.
	 *
	 * @since 5.2.2
	 */
	obj.selectors = {
		fieldsetSaved: '.tribe-tickets__admin-attendees-saved-fields',
		fieldsetSavedHasIac: '.tribe-tickets__admin-attendees-saved-fields--has-iac',
		iacNotice: '.tribe-tickets__admin-attendees-info-iac-notice',
		iacMockedFields: '.tribe-tickets__admin-attendees-info-iac-fields',
		iacMockedFieldsLabelRequired: '.tribe-tickets__admin-attendee-info-field-title-label--required',
		iacNoneCheckbox: '#ticket_iac_setting_none',
		iacAllowedCheckbox: '#ticket_iac_setting_allowed',
		iacRequiredCheckbox: '#ticket_iac_setting_required',
		hidden: '.tribe-common-a11y-hidden',
	};

	/**
	 * Initializes the meta functionality
	 */
	obj.init = function() {
		obj.$tribe_tickets = $( document.getElementById( 'tribetickets' ) );
		obj.$event_tickets = $( document.getElementById( 'event_tickets' ) );

		obj.$event_tickets
			.on( 'change', 'input.show_attendee_info', obj.event.toggleLinkedForm )
			.on( 'change', '.save_attendee_fieldset', obj.event.toggleLinkedForm );

		// Force the click event to be removed from the faux postboxes we have (WP 5.5+ compat).
		$( '.meta-postbox .hndle, .meta-postbox .handlediv' ).off( 'click' );

		obj.$tribe_tickets
			.on( 'change', '.ticket-attendee-info-dropdown', obj.event.selectSavedFieldset )
			.on( 'change', '.save_attendee_fieldset', obj.event.toggleLinkedForm )
			.on( 'click', '.meta-postbox .hndle, .meta-postbox .handlediv', obj.event.clickPostbox )
			.on( 'click', 'a.add-attendee-field', obj.event.addField )
			.on( 'click', 'a.delete-attendee-field', obj.event.removeField )
			.on( 'edit-ticket.tribe', obj.event.editTicket )
			.on( 'saved-ticket.tribe', obj.event.savedTicket )
			// @todo Revisit rsvp toggle once we enable IAC for RSVPs.
			.on( 'click', '#rsvp_form_toggle', obj.event.handleNewRsvp )
			.on( 'click', '#ticket_form_toggle', obj.event.handleNewTicket )
			.on( 'change', obj.selectors.iacNoneCheckbox, obj.event.handleIacCheckbox )
			.on( 'change', obj.selectors.iacAllowedCheckbox, obj.event.handleIacCheckbox )
			.on( 'change', obj.selectors.iacRequiredCheckbox, obj.event.handleIacCheckbox );

		obj.initTicketFields();

		obj.$event_tickets.trigger( 'event-tickets-plus-meta-initialized.tribe' );
	};

	/**
	 * Sets up the custom meta field area for the ticket form
	 */
	obj.initTicketFields = function() {
		if ( ! obj.$event_tickets ) {
			obj.$event_tickets = $( document.getElementById( 'event_tickets' ) );
		}

		obj.initCustomFieldSorting();
		obj.maybeHideSavedFieldsSelect();
		obj.maybeShowIacMockedFields();
		obj.$event_tickets.trigger( 'event-tickets-plus-ticket-meta-initialized.tribe', {
			ticket_id: obj.$event_tickets.find( '#ticket_id' ).val(),
		} );
	};

	/**
	 * Initializes the sortable area for custom fields
	 */
	obj.initCustomFieldSorting = function() {
		$( document.getElementById( 'tribe-tickets-attendee-sortables' ) ).sortable( {
			containment: 'parent',
			items: '> div',
			tolerance: 'pointer',
			connectWith: '#tribe-tickets-attendee-sortables',
		} );
	};

	/**
	 * Toggles a form linked to a checkbox
	 *
	 * Forms (or containers with form fields) are linked to a checkbox via an
	 * ID/data-tribe-toggle relationship. The checkbox has the data-tribe-toggle
	 * attribute that corresponds to the HTML element that will be toggled open or
	 * closed based on the checkbox state.
	 *
	 * Checked == open
	 * Unchecked == closed
	 *
	 * @param {jQuery} $checkbox Checkbox input field.
	 */
	obj.toggleLinkedForm = function( $checkbox ) {
		let $form = $();
		const formId = $checkbox.data( 'tribe-toggle' );

		if ( formId ) {
			$form = $( document.getElementById( formId ) );
		}

		if ( $checkbox.is( ':checked' ) ) {
			$form.show();
		} else {
			$form.hide();
		}
	};

	/**
	 * hide the saved fields selection if there are active fields
	 */
	obj.maybeHideSavedFieldsSelect = function() {
		if ( $( '.tribe-tickets__admin-attendee-info-field--active' ).length ) {
			$( obj.selectors.fieldsetSaved ).hide();
		} else {
			$( obj.selectors.fieldsetSaved ).show();
		}
	};

	/**
	 * Maybe show the IAC mocked fields and notice.
	 *
	 * @since 5.2.2
	 *
	 * @return {void}
	 */
	obj.maybeShowIacMockedFields = function() {
		const iacAllowedChecked = $( obj.selectors.iacAllowedCheckbox ).is( ':checked' );
		const iacRequiredChecked = $( obj.selectors.iacRequiredCheckbox ).is( ':checked' );

		if ( ( ! iacAllowedChecked && ! iacRequiredChecked ) ) {
			obj.hideIacMockedFields();
		} else {
			obj.showIacMockedFields();
		}
	};

	/**
	 * Hide the IAC mocked fields.
	 *
	 * @since 5.2.2
	 *
	 * @return {void}
	 */
	obj.hideIacMockedFields = function() {
		const $form = $( obj.selectors.iacAllowedCheckbox ).closest( '#ticket_form' );
		const $iacNotice = $form.find( obj.selectors.iacNotice );
		const $iacMockedFields = $form.find( obj.selectors.iacMockedFields );
		const $fieldsetSaved = $form.find( obj.selectors.fieldsetSaved );
		const hiddenClassName = obj.selectors.hidden.className();

		$iacNotice.addClass( hiddenClassName );
		$iacMockedFields.addClass( hiddenClassName );
		$fieldsetSaved.removeClass( obj.selectors.fieldsetSavedHasIac.className() );
	};

	/**
	 * Show the IAC mocked fields.
	 *
	 * @since 5.2.2
	 *
	 * @return {void}
	 */
	obj.showIacMockedFields = function() {
		const $form = $( obj.selectors.iacAllowedCheckbox ).closest( '#ticket_form' );
		const $iacMockedFields = $form.find( obj.selectors.iacMockedFields );
		const $iacNotice = $form.find( obj.selectors.iacNotice );
		const $iacRequiredLabel = $iacMockedFields.find( obj.selectors.iacMockedFieldsLabelRequired );
		const $fieldsetSaved = $form.find( obj.selectors.fieldsetSaved );
		const iacRequiredChecked = $( obj.selectors.iacRequiredCheckbox ).is( ':checked' );
		const hiddenClassName = obj.selectors.hidden.className();

		$iacNotice.removeClass( hiddenClassName );
		$iacMockedFields.removeClass( hiddenClassName );

		if ( iacRequiredChecked ) {
			$iacRequiredLabel.removeClass( hiddenClassName );
		} else {
			$iacRequiredLabel.addClass( hiddenClassName );
		}

		$fieldsetSaved.addClass( obj.selectors.fieldsetSavedHasIac.className() );
	};

	/**
	 * Handle the IAC checkbox changes.
	 *
	 * @since 5.2.2
	 *
	 * @param {event} e The event.
	 *
	 * @return {void}
	 */
	obj.event.handleIacCheckbox = function( e ) {
		e.preventDefault();

		obj.maybeShowIacMockedFields();
	};

	/**
	 * Handle the "New Ticket" click.
	 *
	 * @since 5.2.2
	 *
	 * @param {event} e The event.
	 *
	 * @return {void}
	 */
	obj.event.handleNewTicket = function( e ) {
		e.preventDefault();

		obj.maybeShowIacMockedFields();
	};

	/**
	 * Handle the "New RSVP" click.
	 *
	 * @since 5.2.2
	 *
	 * @param {event} e The event.
	 *
	 * @return {void}
	 */
	obj.event.handleNewRsvp = function( e ) {
		e.preventDefault();

		obj.hideIacMockedFields();
	};

	/**
	 * Fetches saved fields via AJAX.
	 *
	 * @param {int} savedFieldsetId Fieldset ID to fetch via AJAX
	 * @return {Object} jqXHR
	 */
	obj.fetch_saved_fields = function( savedFieldsetId ) {
		// load the saved fieldset.
		const args = {
			action: 'tribe-tickets-load-saved-fields',
			fieldset: savedFieldsetId,
		};

		return $.post(
			ajaxurl,
			args,
			'json'
		);
	};

	/**
	 * Injects a saved fieldset into the custom meta field area
	 *
	 * @param {int} savedFieldsetId Fieldset ID to inject
	 */
	obj.inject_saved_fields = function( savedFieldsetId ) {
		const fieldJqxhr = obj.fetch_saved_fields( savedFieldsetId );

		fieldJqxhr.done( function( response ) {
			if ( ! response.success ) {
				obj.$event_tickets.trigger(
					'event-tickets-plus-fieldset-load-failure.tribe',
					{
						fieldset_id: savedFieldsetId,
					}
				);
				return;
			}

			$( document.getElementById( 'tribe-tickets-attendee-sortables' ) ).append( response.data );

			obj.maybeHideSavedFieldsSelect();

			obj.$event_tickets.trigger( 'event-tickets-plus-fieldset-loaded.tribe', { fieldset_id: savedFieldsetId } );
		} );

		fieldJqxhr.fail( function() {
			obj.$event_tickets.trigger( 'event-tickets-plus-fieldset-load-failure.tribe', { fieldset_id: savedFieldsetId } );
		} );
	};

	/**
	 * Adds a custom meta field to the custom meta field area
	 *
	 * @param {string} type Type of field to add
	 */
	obj.addField = function( type ) {
		const args = {
			action: 'tribe-tickets-info-render-field',
			type: type,
		};

		const jqxhr = $.post(
			ajaxurl,
			args,
			'json'
		);

		jqxhr.done( function( response ) {
			if ( ! response.success ) {
				obj.$event_tickets.trigger( 'event-tickets-plus-field-add-failure.tribe', { type: type } );
				return;
			}

			$( document.getElementById( 'tribe-tickets-attendee-sortables' ) ).append( response.data );
			obj.maybeHideSavedFieldsSelect();
			obj.$event_tickets.trigger( 'event-tickets-plus-field-added.tribe', { type: type } );
		} );

		jqxhr.fail( function() {
			obj.$event_tickets.trigger( 'event-tickets-plus-field-add-failure.tribe', { type: type } );
		} );
	};

	/**
	 * Removes a custom meta field from the custom meta field area
	 *
	 * @param {jQuery} $field Field to remove
	 */
	obj.removeField = function( $field ) {
		let fieldHtml = '';

		if ( 'undefined' !== $field[ 0 ].outerHTML ) {
			fieldHtml = $field[ 0 ].outerHTML;
		} else {
			fieldHtml = $( '<div>' ).append( $field.eq( 0 ).clone() ).html();
		}

		$field.remove();

		obj.maybeHideSavedFieldsSelect();
		obj.$event_tickets.trigger( 'event-tickets-plus-field-removed.tribe', { field: fieldHtml } );
	};

	/**
	 * Toggles the visibility of an element with WordPress postbox behaviors attached to it
	 *
	 * @param {jQuery} $postbox Element with the .postbox class to toggle visibility on.
	 */
	obj.toggle_postbox = function( $postbox ) {
		$postbox.toggleClass( 'closed' );
	};

	/**
	 * event to handle the toggling of a linked form
	 */
	obj.event.toggleLinkedForm = function() {
		obj.toggleLinkedForm( $( this ) );
	};

	/**
	 * event to handle injecting a saved fieldset into the custom meta field form
	 */
	obj.event.selectSavedFieldset = function() {
		const savedFieldsetId = $( this ).val();

		if ( ! savedFieldsetId || 0 === parseInt( savedFieldsetId, 10 ) ) {
			return;
		}

		obj.inject_saved_fields( savedFieldsetId );
	};

	/**
	 * event to handle the clicking of an add-field option
	 *
	 * @param {event} e The event.
	 */
	obj.event.addField = function( e ) {
		e.preventDefault();

		obj.addField( $( this ).data( 'type' ) );
	};

	/**
	 * Event to handle the clicking of the remove field link on a custom field.
	 *
	 * @param {event} e The event.
	 */
	obj.event.removeField = function( e ) {
		e.preventDefault();
		obj.removeField( $( this ).closest( '.meta-postbox' ) );
	};

	/**
	 * event to handle initializing the ticket area when editing an event ticket.
	 *
	 * @param {event} e The event.
	 */
	obj.event.editTicket = function() {
		obj.initTicketFields();
	};

	/**
	 * Toggles an element with the WordPress postbox behaviors tied to it via the .postbox and
	 * associated classes
	 */
	obj.event.clickPostbox = function() {
		obj.toggle_postbox( $( this ).closest( '.meta-postbox' ) );
	};

	obj.event.savedTicket = function( e, response ) {
		if ( 'undefined' === typeof response.fieldsets || ! response.success ) {
			return;
		}

		const $fieldsets = $( document.getElementById( 'saved_ticket-attendee-info' ) );
		$fieldsets.find( 'option:not([value="0"])' ).remove();

		for ( let i = 0; i < response.fieldsets.length; i++ ) {
			$fieldsets.append(
				'<option value="' + response.fieldsets[ i ].ID +
				'" data-attendee-group="' +
				response.fieldsets[ i ].post_name.replace( '"', '\\"' ) + '">' +
				response.fieldsets[ i ].post_name +
				'</option>'
			);
		}
	};

	$( obj.init );
} )( window, document, jQuery, tribe_event_tickets_plus.meta.admin );
