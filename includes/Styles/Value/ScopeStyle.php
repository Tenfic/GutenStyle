<?php
namespace GutenStyle\Styles\Value;

final class ScopeStyle {
	public const VERSION = 1;

	private int $version;
	private ?string $preset;
	private array $properties;

	/**
	 * @param array<string,mixed> $properties
	 */
	public function __construct( ?string $preset = null, array $properties = [], int $version = self::VERSION ) {
		$this->version    = $version;
		$this->preset     = $preset;
		$this->properties = $properties;
	}

	public static function empty(): self {
		return new self();
	}

	public function get_version(): int {
		return $this->version;
	}

	public function get_preset(): ?string {
		return $this->preset;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_properties(): array {
		return $this->properties;
	}

	public function without_property( string $property_id ): self {
		$properties = $this->properties;
		unset( $properties[ $property_id ] );
		return new self( $this->preset, $properties, $this->version );
	}

	public function without_preset(): self {
		return new self( null, $this->properties, $this->version );
	}

	public function is_empty(): bool {
		return null === $this->preset && [] === $this->properties;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return [
			'version'    => $this->version,
			'preset'     => $this->preset,
			'properties' => $this->properties,
		];
	}
}
