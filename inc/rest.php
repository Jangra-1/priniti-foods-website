<?php
/**
 * Theme REST endpoints (read-only, public data only).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	static function (): void {
		// Header search suggestions: loaded the first time the search dialog opens.
		register_rest_route(
			'priniti/v1',
			'/search-index',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => static function (): WP_REST_Response {
					$response = new WP_REST_Response( priniti_get_search_index() );
					$response->header( 'Cache-Control', 'public, max-age=300' );
					return $response;
				},
			)
		);
	}
);
