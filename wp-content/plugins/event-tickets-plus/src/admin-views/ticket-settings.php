<?php
unset( $settings['ticket-paypal-et-plus-header'] );

/** @var Tribe__Settings $settings_class */
$settings_class = tribe( 'settings' );

// Get link to Display Tab.
$display_tab_url = $settings_class->get_url(
	[
		'page' => 'tribe-common',
		'tab'  => 'display',
	]
);

$iac_tooltip = esc_html_x(
	'The default Individual Attendee Collection option when you create new tickets, which may be customized per ticket.',
	'tooltip for Individual Attendee Collection setting',
	'event-tickets-plus'
);

$iac_enabled = tribe_tickets_new_views_is_enabled();

if ( ! $iac_enabled ) {
	$anchor_text = sprintf(
		// Translators: %1$s: dynamic "Tickets" text.
		_x(
			'Updated %1$s Experience setting',
			'link anchor text within tooltip for Individual Attendee Collection setting',
			'event-tickets-plus'
		),
		tribe_get_ticket_label_plural( 'new_views_not_enabled_setting' )
	);

	$iac_tooltip .= ' ';
	$iac_tooltip .= wp_kses(
		sprintf(
			// Translators: %1$s: link to new tickets views setting.
			_x(
				'The %1$s must also be enabled if you want this setting enabled.',
				'tooltip for Individual Attendee Collection setting',
				'event-tickets-plus'
			),
			'<a href="' . esc_url( $display_tab_url . '#tickets_rsvp_display_title' ) . '">' . $anchor_text . '</a>'
		),
		[
			'a' => [
				'href' => [],
			],
		]
	);
}

$template_options = [
	'default' => esc_html_x( 'Default Page Template', 'dropdown option', 'event-tickets-plus' ),
];

if ( class_exists( 'Tribe__Events__Main' ) ) {
	$template_options['same'] = esc_html__( 'Same as Event Page Template', 'event-tickets-plus' );
}

$templates = get_page_templates();

ksort( $templates );

foreach ( array_keys( $templates ) as $template ) {
	$template_options[ $templates[ $template ] ] = $template;
}

$page_options = [ '' => esc_html__( 'Choose a page or leave blank.', 'event-tickets-plus' ) ];

$pages = get_pages();

if ( $pages ) {
	foreach ( $pages as $page ) {
		$page_options[ $page->ID ] = $page->post_title;
	}
} else {
	// If no pages, let the user know they need one.
	$page_options = [ '' => esc_html__( 'You must create a page before using this functionality', 'event-tickets-plus' ) ];
}

$ar_page_description = __( 'Optional: select an existing page to act as your attendee registration page. <strong>Requires</strong> use of the `[tribe_attendee_registration]` shortcode and overrides the above template and URL slug.', 'event-tickets-plus' );

/** @var Tribe__Tickets__Attendee_Registration__Main $attendee_registration */
$attendee_registration = tribe( 'tickets.attendee_registration' );

/** @var \Tribe\Tickets\Plus\Attendee_Registration\IAC $iac */
$iac = tribe( 'tickets-plus.attendee-registration.iac' );

$ar_page = $attendee_registration->get_attendee_registration_page();

// this is hooked too early for has_shortcode() to work properly, so regex to the rescue!
if ( ! empty( $ar_page ) && ! preg_match( '/\[tribe_attendee_registration\/?\]/', $ar_page->post_content ) ) {
	$ar_slug_description = __( 'Selected page <strong>must</strong> use the `[tribe_attendee_registration]` shortcode. While the shortcode is missing the default redirect will be used.', 'event-tickets-plus' );
}

$modal_version_check = ! tribe_installed_before( 'Tribe__Tickets__Main', '4.11.0' );

$iac_option_name = $iac->get_default_iac_setting_option_name();
$iac_default     = $iac->get_default_iac_setting();
$iac_options     = $iac->get_iac_setting_options();

$attendee_options = [
	'ticket-attendee-heading'       => [
		'type' => 'html',
		'html' => '<h3>' . __( 'Attendee Registration', 'event-tickets-plus' ) . '</h3>',
	],
	$iac_option_name                => [
		'type'            => 'dropdown',
		'label'           => esc_html_x( 'Individual Attendee Collection Default Setting', 'Individual Attendee Collection settings label', 'event-tickets-plus' ),
		'tooltip'         => $iac_tooltip,
		'validation_type' => 'options',
		'size'            => 'large',
		'default'         => $iac_default,
		'options'         => $iac_options,
	],
	'ticket-attendee-modal'         => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Attendee Registration Modal ', 'event-tickets-plus' ),
		'tooltip'         => wp_kses(
			sprintf(
				// Translators: %1$s: dynamic "tickets" text. %2$s: opening of HTML link. %3$s: closing of HTML link.
				_x(
					'Enabling the Attendee Registration Modal provides a new sales flow for purchasing %1$s that include Attendee Registration. [%2$sLearn more%3$s]',
					'checkbox to enable Attendee Registration Modal',
					'event-tickets-plus'
				),
				tribe_get_ticket_label_plural_lowercase( 'modal_notice_tooltip' ),
				'<a href="https://evnt.is/attendee-registration" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			[
				'a' => [
					'href' => [],
				],
			]
		),
		'size'            => 'medium',
		'default'         => $modal_version_check,
		'validation_type' => 'boolean',
		'attributes'      => [ 'id' => 'ticket-attendee-enable-modal' ],
	],
	'ticket-attendee-info-slug'     => [
		'type'                => 'text',
		'label'               => esc_html__( 'Attendee Registration URL slug', 'event-tickets-plus' ),
		'tooltip'             => esc_html__( 'The slug used for building the URL for the Attendee Registration Info page.', 'event-tickets-plus' ),
		'size'                => 'medium',
		'default'             => $attendee_registration->get_slug(),
		'validation_callback' => 'is_string',
		'validation_type'     => 'slug',
	],
	'ticket-attendee-info-template' => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Attendee Registration template', 'event-tickets-plus' ),
		'tooltip'         => esc_html__( 'Choose a page template to control the appearance of your attendee registration page.', 'event-tickets-plus' ),
		'validation_type' => 'options',
		'size'            => 'large',
		'default'         => 'default',
		'options'         => $template_options,
	],
	'ticket-attendee-page-id'       => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Attendee Registration page', 'event-tickets-plus' ),
		'tooltip'         => $ar_page_description,
		'validation_type' => 'options',
		'size'            => 'large',
		'default'         => 'default',
		'options'         => $page_options,
	],
];

$settings = Tribe__Main::array_insert_after_key( 'ticket-authentication-requirements', $settings, $attendee_options );

return $settings;
