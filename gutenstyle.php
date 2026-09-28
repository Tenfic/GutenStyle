<?php
/**
 * Plugin Name:       GutenStyle
 * Plugin URI:        https://gutenstyle.com/
 * Description:       Visual styling for native Gutenberg blocks, globally, per post, or per block.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Tenfic
 * Author URI:        https://tenfic.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gutenstyle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GUTENSTYLE_VERSION', '0.1.0' );
define( 'GUTENSTYLE_FILE', __FILE__ );
define( 'GUTENSTYLE_PATH', plugin_dir_path( __FILE__ ) );
define( 'GUTENSTYLE_URL', plugin_dir_url( __FILE__ ) );

require_once GUTENSTYLE_PATH . 'includes/Core/Autoloader.php';

\GutenStyle\Core\Autoloader::register();

/**
 * Public access to the GutenStyle Free engine for block modules and add-ons.
 */
function gutenstyle_style_engine(): ?\GutenStyle\Styles\StyleEngine {
	return \GutenStyle\Styles\StyleModule::engine();
}

/**
 * Public access to the extensible registry after GutenStyle has booted.
 */
function gutenstyle_style_registry(): ?\GutenStyle\Styles\StyleRegistry {
	return \GutenStyle\Styles\StyleModule::registry();
}

add_action(
	'plugins_loaded',
	static function () {
		$plugin = new \GutenStyle\Core\Plugin();
		$plugin->boot();
	}
);
