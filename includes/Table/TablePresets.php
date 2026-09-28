<?php
namespace GutenStyle\Table;

use GutenStyle\Styles\Schema\PresetDefinition;

final class TablePresets {
	/**
	 * @return PresetDefinition[]
	 */
	public static function all(): array {
		$module = TableAdapter::BLOCK_NAME;

		return [
			new PresetDefinition( 'table-theme-default', __( 'Theme Default', 'gutenstyle' ), $module, [] ),
			new PresetDefinition(
				'table-clean',
				__( 'Clean', 'gutenstyle' ),
				$module,
				[
					'tableBorderStyle'         => 'solid',
					'tableBorderWidth'         => '1px',
					'tableBorderColor'         => '#e2e8f0',
					'tableBorderRadius'        => '8px',
					'tableBackgroundColor'     => '#ffffff',
					'cellPaddingHorizontal'    => '12px',
					'cellPaddingVertical'      => '10px',
					'cellTextColor'            => '#334155',
					'headerBackgroundColor'    => '#f8fafc',
					'headerTextColor'          => '#0f172a',
					'headerFontWeight'         => '600',
					'dividerColor'             => '#e2e8f0',
					'captionTextColor'         => '#64748b',
					'captionFontSize'          => '14px',
					'captionSpacingTop'        => '8px',
					'tableResponsiveMode'      => 'horizontal-scroll',
				]
			),
			new PresetDefinition(
				'table-bordered',
				__( 'Bordered', 'gutenstyle' ),
				$module,
				[
					'tableBorderStyle'         => 'solid',
					'tableBorderWidth'         => '1px',
					'tableBorderColor'         => '#94a3b8',
					'tableBorderRadius'        => '4px',
					'cellPaddingHorizontal'    => '12px',
					'cellPaddingVertical'      => '10px',
					'headerBackgroundColor'    => '#f1f5f9',
					'headerTextColor'          => '#0f172a',
					'headerFontWeight'         => '700',
					'dividerColor'             => '#94a3b8',
					'tableResponsiveMode'      => 'horizontal-scroll',
				]
			),
			new PresetDefinition(
				'table-striped',
				__( 'Striped', 'gutenstyle' ),
				$module,
				[
					'tableBorderStyle'          => 'solid',
					'tableBorderWidth'          => '1px',
					'tableBorderColor'          => '#e2e8f0',
					'cellPaddingHorizontal'     => '12px',
					'cellPaddingVertical'       => '10px',
					'headerBackgroundColor'     => '#0f172a',
					'headerTextColor'           => '#ffffff',
					'headerFontWeight'          => '600',
					'stripedRows'               => true,
					'stripedRowBackgroundColor' => '#f8fafc',
					'dividerColor'              => '#e2e8f0',
					'tableResponsiveMode'       => 'horizontal-scroll',
				]
			),
			new PresetDefinition(
				'table-editorial',
				__( 'Editorial', 'gutenstyle' ),
				$module,
				[
					'tableBorderStyle'         => 'none',
					'tableBorderWidth'         => '0',
					'cellPaddingHorizontal'    => '4px',
					'cellPaddingVertical'      => '14px',
					'cellTextColor'            => '#292524',
					'headerBackgroundColor'    => '#ffffff',
					'headerTextColor'          => '#1c1917',
					'headerFontWeight'         => '700',
					'dividerColor'             => '#d6d3d1',
					'captionTextColor'         => '#78716c',
					'captionFontSize'          => '14px',
					'captionTextAlign'         => 'left',
					'captionSpacingTop'        => '10px',
					'tableResponsiveMode'      => 'horizontal-scroll',
				]
			),
			new PresetDefinition(
				'table-comparison',
				__( 'Comparison', 'gutenstyle' ),
				$module,
				[
					'tableBorderStyle'         => 'solid',
					'tableBorderWidth'         => '1px',
					'tableBorderColor'         => '#cbd5e1',
					'tableBorderRadius'        => '10px',
					'cellPaddingHorizontal'    => '16px',
					'cellPaddingVertical'      => '12px',
					'cellTextAlign'            => 'center',
					'headerBackgroundColor'    => '#1d4ed8',
					'headerTextColor'          => '#ffffff',
					'headerFontWeight'         => '700',
					'headerTextAlign'          => 'center',
					'hoverRows'                => true,
					'hoverRowBackgroundColor'  => '#eff6ff',
					'dividerColor'             => '#cbd5e1',
					'captionTextAlign'         => 'center',
					'captionSpacingTop'        => '10px',
					'tableResponsiveMode'      => 'horizontal-scroll',
				]
			),
		];
	}
}
