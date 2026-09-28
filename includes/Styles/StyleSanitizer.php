<?php
namespace GutenStyle\Styles;

use GutenStyle\Styles\Value\ScopeStyle;
use InvalidArgumentException;

final class StyleSanitizer {
	public const DOCUMENT_VERSION = 1;

	private StyleRegistry $registry;

	public function __construct( StyleRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * @param array<string,mixed> $payload
	 */
	public function sanitize_scope_style( string $module, array $payload, bool $strict = true ): ScopeStyle {
		$adapter = $this->registry->get_block_adapter( $module );
		if ( null === $adapter ) {
			return $this->invalid_scope( 'Unregistered style module.', $strict );
		}

		$unknown_keys = array_diff( array_keys( $payload ), [ 'version', 'preset', 'properties' ] );
		if ( $strict && [] !== $unknown_keys ) {
			throw new InvalidArgumentException( 'Style scope contains unknown fields.' );
		}

		$version = isset( $payload['version'] ) ? (int) $payload['version'] : ScopeStyle::VERSION;
		if ( ScopeStyle::VERSION !== $version ) {
			return $this->invalid_scope( 'Unsupported style scope version.', $strict );
		}

		$preset = null;
		if ( isset( $payload['preset'] ) && '' !== $payload['preset'] ) {
			if ( ! is_string( $payload['preset'] ) ) {
				if ( $strict ) {
					throw new InvalidArgumentException( 'Preset ID must be a string.' );
				}
			} else {
				$definition = $this->registry->get_preset( $payload['preset'] );
				if ( null === $definition || $module !== $definition->get_module() ) {
					if ( $strict ) {
						throw new InvalidArgumentException( 'Preset is not registered for this module.' );
					}
				} else {
					$preset = $definition->get_id();
				}
			}
		}

		$input_properties = isset( $payload['properties'] ) ? $payload['properties'] : [];
		if ( ! is_array( $input_properties ) ) {
			return $this->invalid_scope( 'Style properties must be an object.', $strict );
		}

		$available  = $this->registry->properties_for_module( $module );
		$properties = [];
		foreach ( $input_properties as $property_id => $value ) {
			if ( ! is_string( $property_id ) || ! isset( $available[ $property_id ] ) ) {
				if ( $strict ) {
					throw new InvalidArgumentException( 'Style property is not registered for this module.' );
				}
				continue;
			}

			$definition = $available[ $property_id ];
			if ( ! $definition->is_valid( $value ) ) {
				if ( $strict ) {
					throw new InvalidArgumentException( 'Style property value is invalid.' );
				}
				continue;
			}

			$properties[ $property_id ] = $definition->sanitize( $value );
		}

		return new ScopeStyle( $preset, $properties, $version );
	}

	/**
	 * @param array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function sanitize_document( array $payload, bool $strict = true ): array {
		$unknown_keys = array_diff( array_keys( $payload ), [ 'version', 'blocks' ] );
		if ( $strict && [] !== $unknown_keys ) {
			throw new InvalidArgumentException( 'Style document contains unknown fields.' );
		}

		$version = isset( $payload['version'] ) ? (int) $payload['version'] : self::DOCUMENT_VERSION;
		if ( self::DOCUMENT_VERSION !== $version ) {
			if ( $strict ) {
				throw new InvalidArgumentException( 'Unsupported style document version.' );
			}
			return self::empty_document();
		}

		$input_blocks = isset( $payload['blocks'] ) ? $payload['blocks'] : [];
		if ( ! is_array( $input_blocks ) ) {
			if ( $strict ) {
				throw new InvalidArgumentException( 'Style document blocks must be an object.' );
			}
			return self::empty_document();
		}

		$blocks = [];
		foreach ( $input_blocks as $module => $scope_payload ) {
			if ( ! is_string( $module ) || ! is_array( $scope_payload ) ) {
				if ( $strict ) {
					throw new InvalidArgumentException( 'Invalid style module payload.' );
				}
				continue;
			}

			$scope = $this->sanitize_scope_style( $module, $scope_payload, $strict );
			if ( ! $scope->is_empty() ) {
				$blocks[ $module ] = $scope->to_array();
			}
		}

		return [
			'version' => self::DOCUMENT_VERSION,
			'blocks'  => $blocks,
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function empty_document(): array {
		return [
			'version' => self::DOCUMENT_VERSION,
			'blocks'  => [],
		];
	}

	private function invalid_scope( string $message, bool $strict ): ScopeStyle {
		if ( $strict ) {
			throw new InvalidArgumentException( $message );
		}
		return ScopeStyle::empty();
	}
}
