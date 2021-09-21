/* global jQuery, tribe, Attendees */

/**
 * Configures Resend Ticket JS Object in the Global Tribe variable
 *
 * @since 5.2.5
 *
 * @type {Object}
 */
tribe.tickets.resendTickets = {};

/**
 * Initializes in a Strict env the code that manages resend ticket functionality.
 *
 * @since 5.2.5
 *
 * @param {Object} $ jQuery
 * @param {Object} obj tribe.tickets.resendTickets
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';

	/**
	 * List of selectors.
	 *
	 * @since 5.2.5
	 */
	obj.selectors = {
		resendButton: '.re-send-ticket-action',
	};

	/**
	 * Localized data for Resend Ticket.
	 *
	 * @since 5.2.5
	 *
	 * @type {object}
	 */
	obj.data = Attendees.resend_ticket || {};

	/**
	 * Active button link element.
	 *
	 * @since 5.2.5
	 *
	 * @type {null|jQuery}
	 */
	obj.clickedButton = null;

	/**
	 * Handle Resend Ticket click.
	 *
	 * @since 5.2.5
 	 */
	obj.resendHandler = function ( e ) {

		obj.clickedButton = $( this );
		obj.clickedButton.prop( 'disabled', true );
		obj.clickedButton.text( obj.data.progress_label );

		const params = {
			action : 'event-tickets-plus-resend-tickets',
			attendee_id: obj.clickedButton.attr( 'data-attendee-id' ),
			provider: obj.clickedButton.attr( 'data-provider' ),
			nonce : obj.data.nonce,
		};

		$.post(
			Attendees.ajaxurl,
			params,
			obj.responseHandler,
			'json'
		);
	}

	/**
	 * Handler Ajax response.
	 *
	 * @since 5.2.5
	 *
	 * @param {Object} response
	 */
	obj.responseHandler = function( response ) {

		if ( ! obj.clickedButton instanceof jQuery ) {
			return;
		}

		if ( response.success ) {
			obj.clickedButton.text( obj.data.success_label );
			obj.clickedButton.addClass( 'button-disabled' );
		} else {
			obj.clickedButton.text( obj.data.default_label );
			obj.clickedButton.prop( 'disabled', false );
		}

		alert( response.data.message );
	}

	/**
	 * Handles the initialization of the scripts when Document is ready.
	 *
	 * @since 5.2.5
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$( obj.selectors.resendButton ).on( 'click', obj.resendHandler );
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.tickets.resendTickets );
