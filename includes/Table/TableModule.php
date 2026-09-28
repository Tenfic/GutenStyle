<?php
namespace GutenStyle\Table;

use GutenStyle\Contracts\ModuleInterface;
use GutenStyle\Styles\Schema\PropertyDefinition;
use GutenStyle\Styles\StyleModule;
use GutenStyle\Styles\StyleRegistry;

final class TableModule implements ModuleInterface {
	public function register(): void {
		add_action( 'gutenstyle/register_style_properties', [ $this, 'register_properties' ] );
		add_action( 'gutenstyle/register_block_adapters', [ $this, 'register_adapter' ] );
		add_action( 'gutenstyle/register_style_presets', [ $this, 'register_presets' ] );
		add_filter( 'register_block_type_args', [ $this, 'register_block_attribute' ], 10, 2 );
		add_filter( 'render_block_core/table', [ $this, 'render_table' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_style' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_style' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_style' ] );
	}

	public function register_adapter( StyleRegistry $registry ): void {
		$registry->register_block_adapter( new TableAdapter() );
	}

	public function register_presets( StyleRegistry $registry ): void {
		foreach ( TablePresets::all() as $preset ) {
			$registry->register_preset( $preset );
		}
	}

	public function register_properties( StyleRegistry $registry ): void {
		$module    = TableAdapter::BLOCK_NAME;
		$dimension = [ 'modules' => [ $module ], 'min' => 0 ];
		$align     = [
			'modules'        => [ $module ],
			'allowed_values' => [ 'left', 'center', 'right', 'start', 'end' ],
		];

		$definitions = [
			new PropertyDefinition(
				'tableBorderStyle',
				__( 'Border style', 'gutenstyle' ),
				PropertyDefinition::TYPE_ENUM,
				[
					'modules'        => [ $module ],
					'allowed_values' => [ 'solid', 'dashed', 'dotted', 'double', 'none' ],
				]
			),
			new PropertyDefinition( 'tableBorderWidth', __( 'Border width', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition( 'tableBorderColor', __( 'Border color', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'tableBorderRadius', __( 'Border radius', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition( 'tableBackgroundColor', __( 'Table background', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'cellPaddingHorizontal', __( 'Horizontal padding', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition( 'cellPaddingVertical', __( 'Vertical padding', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition( 'cellTextColor', __( 'Cell text color', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'cellBackgroundColor', __( 'Cell background', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'cellTextAlign', __( 'Cell text alignment', 'gutenstyle' ), PropertyDefinition::TYPE_ENUM, $align ),
			new PropertyDefinition(
				'cellVerticalAlign',
				__( 'Cell vertical alignment', 'gutenstyle' ),
				PropertyDefinition::TYPE_ENUM,
				[ 'modules' => [ $module ], 'allowed_values' => [ 'top', 'middle', 'bottom' ] ]
			),
			new PropertyDefinition( 'headerBackgroundColor', __( 'Header background', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'headerTextColor', __( 'Header text color', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition(
				'headerFontWeight',
				__( 'Header font weight', 'gutenstyle' ),
				PropertyDefinition::TYPE_ENUM,
				[ 'modules' => [ $module ], 'allowed_values' => [ '400', '500', '600', '700' ] ]
			),
			new PropertyDefinition( 'headerTextAlign', __( 'Header text alignment', 'gutenstyle' ), PropertyDefinition::TYPE_ENUM, $align ),
			new PropertyDefinition( 'stripedRows', __( 'Striped rows', 'gutenstyle' ), PropertyDefinition::TYPE_BOOLEAN, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'stripedRowBackgroundColor', __( 'Stripe background', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'hoverRows', __( 'Row hover', 'gutenstyle' ), PropertyDefinition::TYPE_BOOLEAN, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'hoverRowBackgroundColor', __( 'Hover background', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'dividerColor', __( 'Divider color', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'captionTextColor', __( 'Caption text color', 'gutenstyle' ), PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] ),
			new PropertyDefinition( 'captionFontSize', __( 'Caption font size', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition( 'captionTextAlign', __( 'Caption alignment', 'gutenstyle' ), PropertyDefinition::TYPE_ENUM, $align ),
			new PropertyDefinition( 'captionSpacingTop', __( 'Caption top spacing', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition( 'captionSpacingBottom', __( 'Caption bottom spacing', 'gutenstyle' ), PropertyDefinition::TYPE_DIMENSION, $dimension ),
			new PropertyDefinition(
				'tableResponsiveMode',
				__( 'Responsive behavior', 'gutenstyle' ),
				PropertyDefinition::TYPE_ENUM,
				[
					'modules'        => [ $module ],
					'allowed_values' => [ 'native', 'horizontal-scroll' ],
					'responsive'     => true,
				]
			),
		];

		foreach ( $definitions as $definition ) {
			$registry->register_property( $definition );
		}
	}

	/**
	 * @param array<string,mixed> $args
	 * @return array<string,mixed>
	 */
	public function register_block_attribute( array $args, string $block_type ): array {
		if ( TableAdapter::BLOCK_NAME !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = [];
		}
		$args['attributes']['gutenstyle'] = [ 'type' => 'object' ];
		return $args;
	}

	/**
	 * @param array<string,mixed> $block
	 */
	public function render_table( string $block_content, array $block ): string {
		$engine = StyleModule::engine();
		if ( null === $engine ) {
			return $block_content;
		}

		$adapter = $engine->registry()->get_block_adapter( TableAdapter::BLOCK_NAME );
		if ( ! $adapter instanceof TableAdapter ) {
			return $block_content;
		}

		return ( new TableRenderer( $engine, $adapter ) )->render( $block_content, $block );
	}

	public function enqueue_frontend_style(): void {
		$engine = StyleModule::engine();
		if ( null === $engine ) {
			return;
		}

		$global_style = $engine->global_store()->get_style( TableAdapter::BLOCK_NAME );
		$post_id      = function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0;
		$post_style   = $post_id > 0
			? $engine->post_store()->get_style( $post_id, TableAdapter::BLOCK_NAME )
			: null;
		$has_block_override = false;
		if ( $post_id > 0 && function_exists( 'get_post' ) && function_exists( 'has_block' ) ) {
			$post = get_post( $post_id );
			$has_block_override = $post
				&& has_block( TableAdapter::BLOCK_NAME, $post )
				&& false !== strpos( (string) $post->post_content, '"gutenstyle"' );
		}

		if ( $global_style->is_empty() && ( null === $post_style || $post_style->is_empty() ) && ! $has_block_override ) {
			return;
		}

		$this->enqueue_style();
	}

	public function enqueue_admin_style( string $hook_suffix ): void {
		if ( 'toplevel_page_gutenstyle' === $hook_suffix ) {
			$this->enqueue_style();
		}
	}

	public function enqueue_editor_style(): void {
		$this->enqueue_style();
	}

	private function enqueue_style(): void {
		$css_file = GUTENSTYLE_PATH . 'build/table.css';
		if ( ! file_exists( $css_file ) ) {
			return;
		}

		wp_enqueue_style(
			'gutenstyle-table',
			GUTENSTYLE_URL . 'build/table.css',
			[],
			(string) filemtime( $css_file )
		);
	}
}
