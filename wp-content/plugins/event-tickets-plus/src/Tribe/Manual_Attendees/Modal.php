<?php

namespace Tribe\Tickets\Plus\Manual_Attendees;

use Tribe\Tickets\Plus\Manual_Attendees\Assets as Assets;

/**
 * Class Modal
 *
 * @package Tribe\Tickets\Plus\Manual_Attendees
 *
 * @since 5.2.0
 */
class Modal {

	/**
	 * Modal ID.
	 *
	 * @since 5.2.0
	 *
	 * @var string
	 */
	public static $modal_id = 'tribe-tickets__manual-attendees-dialog';

	/**
	 * Modal target.
	 *
	 * @since 5.2.0
	 *
	 * @var string
	 */
	public static $modal_target = 'tribe-tickets__manual-attendees-dialog';

	/**
	 * Check if we should render the modal.
	 *
	 * @since 5.2.0
	 *
	 * @return boolean Whether we should render the modal.
	 */
	public function should_render() {
		$screen = get_current_screen();

		return false !== strpos( $screen->id, 'page_tickets-attendees' );
	}

	/**
	 * Render the Manual Attendees modal.
	 *
	 * @since 5.2.0
	 */
	public function render_modal() {
		if ( ! $this->should_render() ) {
			return;
		}

		// Enqueue Manual Attendee assets.
		tribe_asset_enqueue_group( Assets::$group_key );

		tribe_asset_enqueue_group( 'tribe-tickets-admin' );

		// Render the modal contents.
		echo $this->get_modal_content();
	}

	/**
	 * Get the default modal args.
	 *
	 * @since 5.2.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return array The default modal args.
	 */
	public function get_modal_args( $args = [] ) {
		$default_args = [
			'append_target'           => '#' . static::$modal_target,
			'button_display'          => false,
			'close_event'             => 'tribeDialogCloseManualAttendeesModal.tribeTickets',
			'show_event'              => 'tribeDialogShowManualAttendeesModal.tribeTickets',
			'content_wrapper_classes' => 'tribe-dialog__wrapper tribe-modal__wrapper--manual-attendees tribe-tickets__admin-container event-tickets tribe-common',
			'title'                   => esc_html__( 'Add Attendee', 'event-tickets-plus' ),
			'title_classes'           => [
				'tribe-dialog__title',
				'tribe-modal__title',
				'tribe-common-h5',
				'tribe-modal__title--manual-attendees',
			],
		];

		return wp_parse_args( $args, $default_args );
	}

	/**
	 * Get the default modal contents.
	 *
	 * @since 5.2.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return string The modal content.
	 */
	public function get_modal_content( $args = [] ) {
		/** @var Tribe__Tickets__Editor__Template $template */
		$template = tribe( 'tickets.editor.template' );

		$content = $template->template( 'v2/components/loader/loader', [], false );

		$args = $this->get_modal_args( $args );

		$dialog_view = tribe( 'dialog.view' );

		// @todo @juanfra: it's returning `null` when using fourth parameter as false.
		ob_start();
		$dialog_view->render_modal( $content, $args, static::$modal_id );
		$modal_content = ob_get_clean();

		// @todo @juanfra: Check how's that we're gonna deal with the admin views
		// and see how we load wrappers here instead of hardcoding.
		$modal  = '<div class="tribe-common event-tickets">';
		$modal .= '<span id="' . esc_attr( static::$modal_target ) . '"></span>';
		$modal .= $modal_content;
		$modal .= '</div>';

		return $modal;
	}

	/**
	 * Get the default modal button args.
	 *
	 * @since 5.2.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return array The default modal button args.
	 */
	public static function get_modal_button_args( $args = [] ) {
		$default_args = [
			'id'                      => static::$modal_id,
			'append_target'           => '#' . static::$modal_target,
			'button_classes'          => [ 'button', 'action', 'add_attendee' ],
			'button_attributes'       => [ 'data-modal-title' => esc_html__( 'Add Attendee', 'event-tickets-plus' ) ],
			'button_display'          => true,
			'button_id'               => 'tribe-tickets__manual-attendee-' . uniqid(),
			'button_name'             => 'tribe-tickets-manual-attendees',
			'button_text'             => esc_attr_x( '+ Add Attendee', 'Add Attendee button for Attendee list', 'event-tickets-plus' ),
			'button_type'             => 'button',
			'close_event'             => 'tribeDialogCloseManualAttendeesModal.tribeTickets',
			'show_event'              => 'tribeDialogShowManualAttendeesModal.tribeTickets',
			'content_wrapper_classes' => 'tribe-dialog__wrapper event-tickets tribe-common',
			'title'                   => esc_html__( 'Add Attendee', 'event-tickets-plus' ),
			'title_classes'           => [
				'tribe-dialog__title',
				'tribe-modal__title',
				'tribe-common-h5',
				'tribe-modal__title--manual-attendees',
			],
		];

		return wp_parse_args( $args, $default_args );
	}

	/**
	 * Get the default modal button.
	 *
	 * @since 5.2.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return string The modal button.
	 */
	public static function get_modal_button( $args = [] ) {
		$args        = self::get_modal_button_args( $args );
		$dialog_view = tribe( 'dialog.view' );
		$button      = $dialog_view->template( 'button', $args, false );

		return $button;
	}
}
