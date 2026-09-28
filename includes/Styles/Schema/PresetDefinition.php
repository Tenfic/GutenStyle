<?php
namespace GutenStyle\Styles\Schema;

use InvalidArgumentException;

final class PresetDefinition {
	private string $id;
	private string $label;
	private string $module;
	private array $properties;

	/**
	 * @param array<string,mixed> $properties
	 */
	public function __construct( string $id, string $label, string $module, array $properties ) {
		if ( ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $id ) ) {
			throw new InvalidArgumentException( 'Preset IDs must use kebab-case.' );
		}
		if ( ! preg_match( '/^[a-z0-9-]+\/[a-z0-9-]+$/', $module ) ) {
			throw new InvalidArgumentException( 'Preset modules must use a registered block name.' );
		}

		$this->id         = $id;
		$this->label      = $label;
		$this->module     = $module;
		$this->properties = $properties;
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_module(): string {
		return $this->module;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_properties(): array {
		return $this->properties;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return [
			'id'         => $this->id,
			'label'      => $this->label,
			'module'     => $this->module,
			'properties' => $this->properties,
		];
	}
}
