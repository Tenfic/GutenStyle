<?php
namespace GutenStyle\Styles\Storage;

use GutenStyle\Styles\StyleSanitizer;
use GutenStyle\Styles\Value\ScopeStyle;

final class PostStyleStore {
	public const META_KEY = '_gutenstyle_styles';

	private StyleSanitizer $sanitizer;

	public function __construct( StyleSanitizer $sanitizer ) {
		$this->sanitizer = $sanitizer;
	}

	public function register(): void {
		add_action( 'init', [ $this, 'register_meta' ] );
	}

	public function register_meta(): void {
		$post_types = get_post_types( [ 'public' => true ], 'names' );
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'editor' ) ) {
				continue;
			}
			if ( function_exists( 'use_block_editor_for_post_type' ) && ! use_block_editor_for_post_type( $post_type ) ) {
				continue;
			}

			$args = [
				'single'            => true,
				'type'              => 'object',
				'default'           => StyleSanitizer::empty_document(),
				'show_in_rest'      => [ 'schema' => self::rest_schema() ],
				'sanitize_callback' => [ $this, 'sanitize_meta' ],
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ): bool {
					unset( $allowed, $meta_key );
					return current_user_can( 'edit_post', (int) $post_id );
				},
			];

			if ( post_type_supports( $post_type, 'revisions' ) ) {
				$args['revisions_enabled'] = true;
			}

			register_post_meta( $post_type, self::META_KEY, $args );
		}
	}

	/**
	 * @param mixed $value
	 * @return array<string,mixed>
	 */
	public function sanitize_meta( $value ): array {
		return is_array( $value )
			? $this->sanitizer->sanitize_document( $value, false )
			: StyleSanitizer::empty_document();
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_document( int $post_id ): array {
		$value = get_post_meta( $post_id, self::META_KEY, true );
		return is_array( $value )
			? $this->sanitizer->sanitize_document( $value, false )
			: StyleSanitizer::empty_document();
	}

	public function get_style( int $post_id, string $module ): ScopeStyle {
		$document = $this->get_document( $post_id );
		$payload  = isset( $document['blocks'][ $module ] ) && is_array( $document['blocks'][ $module ] )
			? $document['blocks'][ $module ]
			: [];
		return $this->sanitizer->sanitize_scope_style( $module, $payload, false );
	}

	public function save_style( int $post_id, string $module, ScopeStyle $style ): bool {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		$style    = $this->sanitizer->sanitize_scope_style( $module, $style->to_array(), true );
		$document = $this->get_document( $post_id );
		if ( $style->is_empty() ) {
			unset( $document['blocks'][ $module ] );
		} else {
			$document['blocks'][ $module ] = $style->to_array();
		}

		return $this->persist( $post_id, $document );
	}

	public function reset_property( int $post_id, string $module, string $property_id ): bool {
		return $this->save_style(
			$post_id,
			$module,
			$this->get_style( $post_id, $module )->without_property( $property_id )
		);
	}

	public function reset_module( int $post_id, string $module ): bool {
		return $this->save_style( $post_id, $module, ScopeStyle::empty() );
	}

	public function reset_all( int $post_id ): bool {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}
		delete_post_meta( $post_id, self::META_KEY );
		return true;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function rest_schema(): array {
		$property_value_schema = [ 'type' => [ 'boolean', 'number', 'string' ] ];
		$scope_schema          = [
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => [
				'version'    => [ 'type' => 'integer' ],
				'preset'     => [ 'type' => [ 'string', 'null' ] ],
				'properties' => [
					'type'                 => 'object',
					'additionalProperties' => $property_value_schema,
				],
			],
		];

		return [
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => [
				'version' => [ 'type' => 'integer' ],
				'blocks'  => [
					'type'                 => 'object',
					'additionalProperties' => $scope_schema,
				],
			],
		];
	}

	/**
	 * @param array<string,mixed> $document
	 */
	private function persist( int $post_id, array $document ): bool {
		if ( [] === $document['blocks'] ) {
			delete_post_meta( $post_id, self::META_KEY );
			return true;
		}

		$result = update_post_meta( $post_id, self::META_KEY, $document );
		return false !== $result || $document === get_post_meta( $post_id, self::META_KEY, true );
	}
}
