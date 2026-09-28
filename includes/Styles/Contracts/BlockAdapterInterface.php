<?php
namespace GutenStyle\Styles\Contracts;

interface BlockAdapterInterface {
	public function get_name(): string;

	/**
	 * @return string[]
	 */
	public function get_supported_properties(): array;

	public function get_css_variable( string $property_id ): ?string;

	/**
	 * Return a future WordPress Style Engine path for a property, or null when
	 * the adapter will use a CSS custom property instead.
	 *
	 * @return string[]|null
	 */
	public function get_style_engine_path( string $property_id ): ?array;
}
