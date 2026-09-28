<?php
namespace GutenStyle\Styles\Value;

final class EffectiveStyle {
	private string $module;
	/** @var array<string,ResolvedProperty> */
	private array $properties;
	private ?string $preset;
	private string $preset_source;

	/**
	 * @param array<string,ResolvedProperty> $properties
	 */
	public function __construct(
		string $module,
		array $properties,
		?string $preset = null,
		string $preset_source = 'theme'
	) {
		$this->module        = $module;
		$this->properties    = $properties;
		$this->preset        = $preset;
		$this->preset_source = $preset_source;
	}

	public function get_module(): string {
		return $this->module;
	}

	/**
	 * @return array<string,ResolvedProperty>
	 */
	public function get_properties(): array {
		return $this->properties;
	}

	public function get_property( string $property_id ): ?ResolvedProperty {
		return isset( $this->properties[ $property_id ] ) ? $this->properties[ $property_id ] : null;
	}

	public function get_preset(): ?string {
		return $this->preset;
	}

	public function get_preset_source(): string {
		return $this->preset_source;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$properties = [];
		foreach ( $this->properties as $property_id => $property ) {
			$properties[ $property_id ] = $property->to_array();
		}

		return [
			'module'       => $this->module,
			'preset'       => $this->preset,
			'presetSource' => $this->preset_source,
			'properties'   => $properties,
		];
	}
}
