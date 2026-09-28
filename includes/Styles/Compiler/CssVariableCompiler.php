<?php
namespace GutenStyle\Styles\Compiler;

use GutenStyle\Styles\StyleRegistry;
use GutenStyle\Styles\Value\EffectiveStyle;
use InvalidArgumentException;

final class CssVariableCompiler {
	private StyleRegistry $registry;

	public function __construct( StyleRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Compile only registered and resolved values to custom-property pairs.
	 *
	 * @return array<string,string>
	 */
	public function compile( EffectiveStyle $style ): array {
		$module  = $style->get_module();
		$adapter = $this->registry->get_block_adapter( $module );
		if ( null === $adapter ) {
			throw new InvalidArgumentException( 'Cannot compile an unregistered style module.' );
		}

		$declarations = [];
		foreach ( $style->get_properties() as $property_id => $resolved ) {
			if ( ! $resolved->is_resolved() ) {
				continue;
			}

			$definition = $this->registry->get_property( $property_id );
			if ( null === $definition || ! $definition->supports_module( $module ) ) {
				continue;
			}

			$variable = $adapter->get_css_variable( $property_id );
			if ( null === $variable ) {
				$variable = $definition->get_css_variable( $module );
			}
			if ( null === $variable || 1 !== preg_match( '/^--gutenstyle-[a-z0-9-]+$/', $variable ) ) {
				continue;
			}

			$value = $resolved->get_value();
			if ( ! $definition->is_valid( $value ) ) {
				throw new InvalidArgumentException( 'Resolved style value no longer matches its schema.' );
			}

			$declarations[ $variable ] = $this->stringify( $definition->sanitize( $value ) );
		}

		ksort( $declarations );
		return $declarations;
	}

	public function compile_declarations( EffectiveStyle $style ): string {
		$output = '';
		foreach ( $this->compile( $style ) as $variable => $value ) {
			$output .= $variable . ':' . $value . ';';
		}
		return $output;
	}

	/**
	 * @param bool|float|int|string|null $value
	 */
	private function stringify( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ? '1' : '0';
		}
		return (string) $value;
	}
}
