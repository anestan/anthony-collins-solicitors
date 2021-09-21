<?php

namespace Tribe\Tickets\Plus\Manual_Attendees;

use Tribe__Tickets__Tickets;

/**
 * Class Hooks
 *
 * @package Tribe\Tickets\Plus\Manual_Attendees
 *
 * @since   5.2.0
 */
class Hooks {

	/**
	 * Add row action link for editing attendee in Attendee List view.
	 *
	 * @since 5.2.0
	 *
	 * @param array $row_actions Action links.
	 * @param array $item        Attendee data.
	 *
	 * @return array Return row action items.
	 */
	public function add_edit_attendee_row_action( array $row_actions, array $item ) {

		if ( ! isset( $item['event_id'] ) ) {
			return $row_actions;
		}

		$event_id = $item['event_id'];

		/** @var Tribe__Tickets__Attendees $attendees */
		$attendees = tribe( 'tickets.attendees' );

		if ( ! $attendees->user_can_manage_attendees( 0, $event_id ) ) {
			return $row_actions;
		}

		$provider           = ! empty( $item['provider'] ) ? $item['provider'] : null;
		$is_provider_active = false;

		if ( ! empty( $provider ) ) {
			$is_provider_active = tribe_tickets_is_provider_active( $provider );
		}

		// Do not continue if this provider is not active.
		if ( ! $is_provider_active ) {
			return $row_actions;
		}

		$button_args  = [
			'button_text'       => esc_html_x( 'Edit Attendee', 'row action', 'event-tickets-plus' ),
			'button_attributes' => [
				'data-attendee-id' => (string) $item['attendee_id'],
				'data-event-id'    => $event_id,
				'data-ticket-id'   => $item['product_id'],
				'data-provider'    => $provider,
				'data-modal-title' => esc_html_x( 'Edit Attendee Info', 'modal title', 'event-tickets-plus' ),
			],
			'button_classes'    => [
				'button-link',
				'edit_attendee',
			],
		];

		$button = Modal::get_modal_button( $button_args );

		$row_actions[] = '<span class="inline edit_link">' . $button . '</span>';

		return $row_actions;
	}

	/**
	 * Add Edit column on Attendee List.
	 *
	 * @since 5.2.0
	 *
	 * @param array $columns List of columns to be shown.
	 *
	 * @return array List of columns to be shown.
	 */
	public function add_attendee_edit_table_column_header( array $columns ) {
		$event_id = tribe_get_request_var( 'event_id', false );

		// Do not continue if this event has no ID passed in.
		if ( ! $event_id ) {
			return $columns;
		}

		// Check if there are any active providers for this event.
		$providers = Tribe__Tickets__Tickets::get_active_providers_for_post( $event_id );

		// Do not continue if this event has no available provider.
		if ( empty( $providers ) ) {
			return $columns;
		}

		return \Tribe__Main::array_insert_after_key(
			'check_in',
			$columns,
			[ 'edit_attendee' => esc_html_x( 'Edit', 'attendee table edit column header', 'event-tickets-plus' ) ]
		);
	}

	/**
	 * Render the Edit Attendee column value.
	 *
	 * @since 5.2.0
	 *
	 * @param string $value  Row item value.
	 * @param array  $item   Row item data.
	 * @param string $column Column name.
	 *
	 * @return string Link with edit icon for edit column.
	 */
	public function render_column_edit_attendee( $value, $item, $column ) {
		if ( 'edit_attendee' != $column ) {
			return $value;
		}

		/** @var \Tribe\Tickets\Plus\Manual_Attendees\Permissions $permissions */
		$permissions = tribe( 'tickets-plus.manual-attendees.permissions' );

		// Check if current user has permission to edit.
		if ( ! is_user_logged_in() || ! $permissions->is_allowed_to_edit( get_current_user_id() ) ) {
			return '';
		}

		$event_id           = $item['event_id'];
		$provider           = ! empty( $item['provider'] ) ? $item['provider'] : null;
		$is_provider_active = false;

		if ( ! empty( $provider ) ) {
			$is_provider_active = tribe_tickets_is_provider_active( $provider );
		}

		// Do not continue if this provider is not active.
		if ( ! $is_provider_active ) {
			return '';
		}

		$button_text  = '<span class="screen-reader-text tribe-common-a11y-visual-hide">' . esc_html_x( 'Edit Attendee', 'edit attendee button', 'event-tickets-plus' ) . '</span>';
		$button_text .= '<span class="edit-attendee-column-icon dashicons dashicons-ellipsis"></span>';

		$button_args = [
			'button_text'       => $button_text,
			'button_attributes' => [
				'data-attendee-id' => (string) $item['attendee_id'],
				'data-event-id'    => $event_id,
				'data-ticket-id'   => $item['product_id'],
				'data-provider'    => $provider,
				'data-modal-title' => esc_html_x( 'Edit Attendee Info', 'modal title', 'event-tickets-plus' ),
			],
			'button_classes'    => [
				'button-link',
				'edit-attendee',
			],
		];

		return Modal::get_modal_button( $button_args );
	}

	/**
	 * Add Button to the Attendee List Table Nav
	 *
	 * @since 5.2.0
	 *
	 * @param array  $nav    The array of items in the nav, where keys are the name of the item and values are the HTML of the buttons/inputs.
	 * @param string $which Either 'top' or 'bottom'; the location of the current nav items being filtered.
	 *
	 * @return array Return nav array with added button.
	 */
	public function add_nav_button( $nav, $which ) {
		if ( ! isset( $nav['left'] ) ) {
			return $nav;
		}

		/** @var \Tribe\Tickets\Plus\Manual_Attendees\Permissions $permissions */
		$permissions = tribe( 'tickets-plus.manual-attendees.permissions' );

		// Check if current user has permission to add.
		if ( ! is_user_logged_in() || ! $permissions->is_allowed_to_add( get_current_user_id() ) ) {
			return $nav;
		}

		$event_id  = tribe_get_request_var( 'event_id', false );
		$providers = Tribe__Tickets__Tickets::get_active_providers_for_post( $event_id );

		// Do not continue if this event has no available provider.
		if ( empty( $providers ) ) {
			return $nav;
		}

		$button_args = [
			'button_attributes' => [
				'data-event-id'    => $event_id,
				'data-provider'    => reset( $providers ),
				'data-modal-title' => esc_attr_x( 'Add Attendee', 'Add new attendee modal title', 'event-tickets-plus' ),
			],
		];

		$button = Modal::get_modal_button( $button_args );

		$nav['left']['add_attendee'] = $button;

		return $nav;
	}

	/**
	 * Add Button to the Attendee Report page Heading.
	 *
	 * @since 5.2.0
	 *
	 * @param Tribe__Tabbed_View $view Tabbed View Object.
	 */
	public function add_nav_button_on_title( $view ) {
		$page = tribe_get_request_var( 'page', false );

		if ( empty( $page ) || 'tickets-attendees' != $page ) {
			return;
		}

		/** @var \Tribe\Tickets\Plus\Manual_Attendees\Permissions $permissions */
		$permissions = tribe( 'tickets-plus.manual-attendees.permissions' );

		// Check if current user has permission to add.
		if ( ! is_user_logged_in() || ! $permissions->is_allowed_to_add( get_current_user_id() ) ) {
			return;
		}

		$event_id  = tribe_get_request_var( 'event_id', false );
		$providers = Tribe__Tickets__Tickets::get_active_providers_for_post( $event_id );

		// Do not continue if this event has no available provider.
		if ( empty( $providers ) ) {
			return;
		}

		$button_args = [
			'button_text'       => esc_attr_x( 'Add New', 'Add new attendee button for report page heading', 'event-tickets-plus' ),
			'button_attributes' => [
				'data-event-id'    => $event_id,
				'data-provider'    => reset( $providers ),
				'data-modal-title' => esc_attr_x( 'Add Attendee', 'Add new attendee modal title', 'event-tickets-plus' ),
			],
			'button_classes'    => [
				'action',
				'add_attendee',
				'page-title-action',
			],
		];

		echo Modal::get_modal_button( $button_args );
	}

	/**
	 * Remove Edit Column from showing up in CSV export.
	 *
	 * @since 5.2.1
	 *
	 * @param array $export_columns The columns to use for exporting.
	 * @param array $items          Items to be exported.
	 * @param int   $event_id       Event ID for the export data.
	 *
	 * @return array The columns to use for exporting.
	 */
	public function remove_edit_column_for_csv_export(  $export_columns, $items, $event_id ) {

		if ( isset( $export_columns['edit_attendee'] ) ) {
			unset( $export_columns['edit_attendee'] );
		}

		return $export_columns;
	}
}
