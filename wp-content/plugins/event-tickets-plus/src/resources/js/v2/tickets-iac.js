/* global tribe, jQuery */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.1.0
 *
 * @type   {Object}
 */
tribe.tickets = tribe.tickets || {};

/**
 * Configures ET IAC Object in the Global Tribe variable
 *
 * @since 5.1.0
 *
 * @type   {Object}
 */
tribe.tickets.iac = {};

/**
 * Initializes in a Strict env the code that manages the plugin IAC library.
 *
 * @since 5.1.0
 *
 * @param  {Object} $   jQuery
 * @param  {Object} obj tribe.tickets.data
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	const $document = $( document );

	/*
	 * IAC Selectors.
	 *
	 * @since 5.1.0
	 */
	obj.selectors = {
		ticketsPageMeta: '.tribe-event-tickets-plus-meta',
		ticketsPageMetaEmail: '.tribe-tickets-meta-email',
		ticketsPageMetaEmailReSend: '.tribe-tickets__tickets-page-attendee-meta-resend-email',
		ticketsPageMetaEmailReSendTemplate: '.tribe-tickets__tickets-page-attendee-meta-resend-email-template',
		formFieldName: '.tribe-tickets__iac-field--name',
		formFieldEmail: '.tribe-tickets__iac-field--email',
		formFieldNameUniqueErrorTemplate: '.tribe-tickets__iac-unique-name-error-template',
		formFieldEmailUniqueErrorTemplate: '.tribe-tickets__iac-unique-email-error-template',
	};

	/*
	 * Object where we store the saved emails by attendee.
	 * We use it to check if the user is changing the value and
	 * we want to display the "Re-send ticket" checkbox.
	 *
	 * @since 5.1.0
	 */
	obj.ticketsPageIACMetaEmailSaved = {};

	/**
	 * Object to store the ticket data on AR, to
	 * check for unique fields.
	 *
	 * @since 5.1.0
	 */
	obj.attendeeTicketData = {};

	/**
	 * Remove the re-send email checkbox for IAC in "My tickets" page.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $input The email input.
	 */
	obj.ticketsPageMetaEmailReSendCheckboxRemove = function( $input ) {
		$input
			.closest( obj.selectors.ticketsPageMetaEmail )
			.find( obj.selectors.ticketsPageMetaEmailReSend )
			.remove();
	};

	/**
	 * Append the re-send email checkbox for IAC in "My tickets" page.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $input The email input.
	 * @param {number} attendeeId The attendee ID.
	 */
	obj.ticketsPageMetaEmailReSendCheckboxAdd = function( $input, attendeeId ) {
		const hasCheckbox = $input
			.closest( obj.selectors.ticketsPageMetaEmail )
			.find( obj.selectors.ticketsPageMetaEmailReSend ).length;

		// Bail if it was added already.
		if ( hasCheckbox ) {
			return;
		}

		const metaEmailResendTemplate = window.wp.template(
			obj.selectors.ticketsPageMetaEmailReSendTemplate.className() + '-' + attendeeId
		);

		// Append the re-send checkbox from the underscores template.
		$input.after( metaEmailResendTemplate() );
	};

	/**
	 * Hook actions to the afterSetup of the tickets block.
	 *
	 * @since 5.1.0
	 *
	 * @param {Event} event The event.
	 */
	obj.bindEmailChangeCheck = function( event ) {
		const $input = $( event.target );
		const $attendee = $input.closest( obj.selectors.ticketsPageMeta );
		const attendeeId = $attendee.data( 'attendee-id' );
		const savedValue = obj.ticketsPageIACMetaEmailSaved[ attendeeId ];

		if ( savedValue !== $input.val().trim() ) {
			obj.ticketsPageMetaEmailReSendCheckboxAdd( $input, attendeeId );
		} else {
			obj.ticketsPageMetaEmailReSendCheckboxRemove( $input );
		}
	};

	/**
	 * Bind the Attendee "Re-send email" functionality.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container The container of the tickets page.
	 */
	obj.bindAttendeeReSendEmail = function( $container ) {
		const attendeeReSendEmail = !! $container.data( 'attendee-resend-email' );

		if ( ! attendeeReSendEmail ) {
			return;
		}

		const $attendees = $container.find( obj.selectors.ticketsPageMeta );

		$attendees.each(
			function() {
				const $attendee = $( this );
				const attendeeId = $attendee.data( 'attendee-id' );
				const $emailMeta = $attendee.find( obj.selectors.ticketsPageMetaEmail );
				const $emailMetaInput = $emailMeta.find( 'input' );

				if ( $emailMetaInput.length && '' !== $emailMetaInput.val() ) {
					obj.ticketsPageIACMetaEmailSaved[ attendeeId ] = $emailMetaInput.val().trim();
				}

				$emailMetaInput.on( 'keyup', obj.bindEmailChangeCheck );
			}
		);
	};

	/**
	 * Hook actions to the afterSetup of the tickets page.
	 *
	 * @since 5.1.0
	 *
	 * @param {Event} event The event.
	 * @param {jQuery} $container The container of the tickets page.
	 */
	obj.bindTicketsPageActions = function( event, $container ) {
		obj.bindAttendeeReSendEmail( $container );
	};

	/**
	 * Get the values from mapping.
	 *
	 * @since 5.1.0
	 *
	 * @param {number} index The index.
	 * @param {object} input The input.
	 *
	 * @return {string} The input value.
	 */
	obj.getInputValuesFromMap = function( index, input ) {
		return input.value.trim();
	};

	/**
	 * Get an array of values from a list of inputs.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $inputs jQuery object of the inputs.
	 *
	 * @return {array} The array with the values.
	 */
	obj.getInputValuesToArray = function( $inputs ) {
		return $inputs.map( obj.getInputValuesFromMap ).get();
	};

	/**
	 * Get the input values by field.
	 * Store them in the object the first time and then fetch from there.
	 *
	 * @since 5.1.0
	 *
	 * @param {number} ticketId The ticket ID.
	 * @param {string} field The field name.
	 *
	 * @return {array} The array with the values of the type of field.
	 */
	obj.getInputValuesByField = function( ticketId, field ) {
		// Return the input values for the field (name or email, for now).
		return obj.attendeeTicketData[ ticketId ][ field ];
	};

	/**
	 * Remove the IAC unique error message relative to the $input.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $input jQuery object of the inputs.
	 *
	 * @return {void}
	 */
	obj.removeIacUniqueErrorMessage = function( $input ) {
		$input.siblings( tribe.tickets.meta.selectors.formFieldInputHelperError ).remove();
	};

	/**
	 * Add the IAC error messsage for the fields.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $input jQuery object of the inputs.
	 * @param {string} field The field type.
	 *
	 * @return {void}
	 */
	obj.addIacUniqueErrorMessage = function( $input, field ) {
		const hasError = $input.siblings( tribe.tickets.meta.selectors.formFieldInputHelperError ).length;

		if ( hasError ) {
			return;
		}

		let uniqueErrorTemplate;
		if ( 'name' === field ) {
			uniqueErrorTemplate = window.wp.template(
				obj.selectors.formFieldNameUniqueErrorTemplate.className()
			);
		} else if ( 'email' === field ) {
			uniqueErrorTemplate = window.wp.template(
				obj.selectors.formFieldEmailUniqueErrorTemplate.className()
			);
		}

		// Append the error.
		$input.after( uniqueErrorTemplate() );
	};

	/**
	 * Load the unique meta values to the `attendeeTicketData`
	 *
	 * @since 5.1.0
	 *
	 * @param {number} index The index.
	 * @param {Object} attendeeTicketsForm The tickets form we are getting the values from.
	 */
	obj.loadUniqueMetaValuesPerTicket = function( index, attendeeTicketsForm ) {
		const $attendeeTicketsForm = $( attendeeTicketsForm );
		const ticketId = $attendeeTicketsForm.data( 'ticket-id' );

		// Create the ticketId if it wasn't there before.
		if ( ! obj.attendeeTicketData.hasOwnProperty( ticketId ) ) {
			obj.attendeeTicketData[ ticketId ] = {};
		}

		// Get the input values by its type and store them in the object so we fetch once.
		const $emailInputs = $attendeeTicketsForm.find( obj.selectors.formFieldEmail + ' input' );
		const emailValues = obj.getInputValuesToArray( $emailInputs );
		obj.attendeeTicketData[ ticketId ].email = emailValues;

		const $nameInputs = $attendeeTicketsForm.find( obj.selectors.formFieldName + ' input' );
		const nameValues = obj.getInputValuesToArray( $nameInputs );
		obj.attendeeTicketData[ ticketId ].name = nameValues;
	};

	/**
	 * Load the unique meta values to the `attendeeTicketData`
	 * object when the form is submitted.
	 *
	 * @since 5.1.0
	 *
	 * @param {event} event The event.
	 * @param {jQuery} $form The container of the tickets page.
	 */
	obj.loadUniqueMetaValues = function( event, $form ) {
		const $attendeeTickets = $form.find( tribe.tickets.meta.selectors.formAttendeeTickets );

		$attendeeTickets.each( obj.loadUniqueMetaValuesPerTicket );
	};

	/**
	 * Hook actions to the afterSetup of the tickets page.
	 *
	 * @since 5.1.0
	 *
	 * @param {event} event The event.
	 * @param {jQuery} $input The input jQuery object.
	 * @param {boolean} isValidField If the Attendee ticket field is valid.
	 */
	obj.bindUniqueMetaValidation = function( event, $input, isValidField ) {
		// Bail if the field is not valid.
		if ( ! isValidField ) {
			return;
		}

		const inputValue = $input.val().trim().toLowerCase();

		// Bail if there's no value.
		if ( '' === inputValue ) {
			return;
		}

		const $inputWrapper = $input.closest( tribe.tickets.meta.selectors.formField );

		// Bail if we don't have to check if it is unique.
		if ( ! $inputWrapper.hasClass( tribe.tickets.meta.selectors.formFieldUnique.className() ) ) {
			return;
		}

		let field;
		const $form = $input.closest( tribe.tickets.meta.selectors.formAttendeeTickets );
		const ticketId = $form.data( 'ticket-id' );

		if ( $inputWrapper.hasClass( obj.selectors.formFieldName.className() ) ) {
			field = 'name';
		} else if ( $inputWrapper.hasClass( obj.selectors.formFieldEmail.className() ) ) {
			field = 'email';
		}

		// Get the input values for the field (name or email).
		const uniqueValues = obj.getInputValuesByField( ticketId, field );

		// Bail if there's only one, nothing to compare with.
		if ( 2 > uniqueValues.length ) {
			return;
		}

		// Check if it's unique, by filtering by the input value. If there's only one like that, it is unique.
		const arrayIsUnique = uniqueValues.filter( function( element ) {
			return element.toLowerCase() === inputValue;
		} );

		// Only valid if it's unique. (If the filtered array has only one value).
		isValidField = 1 === arrayIsUnique.length;

		if ( ! isValidField ) {
			$input.addClass( tribe.tickets.meta.selectors.formFieldInputError.className() );
			obj.addIacUniqueErrorMessage( $input, field );
		} else {
			$input.removeClass( tribe.tickets.meta.selectors.formFieldInputError.className() );
			obj.removeIacUniqueErrorMessage( $input );
		}

		$input.data( 'valid', isValidField );
	};

	/**
	 * Handles the initialization of the scripts when Document is ready.
	 *
	 * @since 5.1.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on(
			'afterSetup.tribeTicketsPage',
			obj.bindTicketsPageActions
		);

		$document.on(
			'beforeValidateForm.tribeTicketsMeta',
			obj.loadUniqueMetaValues
		);

		$document.on(
			'afterValidateField.tribeTicketsMeta',
			obj.bindUniqueMetaValidation
		);
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.tickets.iac );
