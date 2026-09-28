<?php
namespace GutenStyle\Styles\Value;

final class ResolvedProperty {
	/** @var mixed */
	private $value;
	private string $source;
	private string $via;
	private ?string $preset;

	/**
	 * @param mixed $value
	 */
	public function __construct( $value, string $source, string $via = 'property', ?string $preset = null ) {
		$this->value  = $value;
		$this->source = $source;
		$this->via    = $via;
		$this->preset = $preset;
	}

	public static function theme(): self {
		return new self( null, 'theme', 'none' );
	}

	/**
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	public function get_source(): string {
		return $this->source;
	}

	public function get_via(): string {
		return $this->via;
	}

	public function get_preset(): ?string {
		return $this->preset;
	}

	public function is_resolved(): bool {
		return 'theme' !== $this->source;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return [
			'value'  => $this->value,
			'source' => $this->source,
			'via'    => $this->via,
			'preset' => $this->preset,
		];
	}
}
