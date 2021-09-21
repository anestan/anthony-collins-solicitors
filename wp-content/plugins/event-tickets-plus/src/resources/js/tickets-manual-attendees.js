/* global tribe */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.2.0
 *
 * @type   {PlainObject}
 */
tribe.tickets = tribe.tickets || {};
tribe.dialogs = tribe.dialogs || {};
tribe.dialogs.events = tribe.dialogs.events || {};

/**
 * Configures ET Manual Attendees Object in the Global Tribe variable
 *
 * @since 5.2.0
 *
 * @type   {PlainObject}
 */
tribe.tickets.manualAttendees = {};

/**
 * Initializes in a Strict env the code that manages the plugin Manual Attendees library.
 *
 * @since 5.2.0
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.tickets.manualAttendees
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	const $document = $( document );

	/*
	 * Manual Attendees Selectors.
	 *
	 * @since 5.2.0
	 */
	obj.selectors = {
		modalWrapper: '.tribe-modal__wrapper--manual-attendees',
		modalTitle: '.tribe-modal__title',
		modalContent: '.tribe-modal__content',
		formAddAttendeeTicketSelect: '.tribe-tickets__manual-attendees-add-attendee-ticket-select',
		form: '.tribe-tickets__manual-attendees-form',
		formFieldName: '.tribe-tickets__manual-attendees-form-field-name',
		formFieldEmail: '.tribe-tickets__manual-attendees-form-field-email',
		formFieldEmailResend: '.tribe-tickets__manual-attendees-resend-email',
		formFieldEmailResendCheckbox: '.tribe-tickets__manual-attendees-resend-email-input',
		hiddenElement: '.tribe-common-a11y-hidden',
		validationNotice: '.tribe-tickets__notice--error',
	};

	/**
	 * Store attendee email to see if it changes on edit.
	 *
	 * @since 5.2.0
	 */
	obj.attendeeTicketEmail = '';

	/**
	 * Handler for when the modal is being "closed".
	 *
	 * @since 5.2.0
	 *
	 * @param {object} event The close event.
	 * @param {object} dialogEl The dialog element.
	 *
	 * @return {void}
	 */
	obj.modalClose = function( event, dialogEl ) {
		const $modal = $( dialogEl );
		const $modalContent = $modal.find( obj.selectors.modalContent );

		obj.unbindModalEvents( $modalContent );
	};

	/**
	 * Bind handler for when the modal is being "closed".
	 *
	 * @since 5.2.0
	 *
	 * @return {void}
	 */
	obj.bindModalClose = function() {
		$( tribe.dialogs.events ).on(
			'tribeDialogCloseManualAttendeesModal.tribeTickets',
			obj.modalClose
		);
	};

	/**
	 * Unbinds events for the modal content container.
	 *
	 * @since 5.2.0
	 *
	 * @param {jQuery} $container jQuery object of the container.
	 */
	obj.unbindModalEvents = function( $container ) {
		const $emailInput = $container.find( obj.selectors.formFieldEmail );

		$emailInput.off();
		$container.off( 'afterAjaxSuccess.tribeTicketsAdmin', obj.bindModalEvents );
		$container.off();
	};

	/**
	 * Binds events for the going button.
	 *
	 * @since 5.2.0
	 *
	 * @param {jQuery} $container jQuery object of the container.
	 * @param  {object} requestData Object with request data.
	 *
	 * @return {void}
	 */
	obj.bindAddAttendeeTicketSelectChange = function( $container, requestData ) {
		const $ticketSelect = $container.find( obj.selectors.formAddAttendeeTicketSelect );

		$ticketSelect.on( 'change', function( e ) {
			const data = {
				action: 'tribe_tickets_admin_manager',
				request: requestData.request,
				attendeeId: requestData.attendeeId,
				eventId: requestData.eventId,
				ticketId: this.value,
				provider: requestData.provider,
			};

			tribe.tickets.admin.manager.request( data, $container );
		} );
	};

	/**
	 * Binds events for the modal content container.
	 *
	 * @since 5.2.0
	 *
	 * @param  {Event}       event    event object for 'afterAjaxSuccess.tribeTicketsAdmin' event.
	 * @param  {jqXHR}       jqXHR    Request object.
	 * @param  {PlainObject} settings Settings that this request was made with.
	 */
	obj.bindModalEvents = function( event, jqXHR, settings ) {
		const $container = event.data.container;
		const data = event.data.requestData;

		obj.bindAddAttendeeTicketSelectChange( $container, data );
		obj.bindEmailEvents( $container );
		obj.bindForm( $container );
	};

	/**
	 * Handle form success.
	 *
	 * @since 5.2.0
	 *
	 * @param  {Event}       event    event object for 'afterAjaxSuccess.tribeTicketsAdmin' event.
	 * @param  {jqXHR}       jqXHR    Request object.
	 * @param  {PlainObject} settings Settings that this request was made with.
	 */
	obj.handleFormSuccess = function( event, jqXHR, settings ) {
		const data = event.data.requestData;

		if ( 'submit' === data.step ) {
			setTimeout( function() {
				location.reload();
			}, 3000 );
		}
	};

	/**
	 * Handle the Manual Attendee form submission.
	 *
	 * @since 5.2.0
	 *
	 * @param {event} e submission event.
	 */
	obj.handleFormSubmission = function( e ) {
		e.preventDefault();

		const $form = $( this );
		const $container = $form.closest( obj.selectors.modalContent );
		const $errorNotice = $container.find( obj.selectors.validationNotice );
		const params = $form.serializeArray();
		const attendeeId = $form.data( 'attendee-id' ) || null;
		const eventId = $form.data( 'event-id' ) || null;
		const ticketId = $form.data( 'ticket-id' ) || null;
		const provider = $form.data( 'provider' ) || null;
		const request = attendeeId ? 'tribe_tickets_manual_attendees_edit' : 'tribe_tickets_manual_attendees_add';

		const data = {
			action: 'tribe_tickets_admin_manager',
			request: request,
			attendeeId: attendeeId,
			eventId: eventId,
			ticketId: ticketId,
			provider: provider,
			step: 'submit',
		};

		$( params ).each( function( index, object ) {
			data[ object.name ] = object.value;
		} );

		if ( ! tribe.tickets.meta.validateAttendeeTicket( $container ) ) {
			$errorNotice.show();
			document.getElementById( obj.selectors.form.className() )
				.scrollIntoView(
					{
						behavior: 'smooth',
						block: 'start',
					}
				);
			return;
		}

		$errorNotice.hide();

		tribe.tickets.admin.manager.request( data, $container );

		// Bind the AJAX success on submit.
		$container.on(
			'afterAjaxSuccess.tribeTicketsAdmin',
			{ container: $container, requestData: data },
			obj.handleFormSuccess
		);
	};

	/**
	 * Binds events for the Manual Attendees form.
	 *
	 * @since 5.2.0
	 *
	 * @param {jQuery} $container jQuery object of the RSVP container.
	 *
	 * @return {void}
	 */
	obj.bindForm = function( $container ) {
		const $form = $container.find( obj.selectors.form );

		$form.each( function( index, form ) {
			$( form ).on( 'submit', obj.handleFormSubmission );
		} );
	};

	/**
	 * Bind email events.
	 *
	 * @since 5.2.0
	 *
	 * @param {jQuery} $container jQuery object of the container.
	 */
	obj.bindEmailEvents = function( $container ) {
		const $emailInput = $container.find( obj.selectors.formFieldEmail );

		// Save the email value in the object, for reference.
		obj.attendeeTicketEmail = $container.find( obj.selectors.formFieldEmail ).val() || '';

		$emailInput.on( 'input', obj.handleEmailChangeCheck );
	};

	/**
	 * Bind email change check.
	 *
	 * @since 5.2.0
	 *
	 * @param {event} event The event.
	 */
	obj.handleEmailChangeCheck = function( event ) {
		const $input = $( event.target );
		const savedValue = obj.attendeeTicketEmail.trim().toLowerCase();

		if ( savedValue !== $input.val().trim().toLowerCase() ) {
			obj.emailReSendCheckboxShow( $input );
		} else {
			obj.emailReSendCheckboxHide( $input );
		}
	};

	/**
	 * Show the re-send email checkbox.
	 *
	 * @since 5.2.0
	 *
	 * @param {jQuery} $input The email input.
	 */
	obj.emailReSendCheckboxShow = function( $input ) {
		const $formFieldEmailResend = $input
			.closest( obj.selectors.form )
			.find( obj.selectors.formFieldEmailResend );

		// Mark the checkbox as on by default.
		$formFieldEmailResend
			.find( obj.selectors.formFieldEmailResendCheckbox )
			.prop( 'checked', true );

		// Show the field.
		$formFieldEmailResend
			.removeClass( obj.selectors.hiddenElement.className() );
	};

	/**
	 * Hide the re-send email checkbox.
	 *
	 * @since 5.2.0
	 *
	 * @param {jQuery} $input The email input.
	 */
	obj.emailReSendCheckboxHide = function( $input ) {
		const $formFieldEmailResend = $input
			.closest( obj.selectors.form )
			.find( obj.selectors.formFieldEmailResend );

		// Mark the checkbox as off.
		$formFieldEmailResend
			.find( obj.selectors.formFieldEmailResendCheckbox )
			.prop( 'checked', false );

		// Hide the field.
		$formFieldEmailResend
			.addClass( obj.selectors.hiddenElement.className() );
	};

	/**
	 * Handler for when the modal is opened.
	 *
	 * @since 5.2.0
	 *
	 * @param {object} event The show event.
	 * @param {object} dialogEl The dialog element.
	 * @param {object} trigger The event.
	 *
	 * @return {void}
	 */
	obj.modalOpen = function( event, dialogEl, trigger ) {
		const $modal = $( dialogEl );
		const $trigger = $( trigger.target ).closest( 'button' );
		const title = $trigger.data( 'modal-title' );
		const attendeeId = $trigger.data( 'attendee-id' ) || null;
		const eventId = $trigger.data( 'event-id' ) || null;
		const ticketId = $trigger.data( 'ticket-id' ) || null;
		const provider = $trigger.data( 'provider' ) || null;
		const request = attendeeId ? 'tribe_tickets_manual_attendees_edit' : 'tribe_tickets_manual_attendees_add';

		if ( title ) {
			const $modalTitle = $modal.find( obj.selectors.modalTitle );
			$modalTitle.html( title );
		}

		// And replace the content, something like this?
		const $modalContent = $modal.find( obj.selectors.modalContent );
		const data = {
			action: 'tribe_tickets_admin_manager',
			request: request,
			attendeeId: attendeeId,
			eventId: eventId,
			ticketId: ticketId,
			provider: provider,
		};

		tribe.tickets.admin.manager.request( data, $modalContent );

		// Bind the modal events after AJAX success.
		$modalContent.on(
			'afterAjaxSuccess.tribeTicketsAdmin',
			{ container: $modalContent, requestData: data },
			obj.bindModalEvents
		);
	};

	/**
	 * Bind handler for when the modal is being "opened".
	 *
	 * @since 5.2.0
	 *
	 * @return {void}
	 */
	obj.bindModalOpen = function() {
		$( tribe.dialogs.events ).on(
			'tribeDialogShowManualAttendeesModal.tribeTickets',
			obj.modalOpen
		);
	};

	/**
	 * Handles the initialization of the scripts when Document is ready.
	 *
	 * @since 5.2.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.bindModalOpen();
		obj.bindModalClose();
	};

	// Configure on document ready.
	$document.ready( obj.ready );
} )( jQuery, tribe.tickets.manualAttendees );
