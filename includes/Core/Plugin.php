<?php
namespace GutenStyle\Core;

use GutenStyle\Admin\AdminModule;
use GutenStyle\Editor\EditorModule;
use GutenStyle\REST\RestModule;
use GutenStyle\Styles\StyleModule;

final class Plugin {
	/** @var array<object> */
	private array $modules = [];

	public function boot(): void {
		$this->modules = [
			new StyleModule(),
			new RestModule(),
			new AdminModule(),
			new EditorModule(),
		];

		foreach ( $this->modules as $module ) {
			if ( method_exists( $module, 'register' ) ) {
				$module->register();
			}
		}

		/**
		 * Fires after GutenStyle Free has registered its core services.
		 *
		 * Pro and third-party extensions should register against public APIs here.
		 */
		do_action( 'gutenstyle/loaded', $this );
	}
}
