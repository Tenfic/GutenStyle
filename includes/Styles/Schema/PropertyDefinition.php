<?php
namespace GutenStyle\Styles\Schema;

use InvalidArgumentException;

final class PropertyDefinition {
	public const TYPE_BOOLEAN   = 'boolean';
	public const TYPE_ENUM      = 'enum';
	public const TYPE_COLOR     = 'color';
	public const TYPE_NUMBER    = 'number';
	public const TYPE_DIMENSION = 'dimension';
	public const TYPE_STRING    = 'string';

	private string $id;
	private string $label;
	private string $type;
	private string $default_behavior;
	private array $allowed_values;
	private ?float $minimum;
	private ?float $maximum;
	private bool $responsive;
	private array $css_variables;
	private array $modules;
	private ?string $pattern;

	/**
	 * @param array<string,mixed> $options
	 */
	public function __construct( string $id, string $label, string $type, array $options = [] ) {
		if ( ! preg_match( '/^[a-z][A-Za-z0-9]*$/', $id ) ) {
			throw new InvalidArgumentException( 'Style property IDs must use lower camelCase.' );
		}

		$types = [
			self::TYPE_BOOLEAN,
			self::TYPE_ENUM,
			self::TYPE_COLOR,
			self::TYPE_NUMBER,
			self::TYPE_DIMENSION,
			self::TYPE_STRING,
		];

		if ( ! in_array( $type, $types, true ) ) {
			throw new InvalidArgumentException( 'Unsupported style property type.' );
		}

		$allowed_values = isset( $options['allowed_values'] ) && is_array( $options['allowed_values'] )
			? array_values( $options['allowed_values'] )
			: [];
		$pattern        = isset( $options['pattern'] ) && is_string( $options['pattern'] )
			? $options['pattern']
			: null;

		if ( self::TYPE_ENUM === $type && [] === $allowed_values ) {
			throw new InvalidArgumentException( 'Enum properties require allowed values.' );
		}
		if ( self::TYPE_ENUM === $type && count( $allowed_values ) !== count( array_filter( $allowed_values, 'is_string' ) ) ) {
			throw new InvalidArgumentException( 'Enum allowed values must be strings.' );
		}

		if ( self::TYPE_STRING === $type && [] === $allowed_values && null === $pattern ) {
			throw new InvalidArgumentException( 'String properties must be constrained.' );
		}
		if ( null !== $pattern && false === @preg_match( $pattern, '' ) ) {
			throw new InvalidArgumentException( 'String property pattern must be a valid regular expression.' );
		}

		$default_behavior = isset( $options['default_behavior'] ) ? (string) $options['default_behavior'] : 'inherit';
		if ( ! in_array( $default_behavior, [ 'inherit', 'initial' ], true ) ) {
			throw new InvalidArgumentException( 'Unsupported default behavior.' );
		}

		$css_variables = isset( $options['css_variables'] ) && is_array( $options['css_variables'] )
			? $options['css_variables']
			: [];
		foreach ( $css_variables as $module => $variable ) {
			if ( ! is_string( $module ) || ! is_string( $variable ) || ! preg_match( '/^--gutenstyle-[a-z0-9-]+$/', $variable ) ) {
				throw new InvalidArgumentException( 'CSS variable mappings must use the --gutenstyle-* namespace.' );
			}
		}

		$this->id               = $id;
		$this->label            = $label;
		$this->type             = $type;
		$this->default_behavior = $default_behavior;
		$this->allowed_values   = $allowed_values;
		$this->minimum          = isset( $options['min'] ) && is_numeric( $options['min'] ) ? (float) $options['min'] : null;
		$this->maximum          = isset( $options['max'] ) && is_numeric( $options['max'] ) ? (float) $options['max'] : null;
		if ( null !== $this->minimum && null !== $this->maximum && $this->minimum > $this->maximum ) {
			throw new InvalidArgumentException( 'Style property minimum cannot exceed its maximum.' );
		}
		$this->responsive       = ! empty( $options['responsive'] );
		$this->css_variables    = $css_variables;
		$this->modules          = isset( $options['modules'] ) && is_array( $options['modules'] )
			? array_values( array_filter( $options['modules'], 'is_string' ) )
			: [];
		$this->pattern          = $pattern;
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_type(): string {
		return $this->type;
	}

	public function supports_module( string $module ): bool {
		return [] === $this->modules || in_array( $module, $this->modules, true );
	}

	public function get_css_variable( string $module ): ?string {
		return isset( $this->css_variables[ $module ] ) ? $this->css_variables[ $module ] : null;
	}

	/**
	 * @param mixed $value
	 */
	public function is_valid( $value ): bool {
		switch ( $this->type ) {
			case self::TYPE_BOOLEAN:
				return is_bool( $value ) || in_array( $value, [ 0, 1, '0', '1', 'true', 'false' ], true );
			case self::TYPE_ENUM:
				return is_string( $value ) && in_array( $value, $this->allowed_values, true );
			case self::TYPE_COLOR:
				return is_string( $value ) && 1 === preg_match( '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value );
			case self::TYPE_NUMBER:
				if ( ! is_numeric( $value ) ) {
					return false;
				}
				$number = (float) $value;
				return is_finite( $number )
					&& ( null === $this->minimum || $number >= $this->minimum )
					&& ( null === $this->maximum || $number <= $this->maximum );
			case self::TYPE_DIMENSION:
				if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
					return false;
				}
				return 1 === preg_match( '/^(?:0|-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vw|vh|vmin|vmax|ch|ex))$/', trim( (string) $value ) );
			case self::TYPE_STRING:
				if ( ! is_string( $value ) ) {
					return false;
				}
				if ( [] !== $this->allowed_values ) {
					return in_array( $value, $this->allowed_values, true );
				}
				return null !== $this->pattern && 1 === preg_match( $this->pattern, $value );
		}

		return false;
	}

	/**
	 * @param mixed $value
	 * @return bool|float|int|string|null
	 */
	public function sanitize( $value ) {
		if ( ! $this->is_valid( $value ) ) {
			return null;
		}

		switch ( $this->type ) {
			case self::TYPE_BOOLEAN:
				return true === $value || 1 === $value || '1' === $value || 'true' === $value;
			case self::TYPE_NUMBER:
				$number = (float) $value;
				return floor( $number ) === $number ? (int) $number : $number;
			case self::TYPE_COLOR:
				return strtolower( trim( (string) $value ) );
			case self::TYPE_DIMENSION:
				return trim( (string) $value );
			case self::TYPE_ENUM:
			case self::TYPE_STRING:
				return function_exists( 'sanitize_text_field' )
					? sanitize_text_field( $value )
					: trim( strip_tags( $value ) );
		}

		return null;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return [
			'id'              => $this->id,
			'label'           => $this->label,
			'type'            => $this->type,
			'defaultBehavior' => $this->default_behavior,
			'allowedValues'   => $this->allowed_values,
			'min'             => $this->minimum,
			'max'             => $this->maximum,
			'responsive'      => $this->responsive,
			'cssVariables'    => $this->css_variables,
			'modules'         => $this->modules,
		];
	}
}
