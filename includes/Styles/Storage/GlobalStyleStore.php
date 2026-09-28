<?php
namespace GutenStyle\Styles\Storage;

use GutenStyle\Styles\StyleSanitizer;
use GutenStyle\Styles\Value\ScopeStyle;

final class GlobalStyleStore {
	public const OPTION_NAME = 'gutenstyle_global_styles';

	private StyleSanitizer $sanitizer;

	public function __construct( StyleSanitizer $sanitizer ) {
		$this->sanitizer = $sanitizer;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_document(): array {
		$value = get_option( self::OPTION_NAME, StyleSanitizer::empty_document() );
		return is_array( $value )
			? $this->sanitizer->sanitize_document( $value, false )
			: StyleSanitizer::empty_document();
	}

	public function get_style( string $module ): ScopeStyle {
		$document = $this->get_document();
		$payload  = isset( $document['blocks'][ $module ] ) && is_array( $document['blocks'][ $module ] )
			? $document['blocks'][ $module ]
			: [];
		return $this->sanitizer->sanitize_scope_style( $module, $payload, false );
	}

	/**
	 * @param array<string,mixed> $document
	 */
	public function save_document( array $document ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return $this->persist( $this->sanitizer->sanitize_document( $document, true ) );
	}

	public function save_style( string $module, ScopeStyle $style ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$style    = $this->sanitizer->sanitize_scope_style( $module, $style->to_array(), true );
		$document = $this->get_document();
		if ( $style->is_empty() ) {
			unset( $document['blocks'][ $module ] );
		} else {
			$document['blocks'][ $module ] = $style->to_array();
		}

		return $this->persist( $document );
	}

	public function reset_property( string $module, string $property_id ): bool {
		return $this->save_style( $module, $this->get_style( $module )->without_property( $property_id ) );
	}

	public function reset_module( string $module ): bool {
		return $this->save_style( $module, ScopeStyle::empty() );
	}

	public function reset_all(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * @param array<string,mixed> $document
	 */
	private function persist( array $document ): bool {
		if ( [] === $document['blocks'] ) {
			delete_option( self::OPTION_NAME );
			return true;
		}

		$existing = get_option( self::OPTION_NAME, false );
		if ( false === $existing ) {
			return add_option( self::OPTION_NAME, $document, '', false );
		}
		return update_option( self::OPTION_NAME, $document, false );
	}
}
