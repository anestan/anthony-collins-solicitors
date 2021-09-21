/* global tribe, jQuery */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.1.0
 *
 * @type {Object}
 */
tribe.tickets = tribe.tickets || {};

/**
 * Configures ET+ registration page Object in the Global Tribe variable
 *
 * @since 5.1.0
 *
 * @type {Object}
 */
tribe.tickets.registration = {};

/**
 * Initializes in a Strict env the code that manages the plugin registration page.
 *
 * @since 5.1.0
 *
 * @param  {Object} $ jQuery
 * @param  {Object} obj obj
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	const $document = $( document );

	obj.selectors = {
		container: '.tribe-tickets__registration',
		eventContainer: '.tribe-tickets__registration-event',
		form: '#tribe-tickets__registration-form',
		item: '.tribe-tickets__tickets-item',
		itemPrice: '.tribe-amount',
		itemQuantity: '.tribe-ticket-quantity',
		itemTotal: '.tribe-tickets__tickets-item-total .tribe-amount',
		footerQuantity: '.tribe-tickets__tickets-footer-quantity-number',
		footerAmount: '.tribe-tickets__tickets-footer-total .tribe-amount',
		checkoutButton: '.tribe-tickets__registration-submit',
		metaForm: '.tribe-tickets__registration-content',
		metaContainer: '.tribe-tickets__attendee-tickets-container',
		metaContainerHasTickets: '.tribe-tickets__attendee-tickets-container--has-tickets',
		metaItem: '.tribe-tickets__attendee-tickets-item',
		miniCart: '#tribe-tickets__mini-cart',
	};

	/*
	 * Commerce Provider Selectors.
	 *
	 * @since 5.1.0
	 */
	obj.commerceSelector = {
		edd: 'Tribe__Tickets_Plus__Commerce__EDD__Main',
		rsvp: 'Tribe__Tickets__RSVP',
		tpp: 'Tribe__Tickets__Commerce__PayPal__Main',
		Tribe__Tickets__Commerce__PayPal__Main: 'tribe-commerce',
		Tribe__Tickets__RSVP: 'rsvp',
		Tribe__Tickets_Plus__Commerce__EDD__Main: 'edd',
		Tribe__Tickets_Plus__Commerce__WooCommerce__Main: 'woo',
		tribe_eddticket: 'edd',
		tribe_tpp_attendees: 'tpp',
		tribe_wooticket: 'woo',
		woo: 'Tribe__Tickets_Plus__Commerce__WooCommerce__Main',
	};

	/**
	 * Get ticket data to send to cart.
	 *
	 * @since 5.1.0
	 *
	 * @return {object} Tickets data object.
	 */
	obj.getTicketsForSave = function() {
		const tickets = [];
		let $cartForm = $( obj.selectors.miniCart );

		if ( ! $cartForm.length ) {
			$cartForm = $( obj.selectors.container );
		}

		const $ticketRows = $cartForm.find( obj.selectors.item );

		$ticketRows.each(
			function() {
				const $row = $( this );
				const ticketId = $row.data( 'ticketId' );
				const qty = $row.find( obj.selectors.itemQuantity ).text();

				const data = {};
				data.ticket_id = ticketId;
				data.quantity = qty;

				tickets.push( data );
			}
		);

		return tickets;
	};

	/**
	 * Init the form pre-fills (cart and AR forms).
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object of object of the registration page container.
	 *
	 * @return {void}
	 */
	obj.initFormPreFills = function( $container ) {
		const $miniCart = $container.find( obj.selectors.miniCart );

		tribe.tickets.loader.show( $document );

		$.ajax( {
			type: 'GET',
			data: {
				provider: obj.providerId,
			},
			dataType: 'json',
			url: tribe.tickets.utils.getRestEndpoint(),
			success: function( data ) {
				if ( data.tickets ) {
					obj.preFillCartForm( $miniCart, data.tickets );
				}

				if ( data.meta ) {
					obj.appendARFields( $container, data );
					obj.preFillMetaForm( $container, data );

					window.dispatchEvent( new Event( 'tribe_et_after_form_prefills' ) );
				}
			},
			complete: function() {
				tribe.tickets.loader.hide( $document );
			},
		} );
	};

	/**
	 * Appends AR fields on page load.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object of object of the registration page container.
	 * @param {object} data The ticket meta we are using to add "blocks".
	 *
	 * @return {void}
	 */
	obj.appendARFields = function( $container, data ) {
		const tickets = data.tickets;
		let nonMetaCount = 0;
		let metaCount = 0;

		const $tribeRegistration = $container;

		$.each( tickets, function( index, ticket ) {
			const ticketTemplate = window.wp.template( 'tribe-registration--' + ticket.ticket_id );
			const $ticketContainer = $tribeRegistration.find( obj.selectors.metaContainer + '[data-ticket-id="' + ticket.ticket_id + '"]' );
			const counter = 1;

			if ( ! $ticketContainer.length ) {
				nonMetaCount += ticket.quantity;
			} else {
				metaCount += ticket.quantity;
			}

			$ticketContainer.addClass( obj.selectors.metaContainerHasTickets.className() );

			for ( let i = counter; i <= ticket.quantity; ++i ) {
				const datum = { attendee_id: i };
				try {
					$ticketContainer.append( ticketTemplate( datum ) );
				} catch ( error ) {
					// template doesn't exist - the ticket has no meta.
				}
			}
		} );

		obj.maybeShowNonMetaNotice( nonMetaCount, metaCount );
	};

	/**
	 * Maybe show non meta notice.
	 *
	 * @since 5.1.0
	 *
	 * @param {number} nonMetaCount The number of non meta tickets.
	 * @param {number} metaCount The number of meta tickets.
	 *
	 * @return {void}
	 */
	obj.maybeShowNonMetaNotice = function( nonMetaCount, metaCount ) {
		const $notice = $( '.tribe-tickets__notice--non-ar' );
		if ( 0 < nonMetaCount && 0 < metaCount ) {
			$( '#tribe-tickets__non-ar-count' ).text( nonMetaCount );
			$notice.removeClass( 'tribe-common-a11y-hidden' );
		} else {
			$notice.addClass( 'tribe-common-a11y-hidden' );
		}
	};

	/**
	 * Pre-fills the AR fields from supplied data.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object of object of the registration page container.
	 * @param {object} data Data to fill the form in with.
	 * @param {number} len Starting pointer for partial fill-ins.
	 *
	 * @return {void}
	 */
	obj.preFillMetaForm = function( $container, data, len ) {
		let length = len;

		if ( undefined === data || 0 >= data.length ) {
			return;
		}

		if ( undefined === length ) {
			length = 0;
		}

		const $tribeRegistration = $container;
		const $form = $tribeRegistration;
		const $containers = $form.find( obj.selectors.metaContainer );
		let meta = data.meta;

		if ( 0 < length ) {
			meta = meta.splice( 0, length - 1 );
		}

		$.each( meta, function( metaIndex, ticket ) {
			const $currentContainers = $containers.filter( '[data-ticket-id="' + ticket.ticket_id + '"]' );
			const $tempTextarea = $( '<textarea />' );

			if ( ! $currentContainers.length ) {
				return;
			}

			let current = 0;
			$.each( ticket.items, function( ticketIndex, datum ) {
				if ( 'object' !== typeof datum ) {
					return;
				}

				const $ticketContainers = $currentContainers.find( obj.selectors.metaItem );
				$.each( datum, function( index, value ) {
					// Set value of temporary textarea.
					$tempTextarea.html( value );

					const formattedValue = $tempTextarea.text();
					const $field = $ticketContainers.eq( current ).find( '[name*="' + index + '"]' );

					if ( ! $field.is( ':radio' ) && ! $field.is( ':checkbox' ) ) {
						$field.val( formattedValue );
					} else {
						$field.each( function() {
							const $item = $( this );

							if ( formattedValue === $item.val() ) {
								$item.prop( 'checked', true );
							}
						} );
					}

					// Populate for the birthday selects.
					if ( $field.hasClass( 'tribe-tickets__form-field--birth-value' ) ) {
						tribe.tickets.meta.populateFieldBirthday();
					}
				} );

				current++;
			} );
		} );
	};

	/**
	 * Update all the footer info.
	 *
	 * @since 5.1.0
	 *
	 * @param {object} $form The mini-cart form.
	 *
	 * @return {void}
	 */
	obj.updateFooter = function( $form ) {
		obj.updateFooterCount( $form );
		obj.updateFooterAmount( $form );
	};

	/**
	 * Adjust the footer count for +/-.
	 *
	 * @since 5.1.0
	 *
	 * @param {object} $form The mini-cart form.
	 *
	 * @return {void}
	 */
	obj.updateFooterCount = function( $form ) {
		const $field = $form.find( obj.selectors.footerQuantity );
		let footerCount = 0;
		const $quantities = $form.find( obj.selectors.itemQuantity );

		$quantities.each( function() {
			let newQuantity = parseInt( $( this ).text(), 10 );
			newQuantity = isNaN( newQuantity ) ? 0 : newQuantity;
			footerCount += newQuantity;
		} );

		if ( 0 > footerCount ) {
			return;
		}

		$field.text( footerCount );
	};

	/**
	 * Adjust the footer total/amount for +/-.
	 *
	 * @since 5.1.0
	 *
	 * @param {object} $form The mini-cart form.
	 *
	 * @return {void}
	 */
	obj.updateFooterAmount = function( $form ) {
		const $field = $form.find( obj.selectors.footerAmount );
		let footerAmount = 0;
		const $quantities = $form.find( obj.selectors.itemQuantity );
		const provider = $form.data( 'provider' );

		$quantities.each( function() {
			const $qty = $( this );
			const $price = $qty.closest( obj.selectors.item ).find( obj.selectors.itemPrice ).first( 0 );
			const $realPrice = $qty.closest( obj.selectors.item ).data( 'ticket-price' );
			let quantity = parseInt( $qty.text(), 10 );
			quantity = isNaN( quantity ) ? 0 : quantity;
			const priceVal = isNaN( $realPrice ) ? $price.text() : $realPrice.toString();
			const cost = tribe.tickets.utils.cleanNumber( priceVal, provider ) * quantity;
			footerAmount += cost;
		} );

		if ( 0 > footerAmount ) {
			return;
		}

		$field.text( tribe.tickets.utils.numberFormat( footerAmount, provider ) );
	};

	/**
	 * Pre-fill the Mini-Cart.
	 *
	 * @since 5.1.0
	 *
	 * @param {object} $form The mini-cart form.
	 * @param {object} tickets THe ticket data.
	 *
	 * @return {void}
	 */
	obj.preFillCartForm = function( $form, tickets ) {
		const provider = $form.data( 'provider' );

		$.each( tickets, function( index, value ) {
			const $item = $form.find( '[data-ticket-id="' + value.ticket_id + '"]' );

			if ( $item ) {
				const pricePer = $item.find( '.tribe-tickets__tickets-sale-price .tribe-amount' ).text();
				const realPrice = $item.data( 'ticket-price' );
				$item.find( obj.selectors.itemQuantity ).html( value.quantity );
				const price = isNaN( realPrice ) ? pricePer : realPrice.toString();
				const totalPrice = value.quantity * tribe.tickets.utils.cleanNumber( price, provider );
				const formattedPrice = tribe.tickets.utils.numberFormat( totalPrice, provider );
				$item.find( obj.selectors.itemTotal ).html( formattedPrice );
			}
		} );

		obj.updateFooter( $form );
	};

	/**
	 * Handle AR submission.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object of object of the registration page container.
	 *
	 * @return {void}
	 */
	obj.bindSubmit = function( $container ) {
		const $button = $container.find( obj.selectors.checkoutButton );
		const ticketProvider = $container.data( 'provider' );

		/**
		 * Handle AR submission.
		 *
		 * @since 5.1.0
		 */
		$button.on(
			'click', // @todo: Fix this, handle submit.
			function( e ) {
				e.preventDefault();
				const $metaForm = $container.find( obj.selectors.metaForm );
				const $metaFormItems = $metaForm.find( obj.selectors.metaItem );
				const $errorNotice = $container.find( '.tribe-tickets__notice--error' );
				const isValidForm = tribe.tickets.meta.validateForm( $metaForm );
				tribe.tickets.loader.show( $document );

				if ( ! isValidForm[ 0 ] ) {
					$( [ document.documentElement, document.body ] ).animate(
						{ scrollTop: $( '.tribe-tickets__registration' ).offset().top },
						'slow'
					);

					$( '.tribe-tickets__notice--error__count' ).text( isValidForm[ 1 ] );
					$errorNotice.show();
					tribe.tickets.loader.hide( $document );
					return false;
				}

				$errorNotice.hide();

				// Save meta and cart.
				const params = {
					tribe_tickets_provider: obj.commerceSelector[ ticketProvider ],
					tribe_tickets_tickets: obj.getTicketsForSave(),
					tribe_tickets_meta: tribe.tickets.data.getMetaForSave( $metaFormItems ),
				};

				$( '#tribe_tickets_ar_data' ).val( JSON.stringify( params ) );

				// Submit the form.
				$( obj.selectors.form ).submit();
			}
		);
	};

	/**
	 * Binds events for container.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object of object of the registration page container.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) {
		$document.trigger( 'beforeSetup.tribeTicketsRegistrationPage', [ $container ] );

		obj.bindSubmit( $container );

		$document.trigger( 'afterSetup.tribeTicketsRegistrationPage', [ $container ] );
	};

	/**
	 * Init the tickets registration script
	 *
	 * @since 5.1.0
	 */
	obj.ready = function() {
		const $tribeRegistration = $( obj.selectors.container );

		$tribeRegistration.each( function( index, block ) {
			obj.initFormPreFills( $( block ) );
			obj.bindEvents( $( block ) );
		} );
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.tickets.registration );
/* eslint-enable max-len */
