<?php
namespace GutenStyle\Admin;

use GutenStyle\Contracts\ModuleInterface;

final class AdminModule implements ModuleInterface {
	private const PAGE_SLUG = 'gutenstyle';

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'GutenStyle', 'gutenstyle' ),
			__( 'GutenStyle', 'gutenstyle' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ],
			'dashicons-art',
			58
		);
	}

	public function render_page(): void {
		echo '<div class="wrap"><div id="gutenstyle-admin-app"></div></div>';
	}

	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		$asset_file = GUTENSTYLE_PATH . 'build/admin.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			'gutenstyle-admin',
			GUTENSTYLE_URL . 'build/admin.js',
			$asset['dependencies'] ?? [],
			$asset['version'] ?? GUTENSTYLE_VERSION,
			true
		);

		$css_file = GUTENSTYLE_PATH . 'build/admin.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'gutenstyle-admin',
				GUTENSTYLE_URL . 'build/admin.css',
				[],
				(string) filemtime( $css_file )
			);
		}
	}
}
