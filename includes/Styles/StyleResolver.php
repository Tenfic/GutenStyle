<?php
namespace GutenStyle\Styles;

use GutenStyle\Styles\Value\EffectiveStyle;
use GutenStyle\Styles\Value\ResolvedProperty;
use GutenStyle\Styles\Value\ScopeStyle;
use InvalidArgumentException;

final class StyleResolver {
	private StyleRegistry $registry;

	public function __construct( StyleRegistry $registry ) {
		$this->registry = $registry;
	}

	public function resolve(
		string $module,
		ScopeStyle $global,
		ScopeStyle $post,
		ScopeStyle $block
	): EffectiveStyle {
		if ( null === $this->registry->get_block_adapter( $module ) ) {
			throw new InvalidArgumentException( 'Cannot resolve an unregistered style module.' );
		}

		$definitions = $this->registry->properties_for_module( $module );
		$properties  = [];
		foreach ( $definitions as $property_id => $definition ) {
			unset( $definition );
			$properties[ $property_id ] = ResolvedProperty::theme();
		}

		$effective_preset = null;
		$preset_source    = 'theme';
		foreach (
			[
				'global' => $global,
				'post'   => $post,
				'block'  => $block,
			] as $source => $scope
		) {
			if ( ScopeStyle::VERSION !== $scope->get_version() ) {
				throw new InvalidArgumentException( 'Scope uses an unsupported schema version.' );
			}

			$preset = $scope->get_preset();
			if ( null !== $preset ) {
				$definition = $this->registry->get_preset( $preset );
				if ( null === $definition || $module !== $definition->get_module() ) {
					throw new InvalidArgumentException( 'Scope references an invalid preset.' );
				}

				foreach ( $definition->get_properties() as $property_id => $value ) {
					if ( isset( $definitions[ $property_id ] ) ) {
						$properties[ $property_id ] = new ResolvedProperty( $value, $source, 'preset', $preset );
					}
				}
				$effective_preset = $preset;
				$preset_source    = $source;
			}

			foreach ( $scope->get_properties() as $property_id => $value ) {
				if ( ! isset( $definitions[ $property_id ] ) || ! $definitions[ $property_id ]->is_valid( $value ) ) {
					throw new InvalidArgumentException( 'Scope contains an invalid or unregistered property.' );
				}

				$properties[ $property_id ] = new ResolvedProperty(
					$definitions[ $property_id ]->sanitize( $value ),
					$source,
					'property'
				);
			}
		}

		return new EffectiveStyle( $module, $properties, $effective_preset, $preset_source );
	}
}
