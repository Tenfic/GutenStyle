<?php
namespace GutenStyle\Table;

use GutenStyle\Styles\StyleEngine;
use GutenStyle\Styles\Value\EffectiveStyle;

final class TableRenderer {
	private StyleEngine $engine;
	private TableAdapter $adapter;

	public function __construct( StyleEngine $engine, TableAdapter $adapter ) {
		$this->engine  = $engine;
		$this->adapter = $adapter;
	}

	/**
	 * @param array<string,mixed> $block
	 */
	public function render( string $block_content, array $block ): string {
		if ( '' === $block_content || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $block_content;
		}

		$attributes = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : [];
		$post_id    = function_exists( 'get_the_ID' ) ? (int) get_the_ID() : 0;
		$effective  = $this->engine->resolve( TableAdapter::BLOCK_NAME, $post_id, $attributes );
		$excluded   = $this->adapter->get_native_override_properties( $attributes );
		$variables  = $this->engine->compiler()->compile( $effective, $excluded );
		$native     = $this->engine->native_compiler()->compile( $effective, $excluded );
		$classes    = $this->get_classes( $effective, $variables, $excluded );

		$native_css = isset( $native['css'] ) && is_string( $native['css'] ) ? $native['css'] : '';
		if ( [] === $variables && '' === $native_css && [] === $classes ) {
			return $block_content;
		}

		$processor = new \WP_HTML_Tag_Processor( $block_content );
		if ( ! $processor->next_tag( [ 'class_name' => 'wp-block-table' ] ) ) {
			return $block_content;
		}

		$processor->add_class( TableAdapter::WRAPPER_CLASS );
		foreach ( $classes as $class_name ) {
			$processor->add_class( $class_name );
		}
		if ( [] !== $variables ) {
			$processor->set_attribute(
				'style',
				$this->append_css( $processor->get_attribute( 'style' ), $this->compile_variables( $variables ) )
			);
		}

		if ( '' !== $native_css && $processor->next_tag( [ 'tag_name' => 'TABLE' ] ) ) {
			$processor->set_attribute(
				'style',
				$this->append_css( $processor->get_attribute( 'style' ), $native_css )
			);
		}

		return $processor->get_updated_html();
	}

	/**
	 * @param array<string,string> $variables
	 * @param string[] $excluded
	 * @return string[]
	 */
	private function get_classes( EffectiveStyle $effective, array $variables, array $excluded ): array {
		$classes = [];
		foreach ( $this->adapter->get_supported_properties() as $property_id ) {
			$variable = $this->adapter->get_css_variable( $property_id );
			if ( null !== $variable && isset( $variables[ $variable ] ) ) {
				$classes[] = $this->adapter->get_property_class( $property_id );
			}
		}

		if ( $this->is_enabled( $effective, 'stripedRows', $excluded ) ) {
			$classes[] = TableAdapter::WRAPPER_CLASS . '--striped';
		}
		if ( $this->is_enabled( $effective, 'hoverRows', $excluded ) ) {
			$classes[] = TableAdapter::WRAPPER_CLASS . '--hover';
		}
		$responsive = $effective->get_property( 'tableResponsiveMode' );
		if (
			null !== $responsive
			&& $responsive->is_resolved()
			&& ! in_array( 'tableResponsiveMode', $excluded, true )
			&& 'horizontal-scroll' === $responsive->get_value()
		) {
			$classes[] = TableAdapter::WRAPPER_CLASS . '--horizontal-scroll';
		}

		return array_values( array_unique( $classes ) );
	}

	/**
	 * @param string[] $excluded
	 */
	private function is_enabled( EffectiveStyle $effective, string $property_id, array $excluded ): bool {
		$property = $effective->get_property( $property_id );
		return null !== $property
			&& $property->is_resolved()
			&& ! in_array( $property_id, $excluded, true )
			&& true === $property->get_value();
	}

	/**
	 * @param array<string,string> $variables
	 */
	private function compile_variables( array $variables ): string {
		$output = '';
		foreach ( $variables as $property => $value ) {
			$output .= $property . ':' . $value . ';';
		}
		return $output;
	}

	/** @param mixed $existing */
	private function append_css( $existing, string $css ): string {
		$existing = is_string( $existing ) ? trim( $existing ) : '';
		if ( '' !== $existing && ';' !== substr( $existing, -1 ) ) {
			$existing .= ';';
		}
		return $existing . ltrim( $css );
	}
}
