<?php
namespace GutenStyle\Styles\Contracts;

use GutenStyle\Styles\Value\ScopeStyle;

interface StyleScopeInterface {
	public function get_name(): string;

	/**
	 * @param array<string,mixed> $context
	 */
	public function get_style( string $module, array $context = [] ): ScopeStyle;
}
