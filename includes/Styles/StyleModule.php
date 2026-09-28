<?php
namespace GutenStyle\Styles;

use GutenStyle\Contracts\ModuleInterface;
use GutenStyle\Styles\Storage\GlobalStyleStore;
use GutenStyle\Styles\Storage\PostStyleStore;

final class StyleModule implements ModuleInterface {
	private static ?StyleRegistry $registry = null;
	private static ?StyleSanitizer $sanitizer = null;
	private static ?GlobalStyleStore $global_store = null;
	private static ?PostStyleStore $post_store = null;
	private static ?StyleEngine $engine = null;

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

		self::$sanitizer    = new StyleSanitizer( self::$registry );
		self::$global_store = new GlobalStyleStore( self::$sanitizer );
		self::$post_store   = new PostStyleStore( self::$sanitizer );
		self::$post_store->register();
		self::$engine = new StyleEngine(
			self::$registry,
			self::$sanitizer,
			self::$global_store,
			self::$post_store
		);
	}

	public static function registry(): ?StyleRegistry {
		return self::$registry;
	}

	public static function sanitizer(): ?StyleSanitizer {
		return self::$sanitizer;
	}

	public static function global_store(): ?GlobalStyleStore {
		return self::$global_store;
	}

	public static function post_store(): ?PostStyleStore {
		return self::$post_store;
	}

	public static function engine(): ?StyleEngine {
		return self::$engine;
	}
}
