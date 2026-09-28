<?php
namespace GutenStyle\Styles\Scope;

use GutenStyle\Styles\Block\BlockStyleAttributes;
use GutenStyle\Styles\Contracts\StyleScopeInterface;
use GutenStyle\Styles\StyleSanitizer;
use GutenStyle\Styles\Value\ScopeStyle;

final class BlockStyleScope implements StyleScopeInterface {
	private StyleSanitizer $sanitizer;

	public function __construct( StyleSanitizer $sanitizer ) {
		$this->sanitizer = $sanitizer;
	}

	public function get_name(): string {
		return 'block';
	}

	public function get_style( string $module, array $context = [] ): ScopeStyle {
		$attributes = isset( $context['block_attributes'] ) && is_array( $context['block_attributes'] )
			? $context['block_attributes']
			: [];
		return BlockStyleAttributes::read( $module, $attributes, $this->sanitizer );
	}
}
