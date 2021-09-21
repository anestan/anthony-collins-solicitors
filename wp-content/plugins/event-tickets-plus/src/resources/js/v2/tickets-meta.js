/* global tribe, jQuery */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.0.0
 *
 * @type {Object}
 */
tribe.tickets = tribe.tickets || {};

/**
 * Configures ET meta fields in the Global Tribe variable
 *
 * @since 5.0.0
 *
 * @type {Object}
 */
tribe.tickets.meta = {};

/**
 * Initializes in a Strict env the code that manages the RSVP block.
 *
 * @since 5.0.0
 *
 * @param  {Object} $   jQuery
 * @param  {Object} obj tribe.tickets.meta
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 5.0.0
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		formAttendeeTicketsWrapper: '.tribe-tickets__attendee-tickets',
		formAttendeeTickets: '.tribe-tickets__attendee-tickets-container',
		formAttendeeTicketsItem: '.tribe-tickets__attendee-tickets-item',
		formAttendeeTicketsItemError: '.tribe-tickets__attendee-tickets-item--has-error',
		formAttendeeTicketsItemFocus: '.tribe-tickets__attendee-tickets-item--has-focus',
		formField: '.tribe-tickets__form-field',
		formFieldRequired: '.tribe-tickets__form-field--required',
		formFieldUnique: '.tribe-tickets__form-field--unique',
		formFieldText: '.tribe-tickets__form-field--text',
		formFieldEmail: '.tribe-tickets__form-field--email',
		formFieldInput: '.tribe-tickets__form-field-input',
		formFieldInputError: '.tribe-tickets__form-field-input--error',
		formFieldInputHelper: '.tribe-tickets__form-field-input-helper',
		formFieldInputHelperError: '.tribe-tickets__form-field-input-helper--error',
		formFieldInputCheckboxRadioGroup: '.tribe-common-form-control-checkbox-radio-group',
		formFieldInputCheckbox: {
			container: '.tribe-tickets__form-field--checkbox',
			checkbox: '.tribe-tickets__form-field-input--checkbox',
		},
		formFieldInputBirthday: {
			container: '.tribe-tickets__form-field--birth',
			select: '.tribe-tickets__form-field--birth select',
			day: '.tribe-tickets__form-field--birth-day',
			month: '.tribe-tickets__form-field--birth-month',
			year: '.tribe-tickets__form-field--birth-year',
			value: '.tribe-tickets__form-field--birth-value',
		},
		hiddenElement: '.tribe-common-a11y-hidden',
	};

	/**
	 * Validates the entire meta form.
	 * Adds errors to the top of the modal.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $form jQuery object that is the form we are validating.
	 *
	 * @returns {array} If the form validates.
	 */
	obj.validateForm = function( $form ) {
		const $attendeeTickets = $form.find( obj.selectors.formAttendeeTicketsItem );
		let formValid = true;
		let invalidTickets = 0;

		$document.trigger( 'beforeValidateForm.tribeTicketsMeta', [ $form ] );

		$attendeeTickets.each(
			function() {
				const $attendeeTicket = $( this );
				const validAttendeeTicket = obj.validateAttendeeTicket( $attendeeTicket );

				if ( ! validAttendeeTicket ) {
					invalidTickets++;
					formValid = false;
				}
			}
		);

		$document.trigger( 'afterValidateForm.tribeTicketsMeta', [ $form, formValid, invalidTickets ] );

		return [ formValid, invalidTickets ];
	};

	/**
	 * Validates and adds/removes error classes from a ticket meta block.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $container jQuery object that is the block we are validating.
	 *
	 * @returns {boolean} True if all fields validate, false otherwise.
	 */
	obj.validateAttendeeTicket = function( $container ) {
		const $fields = $container.find( obj.selectors.formFieldInput );
		let isValid = true;

		$document.trigger( 'beforeValidateAttendeeTicket.tribeTicketsMeta', [ $container ] );

		$fields.each(
			function() {
				const $field = $( this );
				const isValidField = obj.validateField( $field[ 0 ] );

				if ( ! isValidField ) {
					isValid = false;
				}
			}
		);

		if ( isValid ) {
			$container.removeClass( obj.selectors.formAttendeeTicketsItemError.className() );
		} else {
			$container.addClass( obj.selectors.formAttendeeTicketsItemError.className() );
		}

		$document.trigger( 'afterValidateAttendeeTicket.tribeTicketsMeta', [ $container, isValid ] );

		return isValid;
	};

	/**
	 * Validate Checkbox/Radio group.
	 * We operate under the assumption that you must check _at least_ one,
	 * but not necessarily all.
	 *
	 * @since 5.0.0
	 *
	 * @param {jQuery} $group The jQuery object for the checkbox group.
	 *
	 * @return {boolean} If the input group is valid.
	 */
	obj.validateCheckboxRadioGroup = function( $group ) {
		const checked  = $group.find( 'input:checked' ).length;
		const required = $group.find( 'input:required' ).length;

		// the group is valid if there are no required.
		// or if it is required and there's at least one checked.
		const isValid = ! required || ( required && checked );

		return !! isValid;
	};

	/**
	 * Check if it's the birthday meta field.
	 *
	 * @since 5.0.0
	 *
	 * @param {jQuery} $input jQuery object of the input.
	 *
	 * @return {boolean} If the field is valid.
	 */
	obj.isFieldBirthday = function( $input ) {
		return $input.hasClass( obj.selectors.formFieldInputBirthday.value.className() );
	};

	/**
	 * Validates the birthday field.
	 *
	 * @since 5.0.0
	 *
	 * @param {jQuery} $input jQuery object of the input.
	 *
	 * @return {boolean} If the field is valid.
	 */
	obj.validateFieldBirthday = function( $input ) {
		const wrapper = $input.closest( obj.selectors.formFieldInputBirthday.container );
		const day = wrapper.find( obj.selectors.formFieldInputBirthday.day );
		const month = wrapper.find( obj.selectors.formFieldInputBirthday.month );
		const year = wrapper.find( obj.selectors.formFieldInputBirthday.year );
		let isValidField = true;

		if ( ! day.prop( 'required' ) && ! month.prop( 'required' ) && ! year.prop( 'required' ) ) {
			return isValidField;
		}

		[ day, month, year ].forEach( function( el ) {

			// Check if given value is a positive number, even if it's a string
			if ( isNaN( parseInt( el.val() ) ) || parseInt( el.val() ) <= 0 ) {
				el.addClass( obj.selectors.formFieldInputError.className() );

				isValidField = false;
			} else {
				el.removeClass( obj.selectors.formFieldInputError.className() );
			}
		} );

		return isValidField;
	};

	/**
	 * Validates a single field.
	 *
	 * @since 5.0.0
	 *
	 * @param {HTMLElement} input DOM Object that is the field we are validating.
	 *
	 * @return {boolean} If the field is valid.
	 */
	obj.validateField = function( input ) {
		const $input     = $( input );
		let isValidField = input.checkValidity();

		$document.trigger( 'beforeValidateField.tribeTicketsMeta', [ $input, isValidField ] );

		if ( ! isValidField ) {
			// Got to be careful of required checkbox/radio groups.
			if ( $input.is( ':checkbox' ) || $input.is( ':radio' ) ) {
				const $group = $input.closest( obj.selectors.formFieldInputCheckboxRadioGroup );

				if ( $group.length ) {
					isValidField = obj.validateCheckboxRadioGroup( $group );
				}
			}
		}

		if ( obj.isFieldBirthday( $input ) ) {
			isValidField = obj.validateFieldBirthday( $input );
		}

		// Do not allow empty spaces as valid for required fields.
		if (
			$input.prop( 'required' ) &&
			( $input.is( ':text' ) || $input.is( 'textarea' ) || 'tel' === $input.attr( 'type' ) )
		) {
			isValidField = !! $input.val().trim();
		}

		if ( ! isValidField ) {
			$input.addClass( obj.selectors.formFieldInputError.className() );
		} else {
			$input.removeClass( obj.selectors.formFieldInputError.className() );
		}

		$document.trigger( 'afterValidateField.tribeTicketsMeta', [ $input, isValidField ] );

		isValidField = obj.validateFieldOverride( $input, isValidField );

		return isValidField;
	};

	/**
	 * Overrides the single field validation, to work out custom validations.
	 * We can override the validation by setting `data-valid` in the input.
	 *
	 * @since 5.1.0
	 *
	 * @param {jQuery} $input jQuery object of the input.
	 * @param {boolean} isValidField If the Attendee ticket field is valid.
	 *
	 * @return {boolean} If the field is valid.
	 */
	obj.validateFieldOverride = function( $input, isValidField ) {
		if ( typeof $input.data( 'valid' ) === 'undefined' ) {
			return isValidField;
		}

		return $input.data( 'valid' );
	};

	/**
	 * Populate the different birthday field <select>
	 * depending on the value from the hidden input.
	 *
	 * @since 5.0.0
	 */
	obj.populateFieldBirthday = function() {
		$( obj.selectors.formFieldInputBirthday.container ).each( function( index, value ) {
			const wrapper = $( value );

			const day = wrapper.find( obj.selectors.formFieldInputBirthday.day );
			const month = wrapper.find( obj.selectors.formFieldInputBirthday.month );
			const year = wrapper.find( obj.selectors.formFieldInputBirthday.year );
			const realValue = wrapper.find( obj.selectors.formFieldInputBirthday.value );

			const savedValues = realValue.val().split( '-' );

			if ( 3 === savedValues.length ) {
				year.val( savedValues[ 0 ] );
				month.val( savedValues[ 1 ] );
				day.val( savedValues[ 2 ] );
			}
		} );
	};

	/**
	 * Update the birthday hidden input value depending
	 * on the changes the different <select> had.
	 *
	 * @since 5.0.0
	 *
	 * @param {Event} e input event.
	 *
	 * @return {void}
	 */
	obj.updateFieldBirthdayValue = function( e ) {
		const wrapper = $( e.target ).closest( obj.selectors.formFieldInputBirthday.container );
		const day = wrapper.find( obj.selectors.formFieldInputBirthday.day );
		const month = wrapper.find( obj.selectors.formFieldInputBirthday.month );
		const year = wrapper.find( obj.selectors.formFieldInputBirthday.year );
		const realValue = wrapper.find( obj.selectors.formFieldInputBirthday.value );

		// Data is stored in format: yyyy-mm-dd
		realValue.val( year.val() + '-' + month.val() + '-' + day.val() );
		realValue.trigger( 'change' );
	};

	/**
	 * Handle the required checkboxes. Once a checkbox changes we update
	 * the required value and set it only to the checked ones.
	 *
	 * @since 5.0.0
	 *
	 * @param {Event} e input change event.
	 *
	 * @return {void}
	 */
	obj.handleRequiredCheckboxes = function( e ) {
		const $input = $( e.target );
		const $group = $input.closest( obj.selectors.formFieldInputCheckbox.container );

		if ( ! $group.hasClass( obj.selectors.formFieldRequired.className() ) ) {
			return;
		}

		const $checked = $group.find( obj.selectors.formFieldInputCheckbox.checkbox + ':checked' );
		const $groupCheckboxes = $group.find( obj.selectors.formFieldInputCheckbox.checkbox );

		// If they un-check all, set them all as required.
		if ( 0 === $checked.length ) {
			$groupCheckboxes.attr( 'required', true );
			$groupCheckboxes.attr( 'aria-required', true );
			return;
		}

		// Only set the checked ones as required.
		$groupCheckboxes.removeAttr( 'required' );
		$groupCheckboxes.removeAttr( 'aria-required' );

		$checked.attr( 'required', true );
		$checked.attr( 'aria-required', true );
	};

	/**
	* Adds focus effect to attendee ticket block.
	*
	* @since 5.1.0
	*
	* @param {Event} e The event.
	*/
	obj.focusTicketAttendeeBlock = function( e ) {
		const input = e.target;

		$( input )
			.closest( obj.selectors.formAttendeeTicketsItem )
			.addClass( obj.selectors.formAttendeeTicketsItemFocus.className() );
	};

	/**
	 * Remove focus effect from attendee ticket block.
	 *
	 * @since 5.1.0
	 *
	 * @param {Event} e The event.
	 */
	obj.unFocusTicketAttendeeBlock = function( e ) {
		const input = e.target;

		$( input )
			.closest( obj.selectors.formAttendeeTicketsItem )
			.removeClass( obj.selectors.formAttendeeTicketsItemFocus.className() );
	};

	/**
	 * Init tickets attendee fields.
	 *
	 * @since 5.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		// init tickets attendee fields.
		$document.on(
			'change',
			obj.selectors.formFieldInputBirthday.select,
			obj.updateFieldBirthdayValue
		);

		$document.on(
			'change',
			obj.selectors.formFieldInputCheckbox.checkbox,
			obj.handleRequiredCheckboxes
		);

		/**
		 * Adds focus effect to attendee ticket block.
		 *
		 * @since 5.1.0
		 */
		$document.on(
			'focus',
			obj.selectors.formFieldInput,
			obj.focusTicketAttendeeBlock
		);

		/**
		 * Remove focus effect from attendee ticket block.
		 *
		 * @since 5.1.0
		 */
		$document.on(
			'blur',
			obj.selectors.formFieldInput,
			obj.unFocusTicketAttendeeBlock
		);
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.tickets.meta );
