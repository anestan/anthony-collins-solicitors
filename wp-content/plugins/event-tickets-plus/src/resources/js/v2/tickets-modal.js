/* global tribe, jQuery, TribeTicketOptions */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.1.0
 *
 * @type   {Object}
 */
tribe.tickets = tribe.tickets || {};
tribe.dialogs = tribe.dialogs || {};
tribe.dialogs.events = tribe.dialogs.events || {};

/**
 * Configures ET Modal Object in the Global Tribe variable
 *
 * @since 5.1.0
 *
 * @type   {Object}
 */
tribe.tickets.modal = {};

/**
 * Initializes in a Strict env the code that manages the plugin modal.
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

	/*
	 * AR Cart Modal Selectors.
	 *
	 * Note: some of these have the modal class as well, as the js can
	 * pick up the class from elsewhere in the DOM and grab the wrong data.
	 *
	 * @since 5.1.0
	 */
	obj.selectors = {
		container: '.tribe-modal__wrapper--ar',
		form: '#tribe-tickets__modal-form',
		cartForm: '.tribe-modal__wrapper--ar #tribe-modal__cart',
		itemRemove: '.tribe-tickets__tickets-item-remove',
		itemTotal: '.tribe-tickets__tickets-item-total .tribe-amount',
		itemError: '.tribe-tickets__attendee-tickets-item--has-error',
		metaForm: '.tribe-modal__wrapper--ar #tribe-modal__attendee-registration',
		metaContainer: '.tribe-tickets__attendee-tickets-container',
		metaContainerHasTickets: '.tribe-tickets__attendee-tickets-container--has-tickets',
		metaItem: '.tribe-tickets__attendee-tickets-item',
		submit: '.tribe-tickets__attendee-tickets-submit',
		hidden: '.tribe-common-a11y-hidden',
		validationNotice: '.tribe-tickets__notice--error',
		ticketInCartNotice: '#tribe-tickets__notice__tickets-in-cart',
		noticeNonAr: '.tribe-tickets__notice--non-ar',
		noticeNonArCount: '.tribe-tickets__non-ar-count',
		noticeNonArSingular: '.tribe-tickets__notice--non-ar-singular',
		noticeNonArPlural: '.tribe-tickets__notice--non-ar-plural',
	};

	/**
	 * Appends AR fields when modal cart quantities are changed.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $form The form we are updating.
	 */
	obj.appendARFields = function( $form ) {
		$form.find( tribe.tickets.block.selectors.item ).each(
			function() {
				const $cartItem = $( this );

				if ( $cartItem.is( ':visible' ) ) {
					const ticketID = $cartItem.closest( tribe.tickets.block.selectors.item ).data( 'ticket-id' );
					const $ticketContainer = $( obj.selectors.metaForm ).find( obj.selectors.metaContainer + '[data-ticket-id="' + ticketID + '"]' );

					// Ticket does not have meta - no need to jump through hoops (and throw errors).
					if ( ! $ticketContainer.length ) {
						return;
					}

					const $existing = $ticketContainer.find( obj.selectors.metaItem );
					const qty = tribe.tickets.block.getQty( $cartItem );

					if ( 0 >= qty ) {
						$ticketContainer.removeClass( obj.selectors.metaContainerHasTickets.className() );
						$ticketContainer.find( obj.selectors.metaItem ).remove();

						return;
					}

					if ( $existing.length > qty ) {
						const removeCount = $existing.length - qty;
						$ticketContainer.find( obj.selectors.metaItem + ':nth-last-child( -n+' + removeCount + ' )' ).remove();
					} else if ( $existing.length < qty ) {
						const ticketTemplate = window.wp.template( 'tribe-registration--' + ticketID );
						const counter = 0 < $existing.length ? $existing.length + 1 : 1;

						$ticketContainer.addClass( obj.selectors.metaContainerHasTickets.className() );

						for ( let i = counter; i <= qty; i++ ) {
							const data = { attendee_id: i };

							$ticketContainer.append( ticketTemplate( data ) );
							obj.maybeHydrateAttendeeBlockFromLocal( $existing.length );
						}
					}
				}
			}
		);

		obj.maybeShowNonMetaNotice( $form );
		$document.trigger( 'tribe-ar-fields-appended' );
	};

	/**
	 * Shows/hides the non-ar notice based on the number of tickets passed.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $form The form we're updating.
	 */
	obj.maybeShowNonMetaNotice = function( $form ) {
		let nonMetaCount = 0;
		let metaCount = 0;
		const $cartItems = $form.find( tribe.tickets.block.selectors.item ).filter(
			function() {
				return $( this ).find( tribe.tickets.block.selectors.itemQuantityInput ).val() > 0;
			}
		);

		if ( ! $cartItems.length ) {
			return;
		}

		$cartItems.each(
			function() {
				const $cartItem = $( this );

				if ( ! $cartItem.is( ':visible' ) ) {
					return;
				}

				const ticketID = $cartItem.closest( tribe.tickets.block.selectors.item ).data( 'ticket-id' );
				const $ticketContainer = $( obj.selectors.metaForm ).find( obj.selectors.metaContainer + '[data-ticket-id="' + ticketID + '"]' );

				// Ticket does not have meta - no need to jump through hoops ( and throw errors ).
				if ( ! $ticketContainer.length ) {
					nonMetaCount += tribe.tickets.block.getQty( $cartItem );
				} else {
					metaCount += tribe.tickets.block.getQty( $cartItem );
				}
			}
		);

		const $notice = $( obj.selectors.noticeNonAr );
		const $noticeSingular = $( obj.selectors.noticeNonArSingular );
		const $noticePlural = $( obj.selectors.noticeNonArPlural );
		const $title = $( '.tribe-tickets__attendee-tickets-title' );

		$notice.addClass( obj.selectors.hidden.className() );

		// If there are no non-meta tickets, we don't need the notice.
		// Likewise, if there are no tickets with meta the notice seems redundant.
		if ( 0 < nonMetaCount && 0 < metaCount ) {
			$notice.find( obj.selectors.noticeNonArCount ).text( nonMetaCount );
			if ( 1 < nonMetaCount ) {
				$noticePlural.removeClass( obj.selectors.hidden.className() );
			} else {
				$noticeSingular.removeClass( obj.selectors.hidden.className() );
			}

			$title.show();
		} else {
			$title.hide();
		}
	};

	/**
	 * Pre-fill tickets block from cart.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object of object of the tickets container.
	 *
	 * @return {void}
	 */
	obj.preFillTicketsBlock = function( $container ) {
		$.when(
			tribe.tickets.data.getData( true )
		).then(
			function( data ) {
				const tickets = data.tickets;

				if ( tickets.length ) {
					let $eventCount = 0;

					tickets.forEach( function( ticket ) {
						const $ticketRow = $container.find( '.tribe-tickets__tickets-item[data-ticket-id="' + ticket.ticket_id + '"]' );
						if ( 'true' === $ticketRow.attr( 'data-available' ) ) {
							const $field = $ticketRow.find( tribe.tickets.block.selectors.itemQuantityInput );
							const $optOut = $ticketRow.find( tribe.tickets.block.selectors.itemOptOutInput + ticket.ticket_id + '--modal' );
							if ( $field.length ) {
								$field.val( ticket.quantity );
								$field.trigger( 'change' );
								$eventCount += ticket.quantity;
								if ( 1 === parseInt( ticket.optout, 10 ) ) {
									$optOut.prop( 'checked', 'true' );
								}
							}
						}
					} );

					if ( 0 < $eventCount ) {
						$container.find( obj.selectors.ticketInCartNotice ).fadeIn();
					}
				}
			},
			function() {
				const $errorNotice = $container.find( obj.selectors.ticketInCartNotice );
				$errorNotice
					.removeClass( 'tribe-tickets__notice--barred tribe-tickets__notice--barred-left' )
					.addClass( obj.selectors.validationNotice.className() );
				$errorNotice.find( '.tribe-tickets-notice__title' ).text( TribeMessages.api_error_title );
				$errorNotice.find( '.tribe-tickets-notice__content' ).text( TribeMessages.connection_error );
				$errorNotice.fadeIn();
			}
		);
	};

	/**
	 * Pre-fills the modal AR fields from supplied data.
	 *
	 * @since 5.1.0
	 *
	 * @param {array} meta Data to fill the form in with.
	 */
	obj.preFillModalMetaForm = function( meta ) {
		if ( undefined === meta || 0 >= meta.length ) {
			return;
		}

		const $form = $( obj.selectors.metaForm );
		const $containers = $form.find( obj.selectors.metaContainer );

		$.each( meta, function( idx, ticket ) {
			let current = 0;
			const $currentContainers = $containers.find( obj.selectors.metaItem ).filter( '[data-ticket-id="' + ticket.ticket_id + '"]' );

			if ( ! $currentContainers.length ) {
				return;
			}

			$.each( ticket.items, function( indx, data ) {
				if ( 'object' !== typeof data ) {
					return;
				}

				$.each( data, function( index, value ) {
					const $field = $currentContainers.eq( current ).find( '[name*="' + index + '"]' );

					if ( ! $field.is( ':radio' ) && ! $field.is( ':checkbox' ) ) {
						$field.val( value );
					} else {
						$field.each( function() {
							const $item = $( this );

							if ( value === $item.val() ) {
								$item.prop( 'checked', true );
							}
						} );
					}
				} );

				current++;
			} );
		} );
	};

	/**
	 * Pre-fill the Cart.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $form The form we're updating.
	 *
	 * @return {void}
	 */
	obj.preFillModalCartForm = function( $form ) {
		$form.find( tribe.tickets.block.selectors.item ).hide();

		const $ticketsBlock = $form.closest( tribe.tickets.block.selectors.container );
		const $items = $ticketsBlock.find( tribe.tickets.block.selectors.item );

		// Override the data with what's in the tickets block.
		$.each( $items, function( index, item ) {
			const $blockItem = $( item );
			const $item = $form.find( '[data-ticket-id="' + $blockItem.attr( 'data-ticket-id' ) + '"]' );

			if ( $item ) {
				const quantity = $blockItem.find( tribe.tickets.block.selectors.itemQuantityInput ).val();
				if ( 0 < quantity ) {
					$item.fadeIn();
				}
			}
		} );

		obj.appendARFields( $form );
	};

	/**
	 * Init the form pre-fills (Cart and AR forms).
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $form The form we're updating.
	 *
	 * @return {void}
	 */
	obj.initModalFormPreFills = function( $form ) {
		const $ticketBlock = $form.closest( tribe.tickets.block.selectors.container );
		const postId = $form.data( 'post-id' );

		tribe.tickets.loader.show( $form );

		$.when(
			tribe.tickets.data.getData( false, postId )
		).then(
			function( data ) {
				const $modalForm = $ticketBlock.find( obj.selectors.cartForm );

				obj.preFillModalCartForm( $modalForm );

				if ( data.meta ) {
					$.each( data.meta, function( ticket ) {
						const $matches = $ticketBlock.find( '[data-ticket-id="' + ticket.ticket_id + '"]' );

						if ( $matches.length ) {
							obj.preFillModalMetaForm( data.meta );
							return;
						}
					} );
				}

				// If we didn't get meta from the API, let's fill with sessionStorage.
				const local = tribe.tickets.data.getLocal();

				if ( local.meta ) {
					obj.preFillModalMetaForm( local.meta );
				}

				window.setTimeout( tribe.tickets.loader.hide, 500, $form );
			}
		);

		tribe.tickets.loader.hide( $form );
	};

	/**
	 * Attempts to hydrate a dynamically-created attendee form "block" from sessionStorage data.
	 *
	 * @since 5.1.0
	 *
	 * @param {number} length The "skip" index.
	 *
	 * @return {void}
	 */
	obj.maybeHydrateAttendeeBlockFromLocal = function( length ) {
		$.when(
			tribe.tickets.data.getData()
		).then(
			function( data ) {
				if ( ! data.meta ) {
					return;
				}

				const cartSkip = data.meta.length;
				if ( length < cartSkip ) {
					obj.preFillModalMetaForm( data.meta );
					return;
				}
				const $attendeeForm = $( obj.selectors.metaForm );
				const $newBlocks = $attendeeForm.find( obj.selectors.metaItem ).slice( length - 1 );

				if ( ! $newBlocks ) {
					return;
				}

				$newBlocks.find( tribe.tickets.meta.selectors.formFieldInput ).each(
					function() {
						const $this = $( this );
						const name = $this.attr( 'name' );
						const storedVal = data[ name ];

						if ( storedVal ) {
							$this.val( storedVal );
						}
					}
				);
			}
		);
	};

	/**
	 * Handle document key press to submit the modal form.
	 *
	 * @since 5.1.0
	 *
	 * @return {void}
	 */
	obj.bindDocumentKeypress = function() {
		$document.on(
			'keypress',
			obj.selectors.form,
			function( e ) {
				if ( 13 === e.keyCode ) {
					const $form = $( e.target ).closest( obj.selectors.form );
					// Ensure we're on the modal form.
					if ( 'undefined' === $form ) {
						return;
					}

					e.preventDefault();
					e.stopPropagation();

					// Submit to cart. This will trigger validation as well.
					$form.find( '[name="cart-button"]' ).click();
				}
			}
		);
	};

	/**
	 * Unbind Modal submission.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container The container where we want to bind the modal submit.
	 *
	 * @return {void}
	 */
	obj.unbindModalSubmit = function( $container ) {
		const $submitButton = $container.find( obj.selectors.submit );

		$submitButton.off();
	};

	/**
	 * Handle Modal submission.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container The container where we want to bind the modal submit.
	 *
	 * @return {void}
	 */
	obj.bindModalSubmit = function( $container ) {
		const $submitButton = $container.find( obj.selectors.submit );
		const postId = $container.data( 'post-id' );

		$submitButton.on(
			'click', // @todo: Fix this, handle submit.
			function( e ) {
				e.preventDefault();
				const $button = $( this );
				const $form = $( obj.selectors.form );
				const $metaForm = $( obj.selectors.metaForm );
				const $metaFormItems = $metaForm.find( obj.selectors.metaItem );
				const isValidForm = tribe.tickets.meta.validateForm( $metaForm );
				const $errorNotice = $( obj.selectors.validationNotice );
				const buttonText = $button.attr( 'name' );
				const provider = $form.data( 'provider' );

				tribe.tickets.loader.show( $form );

				if ( ! isValidForm[ 0 ] ) {
					$errorNotice.find( '.tribe-tickets-notice__title' ).text( TribeMessages.validation_error_title );
					$errorNotice.find( 'p' ).html( TribeMessages.validation_error );
					$( obj.selectors.validationNotice + '__count' ).text( isValidForm[ 1 ] );
					$errorNotice.show();
					tribe.tickets.loader.hide( $form );
					document.getElementById( 'tribe-tickets__notice-modal-attendee' )
						.scrollIntoView(
							{
								behavior: 'smooth',
								block: 'start',
							}
						);
					return false;
				}

				$errorNotice.hide();

				// Default to checkout.
				let action = TribeTicketsURLs.checkout[ provider ];

				if ( -1 !== buttonText.indexOf( 'cart' ) ) {
					action = TribeTicketsURLs.cart[ provider ];
				}
				$( obj.selectors.form ).attr( 'action', action );

				// Save meta and cart.
				const params = {
					tribe_tickets_provider: tribe.tickets.block.commerceSelector[ provider ],
					tribe_tickets_tickets: tribe.tickets.block.getTicketsForCart( $form ),
					tribe_tickets_meta: tribe.tickets.data.getMetaForSave( $metaFormItems ),
					tribe_tickets_post_id: postId,
				};

				// Set parameters in the data input.
				const $dataInput = $container.find( '[name="tribe_tickets_ar_data"]' );
				$dataInput.val( JSON.stringify( params ) );

				// Set a flag to clear sessionStorage
				window.tribe.tickets.modal_redirect = true;
				tribe.tickets.data.clearLocal();

				// Submit the form.
				$form.submit();
			}
		);
	};

	/**
	 * Unbind remove Item from Cart Modal.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container The container where we want to bind the modal submit.
	 *
	 * @return {void}
	 */
	obj.unbindModalItemRemove = function( $container ) {
		const $itemRemove = $container.find( obj.selectors.itemRemove );

		$itemRemove.off();
	};

	/**
	 * Remove Item from Cart Modal.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container The container where we want to bind the modal submit.
	 *
	 * @return {void}
	 */
	obj.bindModalItemRemove = function( $container ) {
		const $itemRemove = $container.find( obj.selectors.itemRemove );

		$itemRemove.on(
			'click',
			function( e ) {
				e.preventDefault();

				const ticket = {};
				const $cart = $( this ).closest( 'form' );
				const $cartItem = $( this ).closest( tribe.tickets.block.selectors.item );

				$cartItem.find( tribe.tickets.block.selectors.itemQuantity ).val( 0 );
				$cartItem.fadeOut();

				ticket.id = $cartItem.data( 'ticketId' );
				ticket.qty = 0;
				$cartItem.find( obj.selectors.itemQuantityInput ).val( ticket.qty );
				ticket.price = tribe.tickets.block.getPrice( $cartItem );
				obj.updateItemTotal( ticket.qty, ticket.price, $cartItem );

				$( obj.selectors.metaContainer + '[data-ticket-id="' + ticket.id + '"]' )
					.removeClass( obj.selectors.metaContainerHasTickets.className() )
					.find( obj.selectors.metaItem ).remove();

				// Short delay to ensure the fadeOut has finished.
				window.setTimeout( obj.maybeShowNonMetaNotice, 500, $cart );

				// Close the modal if we remove the last item
				// Again, short delay to ensure the fadeOut has finished.
				window.setTimeout(
					function() {
						const $items = $cart.find( tribe.tickets.block.selectors.item ).filter( ':visible' );

						// Update the form totals.
						tribe.tickets.block.updateFormTotals( $cart );

						// Maybe show non meta notice.
						obj.maybeShowNonMetaNotice( $cart );

						if ( 0 >= $items.length ) {
							// Get the object ID
							const id = $( tribe.tickets.block.selectors.blockSubmit ).attr( 'data-content' );
							const result = 'dialog_obj_' + id.substring( id.lastIndexOf( '-' ) + 1 );

							// Close the dialog.
							window[ result ].hide();
							tribe.tickets.utils.disable( $( tribe.tickets.block.selectors.submit ), false );
						}
					},
					500
				);
			}
		);
	};

	/**
	 * Possibly Update an Items Qty and always update the Total.
	 *
	 * @since 5.1.0
	 *
	 * @param {int}    id The id of the ticket/product.
	 * @param {jQuery} $modalCartItem The cart item to update.
	 * @param {jQuery} $blockCartItem The optional ticket block cart item.
	 *
	 * @returns {object} Returns the updated item for chaining.
	 */
	obj.updateItem = function( id, $modalCartItem, $blockCartItem ) {
		const item = {};
		item.id = id;

		if ( ! $blockCartItem ) {
			item.qty = tribe.tickets.block.getQty( $modalCartItem );
			item.price = tribe.tickets.block.getPrice( $modalCartItem );
		} else {
			item.qty = tribe.tickets.block.getQty( $blockCartItem );
			item.price = tribe.tickets.block.getPrice( $modalCartItem );

			$modalCartItem.find( tribe.tickets.block.selectors.itemQuantityInput ).val( item.qty ).trigger( 'change' );

			// We force new DOM queries here to be sure we pick up dynamically generated items.
			const optOutSelector = tribe.tickets.block.selectors.itemOptOutInput + $blockCartItem.data( 'ticket-id' );
			const optOutSelectorModal = tribe.tickets.block.selectors.itemOptOutInput + $blockCartItem.data( 'ticket-id' ) + '--modal';

			item.$optOut = $( optOutSelector );
			const $optOutInput = $( optOutSelectorModal );
			if ( item.$optOut.length && item.$optOut.is( ':checked' ) ) {
				$optOutInput.val( '1' ).prop( 'checked' , true );
			} else {
				$optOutInput.val( '0' ).prop( 'checked' , false );
			}
		}

		obj.updateItemTotal( item.qty, item.price, $modalCartItem );

		return item;
	};

	/**
	 * Update the total price for the Given Cart Item.
	 *
	 * @since 5.1.0
	 *
	 * @param {number} qty The quantity.
	 * @param {number} price The price.
	 * @param {object} $cartItem The cart item to update.
	 *
	 * @returns {string} - Formatted currency string.
	 */
	obj.updateItemTotal = function( qty, price, $cartItem ) {
		const $form = $cartItem.closest( 'form' );
		const provider = tribe.tickets.block.getTicketsBlockProvider( $form );
		const format = tribe.tickets.utils.getCurrencyFormatting( provider );
		const totalForItem = ( qty * price ).toFixed( format.number_of_decimals );

		const $field = $cartItem.find( obj.selectors.itemTotal );

		$field.text( tribe.tickets.utils.numberFormat( totalForItem, provider ) );

		return totalForItem;
	};

	/**
	 * Init the tickets block pre-fill.
	 *
	 * @param {jQuery} $container jQuery object of object of the tickets container.
	 *
	 * @since 5.1.0
	 */
	obj.initPreFill = function( $container ) {
		obj.preFillTicketsBlock( $container );
	};

	/**
	 * Hook actions to the update form totals ET tickets block function.
	 *
	 * @since 5.1.0
	 *
	 * @param {event} event The event.
	 * @param {jQuery} $form The form we're manipulating.
	 */
	obj.bindAfterUpdateFormTotals = function( event, $form ) {
		obj.appendARFields( $form );
	};

	/**
	 * Hook actions to the update form totals ET tickets block function.
	 *
	 * @since 5.1.0
	 *
	 * @param {event} event The event.
	 * @param {jQuery} $input The input we're manipulating.
	 */
	obj.bindModalQuantityChange = function( event, $input ) {
		const $modalForm = $input.closest( obj.selectors.cartForm );

		if ( $modalForm.length ) {
			const $item = $input.closest( tribe.tickets.block.selectors.item );
			obj.updateItemTotal(
				tribe.tickets.block.getQty( $item ),
				tribe.tickets.block.getPrice( $item ),
				$item
			);
		}
	};

	/**
	 * Hook actions to the before ticket submit from the tickets block.
	 *
	 * @since 5.1.0
	 *
	 * @param {event} event The event.
	 * @param {jQuery} $form The form we're manipulating.
	 * @param {object} params The object with the parameters.
	 */
	obj.bindBeforeTicketsSubmit = function( event, $form, params ) {
		// @todo: See if we can make it relative to the form instead of using IDs
		$form.find( '#tribe_tickets_block_ar_data' ).val( JSON.stringify( params ) );
	};

	/**
	 * Handler for when the modal is closed.
	 *
	 * @since 5.1.0
	 *
	 * @return {void}
	 */
	obj.bindModalClose = function() {
		$( tribe.dialogs.events ).on(
			'tribe_dialog_close_ar_modal',
			function( e, dialogEl ) {
				const $modal = $( dialogEl );
				const $form = $modal.find( obj.selectors.form );
				const postId = $form.data( 'post-id' );
				const $modalCart = $modal.find( obj.selectors.cartForm );

				// Handles storing data to local storage
				tribe.tickets.data.storeLocal( postId );

				tribe.tickets.block.unbindTicketsAddRemove( $modalCart );
				tribe.tickets.block.unbindTicketsQuantityInput( $modalCart );
				tribe.tickets.block.unbindDescriptionToggle( $modalCart );
				obj.unbindOptOutChange( $form );
				obj.unbindModalSubmit( $form );
				obj.unbindModalItemRemove( $form );
			}
		);
	};

	/**
	 * Handler for when "Get Tickets" is clicked, update the modal.
	 *
	 * @since 5.1.0
	 *
	 * @return {void}
	 */
	obj.bindModalOpen = function() {
		$( tribe.dialogs.events ).on(
			'tribe_dialog_show_ar_modal',
			function( e, dialogEl ) {
				const $modal = $( dialogEl );
				const $form = $modal.find( obj.selectors.form );
				const $modalCart = $modal.find( obj.selectors.cartForm );
				const $tribeTicket = $document.find( tribe.tickets.block.selectors.form ).filter( '[data-post-id="' + $form.data( 'postId' ) + '"]' );
				const $cartItems = $tribeTicket.find( tribe.tickets.block.selectors.item );

				// Show the loader.
				tribe.tickets.loader.show( $form );

				$cartItems.each(
					function() {
						const $blockCartItem = $( this );
						const id = $blockCartItem.data( 'ticket-id' );
						const $modalCartItem = $modalCart.find( '[data-ticket-id="' + id + '"]' );

						if ( 0 === $modalCartItem.length || 0 === $blockCartItem.length ) {
							return;
						}

						obj.updateItem( id, $modalCartItem, $blockCartItem );
					}
				);

				// Bind tickets block actions.
				tribe.tickets.block.bindTicketsAddRemove( $modalCart );
				tribe.tickets.block.bindTicketsQuantityInput( $modalCart );
				tribe.tickets.block.bindDescriptionToggle( $modalCart );

				obj.bindModalSubmit( $form );
				obj.bindModalItemRemove( $form );

				obj.initModalFormPreFills( $form );

				tribe.tickets.block.updateFormTotals( $modalCart );

				obj.bindOptOutChange( $form );
				// Hide the loader.
				tribe.tickets.loader.hide( $form );
			}
		);
	};

	/**
	 * Hook actions to the afterSetup of the tickets block.
	 *
	 * @since 5.1.0
	 *
	 * @param {event} event The event.
	 * @param {jQuery} $container The container of the tickets block.
	 */
	obj.bindAfterSetupTicketsBlock = function( event, $container ) {
		if ( TribeTicketOptions.ajax_preload_ticket_form ) {
			tribe.tickets.loader.show( $container );
			obj.initPreFill( $container );
		}
	};

	/**
	 * Maybe submit Tickets Block if no tickets with AR fields are in cart.
	 *
	 * @since 5.2.1
	 *
	 * @return {void}
	 */
	obj.maybeSubmitBlockIfNoArTicketInCart = function() {
		if ( !! TribeTicketsModal.ShowIfNoTicketWithArInCart ) {
			return;
		}

		$document.find( tribe.tickets.block.selectors.blockSubmit ).on(
			'click',
			function( e ) {
				const $button = $( this );
				const $form = $( this ).closest( 'form' );
				const $cartItems = $form.find( tribe.tickets.block.selectors.item );
				const dialogId = $button.data( 'js' ).replace( 'trigger-dialog-', '' );

				let ticketsWithArMetaInCart = 0;

				$cartItems.each(
					function() {
						const $blockCartItem = $( this );

						if ( ! $blockCartItem.is( ':visible' ) ) {
							return;
						}

						const qtyInCart = tribe.tickets.block.getQty( $blockCartItem );
						const hasArFields = $blockCartItem.data( 'ticket-ar-fields' );

						if ( hasArFields && 0 < qtyInCart ) {
							ticketsWithArMetaInCart++;
						}
					}
				);

				// If there are tickets with AR meta in cart.
				if ( ! ticketsWithArMetaInCart ) {
					// Destroy the dialog so it doesn't trigger the opening.
					let dialogToDestroy;

					// Iterate and find the dialog to destroy.
					tribe.dialogs.dialogs.forEach( function( dialog, index ) {
						if ( dialog.id === dialogId ) {
							dialogToDestroy = index;
						}
					} );

					// If the dialog was found, destroy tit.
					if ( ! isNaN( dialogToDestroy ) ) {
						tribe.dialogs.dialogs[ dialogToDestroy ].a11yInstance.destroy();
					}

					// Submit the tickets block form.
					tribe.tickets.block.ticketsSubmit( $form );
				}
			}
		);
	};

	/**
	 * Add change binding to Opt Out checkbox in AR Modal. Will toggle the check/uncheck of the Opt Out checkbox outside AR Modal.
	 *
	 * @since 5.2.9
	 *
	 * @param {jQuery} $form The form we're manipulating.
	 *
	 * @return {void}
	 */
	obj.bindOptOutChange = function( $form ) {
		const $optOutInput = $form.find( '[id^=tribe-tickets-attendees-list-optout-]' );
		$optOutInput.change( function() {
			const $this = $( this );
			const mainOptOutCheckboxid = $this.attr( 'id' );
			const $mainOptOutCheckbox = $( '#' + $this.attr( 'id' ).substr(0, mainOptOutCheckboxid.indexOf( '--' ) ) );
			if ( $this.is( ':checked' ) ) {
				$mainOptOutCheckbox.val( '1' ).prop( 'checked' , true );
			} else {
				$mainOptOutCheckbox.val( '0' ).prop( 'checked' , false );
			}
		} );
	}

	/**
	 * Unbind Opt Out change.
	 *
	 * @since 5.2.9
	 *
	 * @param {jQuery} $form The form we're manipulating.
	 *
	 * @return {void}
	 */
	obj.unbindOptOutChange = function( $form ) {
		const $optOutCheckbox = $form.find( '[id^=tribe-tickets-attendees-list-optout-]' );
		$optOutCheckbox.unbind( 'change' );
	}

	/**
	 * Handles the initialization of the scripts when document is ready.
	 *
	 * @since 5.1.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on(
			'afterUpdateFormTotals.tribeTicketsBlock',
			obj.bindAfterUpdateFormTotals
		);

		$document.on(
			'afterTicketsAddRemove.tribeTicketsBlock',
			obj.bindModalQuantityChange
		);

		$document.on(
			'afterTicketsQuantityChange.tribeTicketsBlock',
			obj.bindModalQuantityChange
		);

		$document.on(
			'beforeTicketsSubmit.tribeTicketsBlock',
			obj.bindBeforeTicketsSubmit
		);

		$document.on(
			'afterSetup.tribeTicketsBlock',
			obj.bindAfterSetupTicketsBlock
		);

		obj.maybeSubmitBlockIfNoArTicketInCart();
		obj.bindModalOpen();
		obj.bindModalClose();
		obj.bindDocumentKeypress();
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.tickets.modal );
