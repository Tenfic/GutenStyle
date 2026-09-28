<?php
namespace GutenStyle\Styles;

final class StyleRegistry {
	/** @var array<string,array<string,mixed>> */
	private array $presets = [];

	/**
	 * @param array<string,mixed> $definition
	 */
	public function register_preset( string $id, array $definition ): void {
		$this->presets[ sanitize_key( $id ) ] = $definition;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function all(): array {
		return $this->presets;
	}
}
