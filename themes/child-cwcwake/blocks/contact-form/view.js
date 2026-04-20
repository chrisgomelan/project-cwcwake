/**
 * CWC Wake — Contact form view script.
 *
 * Pure progressive enhancement on top of the server-rendered form:
 *   - On submit, validate required inputs and a basic email pattern;
 *     if anything is missing, prevent the POST, mark the offending
 *     fields with `aria-invalid` + `is-invalid`, focus the first one,
 *     and let the server-side handler stay the source of truth for
 *     successful submissions.
 *   - On blur, clear the invalid state so the user gets feedback as
 *     they fix it without waiting for a second submit.
 *   - Disable the submit button + change its text on send so users
 *     don't double-submit while the redirect resolves.
 *   - Auto-focus any server-rendered status banner so screen readers
 *     announce the success/error message after the page reloads.
 *
 * The form remains fully functional with JavaScript disabled — every
 * check here is also performed server-side in
 * `inc/contact-form-handler.php`.
 *
 * @since 1.0.0
 */

( () => {
	'use strict';

	/**
	 * Validate an email address using a permissive HTML5-style pattern.
	 *
	 * Mirrors the server-side check (`is_email()`) closely enough for
	 * progressive enhancement; the server remains authoritative.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} value The candidate email string.
	 * @return {boolean} True when the value looks like an email.
	 */
	const isValidEmail = ( value ) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value );

	/**
	 * Toggle the invalid styling on a single field.
	 *
	 * @since 1.0.0
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} input The field to mark.
	 * @param {boolean} invalid Whether to mark it invalid.
	 * @return {void}
	 */
	const setFieldInvalid = ( input, invalid ) => {
		const field = input.closest( '.cwc-contact-form__field' );

		if ( invalid ) {
			input.setAttribute( 'aria-invalid', 'true' );
			input.classList.add( 'is-invalid' );
			field?.classList.add( 'is-invalid' );
		} else {
			input.removeAttribute( 'aria-invalid' );
			input.classList.remove( 'is-invalid' );
			field?.classList.remove( 'is-invalid' );
		}
	};

	/**
	 * Wire validation, submit-state, and blur listeners on a form.
	 *
	 * @since 1.0.0
	 *
	 * @param {HTMLFormElement} form Contact form element.
	 * @return {void}
	 */
	const enhanceForm = ( form ) => {
		const nameInput    = form.querySelector( '[name="cwc_name"]' );
		const emailInput   = form.querySelector( '[name="cwc_email"]' );
		const messageInput = form.querySelector( '[name="cwc_message"]' );
		const submitBtn    = form.querySelector( '.cwc-contact-form__submit' );

		[ nameInput, emailInput, messageInput ].forEach( ( input ) => {
			if ( ! input ) {
				return;
			}
			input.addEventListener( 'blur', () => {
				if ( input.value.trim() !== '' ) {
					setFieldInvalid( input, false );
				}
			} );
		} );

		form.addEventListener( 'submit', ( event ) => {
			let firstInvalid = null;

			if ( nameInput && nameInput.value.trim() === '' ) {
				setFieldInvalid( nameInput, true );
				firstInvalid = firstInvalid || nameInput;
			}

			if ( emailInput ) {
				const emailValue = emailInput.value.trim();
				if ( emailValue === '' || ! isValidEmail( emailValue ) ) {
					setFieldInvalid( emailInput, true );
					firstInvalid = firstInvalid || emailInput;
				}
			}

			if ( messageInput && messageInput.value.trim() === '' ) {
				setFieldInvalid( messageInput, true );
				firstInvalid = firstInvalid || messageInput;
			}

			if ( firstInvalid ) {
				event.preventDefault();
				firstInvalid.focus();
				return;
			}

			if ( submitBtn ) {
				submitBtn.disabled = true;
				submitBtn.dataset.originalLabel = submitBtn.textContent;
				submitBtn.textContent = 'Sending…';
			}
		} );
	};

	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '[data-cwc-contact-form]' ).forEach( enhanceForm );

		/*
		 * After a PRG redirect the page is fresh, so move focus to the
		 * banner (success or error) so screen-reader users hear it
		 * announced right away.
		 */
		const banner = document.querySelector( '[data-cwc-contact-banner]' );
		banner?.focus( { preventScroll: false } );
	} );
} )();
