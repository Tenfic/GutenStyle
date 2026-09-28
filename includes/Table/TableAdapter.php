<?php
namespace GutenStyle\Table;

use GutenStyle\Styles\Contracts\BlockAdapterInterface;

final class TableAdapter implements BlockAdapterInterface {
	public const BLOCK_NAME = 'core/table';

	private const CSS_VARIABLES = [
		'tableBorderRadius'          => '--gutenstyle-table-border-radius',
		'cellPaddingHorizontal'      => '--gutenstyle-table-cell-padding-x',
		'cellPaddingVertical'        => '--gutenstyle-table-cell-padding-y',
		'cellBackgroundColor'        => '--gutenstyle-table-cell-background',
		'cellTextAlign'              => '--gutenstyle-table-cell-text-align',
		'cellVerticalAlign'          => '--gutenstyle-table-cell-vertical-align',
		'headerBackgroundColor'      => '--gutenstyle-table-header-background',
		'headerTextColor'            => '--gutenstyle-table-header-color',
		'headerFontWeight'           => '--gutenstyle-table-header-font-weight',
		'headerTextAlign'            => '--gutenstyle-table-header-text-align',
		'stripedRowBackgroundColor'  => '--gutenstyle-table-stripe-background',
		'hoverRowBackgroundColor'    => '--gutenstyle-table-hover-background',
		'dividerColor'               => '--gutenstyle-table-divider-color',
		'captionTextColor'           => '--gutenstyle-table-caption-color',
		'captionFontSize'            => '--gutenstyle-table-caption-font-size',
		'captionTextAlign'           => '--gutenstyle-table-caption-text-align',
		'captionSpacingTop'          => '--gutenstyle-table-caption-spacing-top',
		'captionSpacingBottom'       => '--gutenstyle-table-caption-spacing-bottom',
	];

	private const STYLE_ENGINE_PATHS = [
		'tableBorderStyle'     => [ 'border', 'style' ],
		'tableBorderWidth'     => [ 'border', 'width' ],
		'tableBorderColor'     => [ 'border', 'color' ],
		'tableBackgroundColor' => [ 'color', 'background' ],
		'cellTextColor'        => [ 'color', 'text' ],
	];

	public function get_name(): string {
		return self::BLOCK_NAME;
	}

	public function get_supported_properties(): array {
		return [
			'tableBorderStyle',
			'tableBorderWidth',
			'tableBorderColor',
			'tableBorderRadius',
			'tableBackgroundColor',
			'cellPaddingHorizontal',
			'cellPaddingVertical',
			'cellTextColor',
			'cellBackgroundColor',
			'cellTextAlign',
			'cellVerticalAlign',
			'headerBackgroundColor',
			'headerTextColor',
			'headerFontWeight',
			'headerTextAlign',
			'stripedRows',
			'stripedRowBackgroundColor',
			'hoverRows',
			'hoverRowBackgroundColor',
			'dividerColor',
			'captionTextColor',
			'captionFontSize',
			'captionTextAlign',
			'captionSpacingTop',
			'captionSpacingBottom',
			'tableResponsiveMode',
		];
	}

	public function get_css_variable( string $property_id ): ?string {
		return isset( self::CSS_VARIABLES[ $property_id ] ) ? self::CSS_VARIABLES[ $property_id ] : null;
	}

	public function get_style_engine_path( string $property_id ): ?array {
		return isset( self::STYLE_ENGINE_PATHS[ $property_id ] ) ? self::STYLE_ENGINE_PATHS[ $property_id ] : null;
	}

	/**
	 * Semantic native Core Table parts used by rendering and documentation.
	 *
	 * @return array<string,string>
	 */
	public function get_selectors(): array {
		return [
			'wrapper' => '.wp-block-table',
			'table'   => '.wp-block-table > table',
			'head'    => '.wp-block-table > table > thead',
			'body'    => '.wp-block-table > table > tbody',
			'foot'    => '.wp-block-table > table > tfoot',
			'row'     => '.wp-block-table > table tr',
			'header'  => '.wp-block-table > table th',
			'cell'    => '.wp-block-table > table td',
			'caption' => '.wp-block-table > figcaption',
		];
	}
}
