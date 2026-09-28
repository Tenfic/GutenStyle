<?php
namespace GutenStyle\Table;

use GutenStyle\Contracts\ModuleInterface;
use GutenStyle\Styles\Schema\PropertyDefinition;
use GutenStyle\Styles\StyleRegistry;

final class TableModule implements ModuleInterface {
	public function register(): void {
		add_action( 'gutenstyle/register_style_properties', [ $this, 'register_properties' ] );
		add_action( 'gutenstyle/register_block_adapters', [ $this, 'register_adapter' ] );
	}

	public function register_adapter( StyleRegistry $registry ): void {
		$registry->register_block_adapter( new TableAdapter() );
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
}
