<?php
namespace GutenStyle\Styles\Compiler;

use GutenStyle\Styles\StyleRegistry;
use GutenStyle\Styles\Value\EffectiveStyle;
use InvalidArgumentException;

final class WordPressStyleEngineCompiler {
	private StyleRegistry $registry;

	public function __construct( StyleRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * @param string[] $excluded_properties
	 * @return array<string,mixed>
	 */
	public function compile_style_object( EffectiveStyle $style, array $excluded_properties = [] ): array {
		$module  = $style->get_module();
		$adapter = $this->registry->get_block_adapter( $module );
		if ( null === $adapter ) {
			throw new InvalidArgumentException( 'Cannot compile an unregistered style module.' );
		}

		$style_object = [];
		foreach ( $style->get_properties() as $property_id => $resolved ) {
			if ( ! $resolved->is_resolved() || in_array( $property_id, $excluded_properties, true ) ) {
				continue;
			}

			$path       = $adapter->get_style_engine_path( $property_id );
			$definition = $this->registry->get_property( $property_id );
			if ( null === $path || [] === $path || null === $definition ) {
				continue;
			}

			$value = $resolved->get_value();
			if ( ! $definition->is_valid( $value ) ) {
				throw new InvalidArgumentException( 'Resolved native style value no longer matches its schema.' );
			}

			$target = &$style_object;
			foreach ( $path as $index => $segment ) {
				if ( ! is_string( $segment ) || 1 !== preg_match( '/^[a-zA-Z][a-zA-Z0-9]*$/', $segment ) ) {
					throw new InvalidArgumentException( 'Style Engine paths must contain safe keys.' );
				}
				if ( count( $path ) - 1 === $index ) {
					$target[ $segment ] = $definition->sanitize( $value );
					continue;
				}
				if ( ! isset( $target[ $segment ] ) || ! is_array( $target[ $segment ] ) ) {
					$target[ $segment ] = [];
				}
				$target = &$target[ $segment ];
			}
			unset( $target );
		}

		return $style_object;
	}

	/**
	 * @param string[] $excluded_properties
	 * @return array<string,mixed>
	 */
	public function compile( EffectiveStyle $style, array $excluded_properties = [] ): array {
		$style_object = $this->compile_style_object( $style, $excluded_properties );
		if ( [] === $style_object || ! function_exists( 'wp_style_engine_get_styles' ) ) {
			return [];
		}

		$output = wp_style_engine_get_styles( $style_object );
		return is_array( $output ) ? $output : [];
	}
}
