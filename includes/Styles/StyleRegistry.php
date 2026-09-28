<?php
namespace GutenStyle\Styles;

use GutenStyle\Styles\Contracts\BlockAdapterInterface;
use GutenStyle\Styles\Schema\PresetDefinition;
use GutenStyle\Styles\Schema\PropertyDefinition;
use InvalidArgumentException;

final class StyleRegistry {
	/** @var array<string,PropertyDefinition> */
	private array $properties = [];

	/** @var array<string,PresetDefinition> */
	private array $presets = [];

	/** @var array<string,BlockAdapterInterface> */
	private array $block_adapters = [];

	/** @var array<string,array<string,mixed>> */
	private array $components = [];

	/** @var array<string,array<string,mixed>> */
	private array $profiles = [];

	public function register_property( PropertyDefinition $definition ): void {
		$this->properties[ $definition->get_id() ] = $definition;
	}

	public function register_block_adapter( BlockAdapterInterface $adapter ): void {
		$name = $adapter->get_name();
		if ( ! preg_match( '/^[a-z0-9-]+\/[a-z0-9-]+$/', $name ) ) {
			throw new InvalidArgumentException( 'Block adapters must use a valid block name.' );
		}

		$this->block_adapters[ $name ] = $adapter;
	}

	public function register_preset( PresetDefinition $definition ): void {
		$module  = $definition->get_module();
		$adapter = $this->get_block_adapter( $module );
		if ( null === $adapter ) {
			throw new InvalidArgumentException( 'Presets require a registered block adapter.' );
		}

		$properties = [];
		foreach ( $definition->get_properties() as $property_id => $value ) {
			$property = $this->get_property( (string) $property_id );
			if ( null === $property || ! $property->supports_module( $module ) || ! $property->is_valid( $value ) ) {
				throw new InvalidArgumentException( 'Preset contains an invalid or unregistered property.' );
			}
			if ( ! in_array( $property_id, $adapter->get_supported_properties(), true ) ) {
				throw new InvalidArgumentException( 'Preset property is not supported by its block adapter.' );
			}

			$properties[ $property_id ] = $property->sanitize( $value );
		}

		$this->presets[ $definition->get_id() ] = new PresetDefinition(
			$definition->get_id(),
			$definition->get_label(),
			$module,
			$properties
		);
	}

	/**
	 * @param array<string,mixed> $definition
	 */
	public function register_component( string $id, array $definition ): void {
		$this->components[ $this->validate_extension_id( $id ) ] = $definition;
	}

	/**
	 * @param array<string,mixed> $definition
	 */
	public function register_profile( string $id, array $definition ): void {
		$this->profiles[ $this->validate_extension_id( $id ) ] = $definition;
	}

	public function get_property( string $id ): ?PropertyDefinition {
		return isset( $this->properties[ $id ] ) ? $this->properties[ $id ] : null;
	}

	public function get_preset( string $id ): ?PresetDefinition {
		return isset( $this->presets[ $id ] ) ? $this->presets[ $id ] : null;
	}

	public function get_block_adapter( string $name ): ?BlockAdapterInterface {
		return isset( $this->block_adapters[ $name ] ) ? $this->block_adapters[ $name ] : null;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function get_component( string $id ): ?array {
		return isset( $this->components[ $id ] ) ? $this->components[ $id ] : null;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function get_profile( string $id ): ?array {
		return isset( $this->profiles[ $id ] ) ? $this->profiles[ $id ] : null;
	}

	/**
	 * @return array<string,PropertyDefinition>
	 */
	public function all_properties(): array {
		return $this->properties;
	}

	/**
	 * @return array<string,PresetDefinition>
	 */
	public function all_presets(): array {
		return $this->presets;
	}

	/**
	 * Backward-compatible preset export for the original public registry API.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function all(): array {
		return $this->export_presets();
	}

	/**
	 * @return array<string,BlockAdapterInterface>
	 */
	public function all_block_adapters(): array {
		return $this->block_adapters;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function all_components(): array {
		return $this->components;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function all_profiles(): array {
		return $this->profiles;
	}

	/**
	 * @return array<string,PropertyDefinition>
	 */
	public function properties_for_module( string $module ): array {
		$adapter = $this->get_block_adapter( $module );
		if ( null === $adapter ) {
			return [];
		}

		$supported = $adapter->get_supported_properties();
		return array_filter(
			$this->properties,
			static function ( PropertyDefinition $definition ) use ( $module, $supported ): bool {
				return $definition->supports_module( $module )
					&& in_array( $definition->get_id(), $supported, true );
			}
		);
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function export_properties(): array {
		$output = [];
		foreach ( $this->properties as $id => $definition ) {
			$output[ $id ] = $definition->to_array();
		}
		return $output;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function export_presets(): array {
		$output = [];
		foreach ( $this->presets as $id => $definition ) {
			$output[ $id ] = $definition->to_array();
		}
		return $output;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function export_block_adapters(): array {
		$output = [];
		foreach ( $this->block_adapters as $name => $adapter ) {
			$properties = [];
			foreach ( $adapter->get_supported_properties() as $property_id ) {
				$properties[ $property_id ] = [
					'cssVariable'     => $adapter->get_css_variable( $property_id ),
					'styleEnginePath' => $adapter->get_style_engine_path( $property_id ),
				];
			}

			$output[ $name ] = [
				'name'       => $name,
				'properties' => $properties,
			];
		}
		return $output;
	}

	private function validate_extension_id( string $id ): string {
		if ( ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $id ) ) {
			throw new InvalidArgumentException( 'Extension IDs must use kebab-case.' );
		}
		return $id;
	}
}
