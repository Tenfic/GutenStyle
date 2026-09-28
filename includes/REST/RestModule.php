<?php
namespace GutenStyle\REST;

use GutenStyle\Contracts\ModuleInterface;
use GutenStyle\Styles\StyleModule;
use InvalidArgumentException;
use WP_Error;
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
				'permission_callback' => [ $this, 'can_read_styles' ],
			]
		);

		register_rest_route(
			'gutenstyle/v1',
			'/schema',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_schema' ],
				'permission_callback' => [ $this, 'can_read_styles' ],
			]
		);

		register_rest_route(
			'gutenstyle/v1',
			'/styles/global',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_global_styles' ],
					'permission_callback' => [ $this, 'can_read_styles' ],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [ $this, 'update_global_styles' ],
					'permission_callback' => [ $this, 'can_manage_global_styles' ],
				],
			]
		);
	}

	public function can_read_styles(): bool {
		return current_user_can( 'edit_posts' );
	}

	public function can_manage_global_styles(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_presets( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );

		$registry = StyleModule::registry();

		return new WP_REST_Response(
			[
				'presets' => $registry ? $registry->export_presets() : [],
			]
		);
	}

	public function get_schema( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );
		$registry = StyleModule::registry();

		return new WP_REST_Response(
			[
				'properties' => $registry ? $registry->export_properties() : [],
				'presets'    => $registry ? $registry->export_presets() : [],
				'adapters'   => $registry ? $registry->export_block_adapters() : [],
			]
		);
	}

	public function get_global_styles( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );
		$store = StyleModule::global_store();

		return new WP_REST_Response(
			$store ? $store->get_document() : [ 'version' => 1, 'blocks' => [] ]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_global_styles( WP_REST_Request $request ) {
		$payload = $request->get_json_params();
		if ( ! is_array( $payload ) ) {
			return new WP_Error(
				'gutenstyle_invalid_styles',
				__( 'Global styles must be a structured object.', 'gutenstyle' ),
				[ 'status' => 400 ]
			);
		}

		$store = StyleModule::global_store();
		if ( null === $store ) {
			return new WP_Error(
				'gutenstyle_engine_unavailable',
				__( 'The GutenStyle engine is not available.', 'gutenstyle' ),
				[ 'status' => 503 ]
			);
		}

		try {
			if ( ! $store->save_document( $payload ) ) {
				return new WP_Error(
					'gutenstyle_styles_not_saved',
					__( 'Global styles could not be saved.', 'gutenstyle' ),
					[ 'status' => 500 ]
				);
			}
		} catch ( InvalidArgumentException $error ) {
			return new WP_Error(
				'gutenstyle_invalid_styles',
				$error->getMessage(),
				[ 'status' => 400 ]
			);
		}

		return new WP_REST_Response( $store->get_document() );
	}
}
