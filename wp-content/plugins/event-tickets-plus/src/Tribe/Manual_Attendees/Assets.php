<?php

namespace Tribe\Tickets\Plus\Manual_Attendees;

use Tribe__Tickets_Plus__Main as Plugin;

/**
 * Class Assets
 *
 * @package Tribe\Tickets\Plus\Manual_Attendees
 *
 * @since   5.2.0
 */
class Assets {

	/**
	 * Key for this group of assets.
	 *
	 * @since 5.2.0
	 *
	 * @var string
	 */
	public static $group_key = 'event-tickets-plus-manual-attendees';

	/**
	 * Register assets.
	 *
	 * @since 5.2.0
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			static::$group_key . '-modal-styles',
			'tickets-manual-attendees.css',
			[
				'tec-variables-full',
				'tribe-common-full-style',
				'tribe-common-responsive',
				'tribe-dialog',
			],
			null,
			[
				'groups' => [
					static::$group_key,
					'tribe-tickets-admin',
				],
			]
		);

		tribe_asset(
			$plugin,
			static::$group_key . '-modal-scripts',
			'tickets-manual-attendees.js',
			[
				'jquery',
				'tribe-common',
				'tribe-tickets-loader',
			],
			null,
			[
				'groups' => [
					static::$group_key,
					'tribe-tickets-admin',
				],
			]
		);
	}
}
