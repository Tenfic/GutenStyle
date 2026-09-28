<?php
declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/wordpress/' );
define( 'GUTENSTYLE_PATH', dirname( __DIR__, 2 ) . '/' );

$GLOBALS['gs_test_actions']          = [];
$GLOBALS['gs_test_caps']             = [];
$GLOBALS['gs_test_options']          = [];
$GLOBALS['gs_test_option_autoload']  = [];
$GLOBALS['gs_test_post_meta']        = [];
$GLOBALS['gs_test_registered_meta']  = [];
$GLOBALS['gs_test_registered_routes'] = [];

function add_action( string $hook, callable $callback, int $priority = 10 ): void {
	$GLOBALS['gs_test_actions'][ $hook ][ $priority ][] = $callback;
}

/** @param mixed ...$args */
function do_action( string $hook, ...$args ): void {
	if ( ! isset( $GLOBALS['gs_test_actions'][ $hook ] ) ) {
		return;
	}
	ksort( $GLOBALS['gs_test_actions'][ $hook ] );
	foreach ( $GLOBALS['gs_test_actions'][ $hook ] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$callback( ...$args );
		}
	}
}

/** @param mixed $value */
function sanitize_text_field( $value ): string {
	return trim( strip_tags( (string) $value ) );
}

function __( string $text, string $domain = '' ): string {
	unset( $domain );
	return $text;
}

function current_user_can( string $capability, ...$args ): bool {
	unset( $args );
	return ! empty( $GLOBALS['gs_test_caps'][ $capability ] );
}

/** @param mixed $default @return mixed */
function get_option( string $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['gs_test_options'] )
		? $GLOBALS['gs_test_options'][ $name ]
		: $default;
}

/** @param mixed $value */
function add_option( string $name, $value, string $deprecated = '', bool $autoload = true ): bool {
	unset( $deprecated );
	if ( array_key_exists( $name, $GLOBALS['gs_test_options'] ) ) {
		return false;
	}
	$GLOBALS['gs_test_options'][ $name ]         = $value;
	$GLOBALS['gs_test_option_autoload'][ $name ] = $autoload;
	return true;
}

/** @param mixed $value */
function update_option( string $name, $value, bool $autoload = true ): bool {
	$changed = ! array_key_exists( $name, $GLOBALS['gs_test_options'] )
		|| $GLOBALS['gs_test_options'][ $name ] !== $value;
	$GLOBALS['gs_test_options'][ $name ]         = $value;
	$GLOBALS['gs_test_option_autoload'][ $name ] = $autoload;
	return $changed;
}

function delete_option( string $name ): bool {
	$existed = array_key_exists( $name, $GLOBALS['gs_test_options'] );
	unset( $GLOBALS['gs_test_options'][ $name ], $GLOBALS['gs_test_option_autoload'][ $name ] );
	return $existed;
}

/** @return mixed */
function get_post_meta( int $post_id, string $key, bool $single = false ) {
	unset( $single );
	return isset( $GLOBALS['gs_test_post_meta'][ $post_id ][ $key ] )
		? $GLOBALS['gs_test_post_meta'][ $post_id ][ $key ]
		: '';
}

/** @param mixed $value @return int|bool */
function update_post_meta( int $post_id, string $key, $value ) {
	$GLOBALS['gs_test_post_meta'][ $post_id ][ $key ] = $value;
	return 1;
}

function delete_post_meta( int $post_id, string $key ): bool {
	unset( $GLOBALS['gs_test_post_meta'][ $post_id ][ $key ] );
	return true;
}

/** @return string[] */
function get_post_types( array $args = [], string $output = 'names' ): array {
	unset( $args, $output );
	return [ 'post', 'page', 'book' ];
}

function post_type_supports( string $post_type, string $feature ): bool {
	unset( $post_type );
	return in_array( $feature, [ 'editor', 'revisions' ], true );
}

function use_block_editor_for_post_type( string $post_type ): bool {
	unset( $post_type );
	return true;
}

function register_post_meta( string $post_type, string $key, array $args ): bool {
	$GLOBALS['gs_test_registered_meta'][ $post_type ][ $key ] = $args;
	return true;
}

function register_rest_route( string $namespace, string $route, array $args ): bool {
	$GLOBALS['gs_test_registered_routes'][ $namespace . $route ] = $args;
	return true;
}

class WP_REST_Request {
	/** @var mixed */
	private $json;

	/** @param mixed $json */
	public function __construct( $json = null ) {
		$this->json = $json;
	}

	/** @return mixed */
	public function get_json_params() {
		return $this->json;
	}
}

class WP_REST_Response {
	/** @var mixed */
	private $data;
	private int $status;

	/** @param mixed $data */
	public function __construct( $data = null, int $status = 200 ) {
		$this->data   = $data;
		$this->status = $status;
	}

	/** @return mixed */
	public function get_data() {
		return $this->data;
	}

	public function get_status(): int {
		return $this->status;
	}
}

class WP_Error {
	private string $code;
	private string $message;
	/** @var mixed */
	private $data;

	/** @param mixed $data */
	public function __construct( string $code, string $message, $data = null ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	public function get_error_code(): string {
		return $this->code;
	}

	public function get_error_message(): string {
		return $this->message;
	}

	/** @return mixed */
	public function get_error_data() {
		return $this->data;
	}
}

require_once GUTENSTYLE_PATH . 'includes/Core/Autoloader.php';
\GutenStyle\Core\Autoloader::register();

/** @var array<string,callable> */
$GLOBALS['gs_tests'] = [];

function gs_test( string $name, callable $test ): void {
	$GLOBALS['gs_tests'][ $name ] = $test;
}

/** @param mixed $expected @param mixed $actual */
function gs_assert_same( $expected, $actual, string $message = '' ): void {
	if ( $expected !== $actual ) {
		throw new RuntimeException(
			( $message ? $message . ': ' : '' )
			. 'expected ' . var_export( $expected, true )
			. ', got ' . var_export( $actual, true )
		);
	}
}

/** @param mixed $condition */
function gs_assert_true( $condition, string $message = '' ): void {
	if ( true !== $condition ) {
		throw new RuntimeException( $message ?: 'Expected true.' );
	}
}

function gs_assert_throws( callable $callback, string $exception_class ): void {
	try {
		$callback();
	} catch ( Throwable $error ) {
		if ( $error instanceof $exception_class ) {
			return;
		}
		throw new RuntimeException( 'Unexpected exception: ' . get_class( $error ) . ' ' . $error->getMessage() );
	}
	throw new RuntimeException( 'Expected exception ' . $exception_class . '.' );
}
