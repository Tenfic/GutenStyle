<?php
namespace GutenStyle\Styles\Block;

use GutenStyle\Styles\StyleSanitizer;
use GutenStyle\Styles\Value\ScopeStyle;

final class BlockStyleAttributes {
	public const ATTRIBUTE_KEY = 'gutenstyle';

	/**
	 * @param array<string,mixed> $block_attributes
	 */
	public static function read( string $module, array $block_attributes, StyleSanitizer $sanitizer ): ScopeStyle {
		$payload = isset( $block_attributes[ self::ATTRIBUTE_KEY ] ) && is_array( $block_attributes[ self::ATTRIBUTE_KEY ] )
			? $block_attributes[ self::ATTRIBUTE_KEY ]
			: [];

		return $sanitizer->sanitize_scope_style( $module, $payload, false );
	}

	/**
	 * @param array<string,mixed> $block_attributes
	 * @return array<string,mixed>
	 */
	public static function write( array $block_attributes, ScopeStyle $style ): array {
		if ( $style->is_empty() ) {
			unset( $block_attributes[ self::ATTRIBUTE_KEY ] );
			return $block_attributes;
		}

		$block_attributes[ self::ATTRIBUTE_KEY ] = $style->to_array();
		return $block_attributes;
	}

	/**
	 * @param array<string,mixed> $block_attributes
	 * @return array<string,mixed>
	 */
	public static function reset_property(
		string $module,
		array $block_attributes,
		string $property_id,
		StyleSanitizer $sanitizer
	): array {
		$style = self::read( $module, $block_attributes, $sanitizer );
		return self::write( $block_attributes, $style->without_property( $property_id ) );
	}

	/**
	 * @param array<string,mixed> $block_attributes
	 * @return array<string,mixed>
	 */
	public static function reset_all( array $block_attributes ): array {
		unset( $block_attributes[ self::ATTRIBUTE_KEY ] );
		return $block_attributes;
	}
}
