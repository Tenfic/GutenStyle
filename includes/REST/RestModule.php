<?php
namespace GutenStyle\REST;

use GutenStyle\Contracts\ModuleInterface;
use GutenStyle\Styles\StyleModule;
use WP_REST_Request;
use WP_REST_Response;

final class RestModule implements ModuleInterface {
	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		register_rest_route(
			'gutenstyle/v1',
			'/presets',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_presets' ],
				'permission_callback' => static function (): bool {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	public function get_presets( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );

		$registry = StyleModule::registry();

		return new WP_REST_Response(
			[
				'presets' => $registry ? $registry->all() : [],
			]
		);
	}
}
