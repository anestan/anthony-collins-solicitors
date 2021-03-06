<?php

namespace TEC\Tickets\Commerce\Gateways\PayPal\REST;

use TEC\Tickets\Commerce\Gateways\PayPal\REST;
use TEC\Tickets\Commerce\Gateways\PayPal\Webhooks\Listeners\Payment_Capture_Completed;
use TEC\Tickets\Commerce\Gateways\PayPal\Webhooks\Webhooks_Route;
use Tribe__Documentation__Swagger__Provider_Interface;
use Tribe__REST__Endpoints__CREATE_Endpoint_Interface;
use Tribe__Tickets__REST__V1__Endpoints__Base;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Webhook.
 *
 * @since   5.1.6
 * @package Tribe\Tickets\REST\V1\Endpoints\PayPal_Commerce
 */
class Webhook_Endpoint
	extends Tribe__Tickets__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__CREATE_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * The REST API endpoint path.
	 *
	 * @since 5.1.6
	 *
	 * @var string
	 */
	protected $path = '/tickets-commerce/paypal/webhook';

	/**
	 * Gets the Endpoint path for the on boarding process.
	 *
	 * @since 5.1.9
	 *
	 * @return string
	 */
	public function get_endpoint_path() {
		return $this->path;
	}

	/**
	 * Get the REST API route URL.
	 *
	 * @since 5.1.9
	 *
	 * @return string The REST API route URL.
	 */
	public function get_route_url() {
		$rest     = tribe( REST::class );

		return rest_url( '/' . $rest->namespace . $this->get_endpoint_path(), 'https' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.1.6
	 */
	public function get_documentation() {
		return [
			'post' => [
				'consumes'   => [
					'application/json',
				],
				'parameters' => [],
				'responses'  => [
					'200' => [
						'description' => __( 'Processes the Webhook as long as it includes valid Payment Event data', 'event-tickets' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type'       => 'object',
									'properties' => [
										'success' => [
											'description' => __( 'Whether the processing was successful', 'event-tickets' ),
											'type'        => 'boolean',
										],
									],
								],
							],
						],
					],
					'403' => [
						'description' => __( 'The webhook was invalid and was not processed', 'event-tickets' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * {@inheritDoc}
	 *
 	 * @todo WIP -- Still using pieces from Give.
	 *
	 * @since 5.1.6
	 *
	 * @param WP_REST_Request $request   The request object.
	 * @param bool            $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 */
	public function create( WP_REST_Request $request, $return_id = false ) {
		$event   = $request->get_body();
		$headers = $request->get_headers();

		/** @var Webhooks_Route $webhook */
		$webhook = tribe( Webhooks_Route::class );

		try {
			$processed = $webhook->handle( $event, $headers );
		} catch ( \Exception $exception ) {
			$processed = false;
		}

		if ( ! $processed ) {
			$error   = 'webhook-not-processed';
			$message = $this->messages->get_message( $error );

			return new WP_Error( $error, $message, [ 'status' => 403 ] );
		}

		$data = [
			'success' => true,
		];

		return new WP_REST_Response( $data );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.1.6
	 *
	 * @return array
	 */
	public function CREATE_args() {
		// Webhooks do not send any arguments, only JSON content.
		return [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.1.6
	 *
	 * @return bool Whether the current user can post or not.
	 */
	public function can_create() {
		// Always open, no further user-based validation.
		return true;
	}
}
