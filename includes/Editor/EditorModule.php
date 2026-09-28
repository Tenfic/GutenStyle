<?php
namespace GutenStyle\Editor;

use GutenStyle\Contracts\ModuleInterface;

final class EditorModule implements ModuleInterface {
	public function register(): void {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets(): void {
		$asset_file = GUTENSTYLE_PATH . 'build/editor.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			'gutenstyle-editor',
			GUTENSTYLE_URL . 'build/editor.js',
			$asset['dependencies'] ?? [],
			$asset['version'] ?? GUTENSTYLE_VERSION,
			true
		);
	}
}
