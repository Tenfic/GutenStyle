<?php
namespace GutenStyle\Styles;

use GutenStyle\Contracts\ModuleInterface;

final class StyleModule implements ModuleInterface {
	private static ?StyleRegistry $registry = null;

	public function register(): void {
		self::$registry = new StyleRegistry();

		/**
		 * Register property definitions before adapters and presets so presets can
		 * be validated against the complete public schema.
		 */
		do_action( 'gutenstyle/register_style_properties', self::$registry );
		do_action( 'gutenstyle/register_block_adapters', self::$registry );
		do_action( 'gutenstyle/register_style_presets', self::$registry );

		// Backward-compatible general registration hook.
		do_action( 'gutenstyle/register_styles', self::$registry );
	}

	public static function registry(): ?StyleRegistry {
		return self::$registry;
	}
}
