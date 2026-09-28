<?php
namespace GutenStyle\Styles;

use GutenStyle\Contracts\ModuleInterface;

final class StyleModule implements ModuleInterface {
	private static ?StyleRegistry $registry = null;

	public function register(): void {
		self::$registry = new StyleRegistry();

		// First proof-of-architecture preset.
		self::$registry->register_preset(
			'table-clean',
			[
				'label'      => __( 'Clean', 'gutenstyle' ),
				'block'      => 'core/table',
				'properties' => [
					'borderRadius' => '8px',
				],
			]
		);

		do_action( 'gutenstyle/register_styles', self::$registry );
	}

	public static function registry(): ?StyleRegistry {
		return self::$registry;
	}
}
