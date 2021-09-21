/* global tribe, JSON, jQuery */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.1.0
 *
 * @type   {Object}
 */
tribe.tickets = tribe.tickets || {};

/**
 * Configures ET data Object in the Global Tribe variable
 *
 * @since 5.1.0
 *
 * @type   {Object}
 */
tribe.tickets.data = {};

/**
 * Initializes in a Strict env the code that manages the plugin data library.
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

	/**
	 * Stores attendee and cart form data to sessionStorage.
	 *
	 * @param {number|string} eventId The ID of the event/post we're on.
	 *
	 * @since 5.1.0
	 */
	obj.storeLocal = function( eventId ) {
		const postId = eventId || tribe.tickets.utils.getTicketsPostId();
		const $form = tribe.tickets.utils.getTicketsFormFromPostId( postId );
		const tickets = tribe.tickets.block.getTicketsForCart( $form );
		const $container = $form.closest( tribe.tickets.block.selectors.container );
		const $metaFormItems = $container.find( tribe.tickets.modal.metaItem );
		const meta = obj.getMetaForSave( $metaFormItems );

		sessionStorage.setItem(
			'tribe_tickets_attendees-' + postId,
			JSON.stringify( meta )
		);

		sessionStorage.setItem(
			'tribe_tickets_cart-' + postId,
			JSON.stringify( tickets )
		);
	};

	/**
	 * Get and format the meta to save.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $items The jQuery object of the items.
	 *
	 * @returns {Object} Meta data object.
	 */
	obj.getMetaForSave = function( $items ) {
		const meta = [];
		const tempMeta = [];

		$items.each(
			function() {
				const data = {};
				const $row = $( this );
				const ticketId = $row.data( 'ticket-id' );

				const $fields = $row.find( tribe.tickets.meta.selectors.formFieldInput );

				// Skip tickets with no meta fields.
				if ( ! $fields.length ) {
					return;
				}

				if ( ! tempMeta[ ticketId ] ) {
					tempMeta[ ticketId ] = {};
					tempMeta[ ticketId ].ticket_id = ticketId;
					tempMeta[ ticketId ].items = [];
				}

				$fields.each(
					function() {
						const $field = $( this );
						let value = $field.val();
						const isRadio = $field.is( ':radio' );
						let name = $field.attr( 'name' );

						// Grab everything after the last bracket `[ `.
						name = name.split( '[' );
						name = name.pop().replace( ']', '' );

						// Skip unchecked radio/checkboxes.
						if ( isRadio || $field.is( ':checkbox' ) ) {
							if ( ! $field.prop( 'checked' ) ) {
								// If empty radio field, if field already has a value, skip setting it as empty.
								if ( isRadio && '' !== data[ name ] ) {
									return;
								}
								value = '';
							}
						}

						data[ name ] = value;
					}
				);
				tempMeta[ ticketId ].items.push( data );
			}
		);

		Object.keys( tempMeta ).forEach( function( index ) {
			const newArr = {
				ticket_id: index,
				items: tempMeta[ index ].items,
			};
			meta.push( newArr );
		} );

		return meta;
	};

	/**
	 * Clears attendee and cart form data for this event from sessionStorage.
	 *
	 * @param {number|string} eventId The ID of the event/post we're on.
	 *
	 * @since 5.1.0
	 */
	obj.clearLocal = function( eventId ) {
		const postId = eventId || tribe.tickets.utils.getTicketsPostId();

		sessionStorage.removeItem( 'tribe_tickets_attendees-' + postId );
		sessionStorage.removeItem( 'tribe_tickets_cart-' + postId );
	};

	/**
	 * Gets attendee and cart form data from sessionStorage.
	 *
	 * @since 5.1.0
	 *
	 * @param {number|string} eventId The ID of the event/post we're on.
	 *
	 * @returns {array} An array of the data.
	 */
	obj.getLocal = function( eventId ) {
		const postId = eventId || tribe.tickets.utils.getTicketsPostId();
		const meta = JSON.parse( sessionStorage.getItem( 'tribe_tickets_attendees-' + postId ) );
		const tickets = JSON.parse( sessionStorage.getItem( 'tribe_tickets_cart-' + postId ) );
		const ret = {};
		ret.meta = meta;
		ret.tickets = tickets;

		return ret;
	};

	/**
	 * Get cart & meta data from sessionStorage, otherwise make an ajax call.
	 * Always loads tickets from API on page load to be sure we keep up to date with the cart.
	 *
	 * This returns a deferred data object ( promise ) So when calling you need to use something like
	 * jQuery's $.when()
	 *
	 * Example:
	 *  $.when(
	 *     obj.getData()
	 *  ).then(
	 *     function( data ) {
	 *         // Do stuff with the data.
	 *     }
	 *  );
	 *
	 * @since 5.1.0
	 *
	 * @param {boolean|string} pageLoad If we are experiencing a page load.
	 * @param {int} eventId The post ID we want to retrieve data for.
	 *
	 * @returns {object} Deferred data object.
	 */
	obj.getData = function( pageLoad, eventId ) {
		let ret = {};
		ret.meta = {};
		ret.tickets = {};
		const deferred = $.Deferred();
		const postId = eventId || tribe.tickets.utils.getTicketsPostId();
		const meta = JSON.parse( sessionStorage.getItem( 'tribe_tickets_attendees-' + postId ) );

		if ( null !== meta ) {
			ret.meta = meta;
		}

		// If we haven't reloaded the page, assume the cart hasn't changed since we did.
		if ( ! pageLoad ) {
			const tickets = JSON.parse( sessionStorage.getItem( 'tribe_tickets_cart-' + postId ) );

			if ( null !== tickets && tickets.length ) {
				ret.tickets = tickets;
			}

			deferred.resolve( ret );
		}

		if ( ! ret.tickets || ! ret.meta ) {
			const $providerId = tribe.tickets.utils.getTicketsProviderIdFromPostId( postId );
			$.ajax( {
				type: 'GET',
				data: {
					provider: $providerId,
					post_id: postId,
				},
				dataType: 'json',
				url: tribe.tickets.utils.getRestEndpoint(),
				success: function( data ) {
					// Store for future use.
					if ( null === meta ) {
						sessionStorage.setItem(
							'tribe_tickets_attendees-' + postId,
							JSON.stringify( data.meta )
						);
					}

					sessionStorage.setItem(
						'tribe_tickets_cart-' + postId, // @todo: review this and how to get it from the container data.
						JSON.stringify( data.tickets )
					);

					ret = {
						meta: data.meta,
						tickets: data.tickets,
					};

					deferred.resolve( ret );
				},
				error: function() {
					deferred.reject( false );
				},
			} );
		}

		return deferred.promise();
	};

	/**
	 * Handles the initialization of the scripts when Document is ready.
	 *
	 * @since 5.1.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		/**
		 * Stores to sessionStorage onbeforeunload for accidental refreshes, etc.
		 *
		 * @since 5.1.0
		 */
		$document.on(
			'beforeunload',
			function() {
				if ( window.tribe.tickets.modal_redirect ) {
					tribe.tickets.data.clearLocal();
					return;
				}

				const $ticketsBlock = $document.find( tribe.tickets.block.selectors.container );
				// Iterate over the tickets block and storeLocal.
				$ticketsBlock.each( function( index, block ) {
					const blockPostId = $( block ).find( tribe.tickets.block.selectors.form ).data( 'post-id' );
					obj.data.storeLocal( blockPostId );
				} );

			}
		);
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.tickets.data );
