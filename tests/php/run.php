<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use GutenStyle\REST\RestModule;
use GutenStyle\Styles\Block\BlockStyleAttributes;
use GutenStyle\Styles\Compiler\CssVariableCompiler;
use GutenStyle\Styles\Contracts\BlockAdapterInterface;
use GutenStyle\Styles\Schema\PresetDefinition;
use GutenStyle\Styles\Schema\PropertyDefinition;
use GutenStyle\Styles\Storage\GlobalStyleStore;
use GutenStyle\Styles\Storage\PostStyleStore;
use GutenStyle\Styles\StyleModule;
use GutenStyle\Styles\StyleRegistry;
use GutenStyle\Styles\StyleResolver;
use GutenStyle\Styles\StyleSanitizer;
use GutenStyle\Styles\Value\ScopeStyle;

final class ExampleBlockAdapter implements BlockAdapterInterface {
	public function get_name(): string {
		return 'example/card';
	}

	public function get_supported_properties(): array {
		return [ 'radius', 'padding', 'headerColor', 'mode', 'enabled', 'count', 'safeName' ];
	}

	public function get_css_variable( string $property_id ): ?string {
		return 'radius' === $property_id ? '--gutenstyle-example-radius' : null;
	}

	public function get_style_engine_path( string $property_id ): ?array {
		return 'headerColor' === $property_id ? [ 'color', 'text' ] : null;
	}
}

function gs_fixture_registry(): StyleRegistry {
	$registry = new StyleRegistry();
	$module   = 'example/card';
	$registry->register_property(
		new PropertyDefinition( 'radius', 'Radius', PropertyDefinition::TYPE_DIMENSION, [ 'modules' => [ $module ] ] )
	);
	$registry->register_property(
		new PropertyDefinition(
			'padding',
			'Padding',
			PropertyDefinition::TYPE_DIMENSION,
			[ 'modules' => [ $module ], 'css_variables' => [ $module => '--gutenstyle-example-padding' ] ]
		)
	);
	$registry->register_property(
		new PropertyDefinition( 'headerColor', 'Header color', PropertyDefinition::TYPE_COLOR, [ 'modules' => [ $module ] ] )
	);
	$registry->register_property(
		new PropertyDefinition(
			'mode',
			'Mode',
			PropertyDefinition::TYPE_ENUM,
			[ 'modules' => [ $module ], 'allowed_values' => [ 'clean', 'strong' ] ]
		)
	);
	$registry->register_property(
		new PropertyDefinition( 'enabled', 'Enabled', PropertyDefinition::TYPE_BOOLEAN, [ 'modules' => [ $module ] ] )
	);
	$registry->register_property(
		new PropertyDefinition(
			'count',
			'Count',
			PropertyDefinition::TYPE_NUMBER,
			[ 'modules' => [ $module ], 'min' => 0, 'max' => 10 ]
		)
	);
	$registry->register_property(
		new PropertyDefinition(
			'safeName',
			'Safe name',
			PropertyDefinition::TYPE_STRING,
			[ 'modules' => [ $module ], 'pattern' => '/^[a-z ]+$/' ]
		)
	);
	$registry->register_block_adapter( new ExampleBlockAdapter() );
	$registry->register_preset(
		new PresetDefinition(
			'example-clean',
			'Clean',
			$module,
			[ 'radius' => '8px', 'padding' => '14px', 'headerColor' => '#000000' ]
		)
	);
	return $registry;
}

gs_test(
	'resolver merges Global, Post, and Block property by property',
	static function (): void {
		$resolver = new StyleResolver( gs_fixture_registry() );
		$result   = $resolver->resolve(
			'example/card',
			new ScopeStyle( null, [ 'radius' => '8px', 'padding' => '14px', 'headerColor' => '#000000' ] ),
			new ScopeStyle( null, [ 'headerColor' => '#800080' ] ),
			new ScopeStyle( null, [ 'radius' => '16px' ] )
		);

		gs_assert_same( '16px', $result->get_property( 'radius' )->get_value() );
		gs_assert_same( 'block', $result->get_property( 'radius' )->get_source() );
		gs_assert_same( '14px', $result->get_property( 'padding' )->get_value() );
		gs_assert_same( 'global', $result->get_property( 'padding' )->get_source() );
		gs_assert_same( '#800080', $result->get_property( 'headerColor' )->get_value() );
		gs_assert_same( 'post', $result->get_property( 'headerColor' )->get_source() );
	}
);

gs_test(
	'removing a Block override restores Global inheritance',
	static function (): void {
		$resolver = new StyleResolver( gs_fixture_registry() );
		$block    = ( new ScopeStyle( null, [ 'radius' => '16px' ] ) )->without_property( 'radius' );
		$result   = $resolver->resolve(
			'example/card',
			new ScopeStyle( null, [ 'radius' => '8px' ] ),
			ScopeStyle::empty(),
			$block
		);

		gs_assert_same( '8px', $result->get_property( 'radius' )->get_value() );
		gs_assert_same( 'global', $result->get_property( 'radius' )->get_source() );
	}
);

gs_test(
	'a partial Post scope leaves other properties inherited',
	static function (): void {
		$result = ( new StyleResolver( gs_fixture_registry() ) )->resolve(
			'example/card',
			new ScopeStyle( null, [ 'radius' => '8px', 'padding' => '14px' ] ),
			new ScopeStyle( null, [ 'radius' => '10px' ] ),
			ScopeStyle::empty()
		);
		gs_assert_same( '10px', $result->get_property( 'radius' )->get_value() );
		gs_assert_same( '14px', $result->get_property( 'padding' )->get_value() );
		gs_assert_same( 'global', $result->get_property( 'padding' )->get_source() );
	}
);

gs_test(
	'no GutenStyle value remains theme-owned',
	static function (): void {
		$result = ( new StyleResolver( gs_fixture_registry() ) )->resolve(
			'example/card',
			ScopeStyle::empty(),
			ScopeStyle::empty(),
			ScopeStyle::empty()
		);
		gs_assert_same( null, $result->get_property( 'radius' )->get_value() );
		gs_assert_same( 'theme', $result->get_property( 'radius' )->get_source() );
		gs_assert_same( false, $result->get_property( 'radius' )->is_resolved() );
	}
);

gs_test(
	'unregistered resolver properties are rejected',
	static function (): void {
		gs_assert_throws(
			static function (): void {
				( new StyleResolver( gs_fixture_registry() ) )->resolve(
					'example/card',
					new ScopeStyle( null, [ 'rawCss' => 'display:none' ] ),
					ScopeStyle::empty(),
					ScopeStyle::empty()
				);
			},
			InvalidArgumentException::class
		);
	}
);

gs_test(
	'presets merge through the same property resolver',
	static function (): void {
		$result = ( new StyleResolver( gs_fixture_registry() ) )->resolve(
			'example/card',
			new ScopeStyle( 'example-clean' ),
			new ScopeStyle( null, [ 'headerColor' => '#800080' ] ),
			ScopeStyle::empty()
		);
		gs_assert_same( '8px', $result->get_property( 'radius' )->get_value() );
		gs_assert_same( 'preset', $result->get_property( 'radius' )->get_via() );
		gs_assert_same( 'example-clean', $result->get_property( 'radius' )->get_preset() );
		gs_assert_same( '#800080', $result->get_property( 'headerColor' )->get_value() );
		gs_assert_same( 'post', $result->get_property( 'headerColor' )->get_source() );
	}
);

gs_test(
	'removing a Post override restores Global',
	static function (): void {
		$post   = ( new ScopeStyle( null, [ 'radius' => '12px' ] ) )->without_property( 'radius' );
		$result = ( new StyleResolver( gs_fixture_registry() ) )->resolve(
			'example/card',
			new ScopeStyle( null, [ 'radius' => '8px' ] ),
			$post,
			ScopeStyle::empty()
		);
		gs_assert_same( '8px', $result->get_property( 'radius' )->get_value() );
		gs_assert_same( 'global', $result->get_property( 'radius' )->get_source() );
	}
);

gs_test(
	'Block overrides do not leak between block instances',
	static function (): void {
		$resolver = new StyleResolver( gs_fixture_registry() );
		$global   = new ScopeStyle( null, [ 'radius' => '8px' ] );
		$block_a  = $resolver->resolve( 'example/card', $global, ScopeStyle::empty(), new ScopeStyle( null, [ 'radius' => '16px' ] ) );
		$block_b  = $resolver->resolve( 'example/card', $global, ScopeStyle::empty(), ScopeStyle::empty() );
		gs_assert_same( '16px', $block_a->get_property( 'radius' )->get_value() );
		gs_assert_same( '8px', $block_b->get_property( 'radius' )->get_value() );
		gs_assert_same( 'global', $block_b->get_property( 'radius' )->get_source() );
	}
);

gs_test(
	'sanitizer accepts valid typed values',
	static function (): void {
		$style = ( new StyleSanitizer( gs_fixture_registry() ) )->sanitize_scope_style(
			'example/card',
			[
				'properties' => [
					'enabled'     => 'false',
					'mode'        => 'clean',
					'headerColor' => '#ABCDEF',
					'count'       => '4',
					'radius'      => '1.5rem',
					'safeName'    => 'safe name',
				],
			]
		);
		gs_assert_same( false, $style->get_properties()['enabled'] );
		gs_assert_same( '#abcdef', $style->get_properties()['headerColor'] );
		gs_assert_same( 4, $style->get_properties()['count'] );
	}
);

gs_test(
	'sanitizer rejects invalid colors enums booleans dimensions numbers strings and unknown properties',
	static function (): void {
		$sanitizer = new StyleSanitizer( gs_fixture_registry() );
		$invalid   = [
			'headerColor' => 'red;display:none',
			'mode'        => 'unknown',
			'enabled'     => 'yes',
			'radius'      => 'calc(1px + 2px)',
			'count'       => 99,
			'safeName'    => '<script>',
			'unknown'     => 'value',
		];
		foreach ( $invalid as $property => $value ) {
			gs_assert_throws(
				static function () use ( $sanitizer, $property, $value ): void {
					$sanitizer->sanitize_scope_style( 'example/card', [ 'properties' => [ $property => $value ] ] );
				},
				InvalidArgumentException::class
			);
		}
	}
);

gs_test(
	'property definitions reject invalid constraints and scope versions',
	static function (): void {
		gs_assert_throws(
			static function (): void {
				new PropertyDefinition(
					'broken',
					'Broken',
					PropertyDefinition::TYPE_STRING,
					[ 'pattern' => '/[/' ]
				);
			},
			InvalidArgumentException::class
		);
		gs_assert_throws(
			static function (): void {
				new PropertyDefinition(
					'brokenRange',
					'Broken range',
					PropertyDefinition::TYPE_NUMBER,
					[ 'min' => 10, 'max' => 1 ]
				);
			},
			InvalidArgumentException::class
		);
		gs_assert_throws(
			static function (): void {
				( new StyleResolver( gs_fixture_registry() ) )->resolve(
					'example/card',
					new ScopeStyle( null, [], 2 ),
					ScopeStyle::empty(),
					ScopeStyle::empty()
				);
			},
			InvalidArgumentException::class
		);
	}
);

gs_test(
	'registry retrieves future component and profile definitions',
	static function (): void {
		$registry = new StyleRegistry();
		$registry->register_component( 'notice', [ 'label' => 'Notice' ] );
		$registry->register_profile( 'editorial', [ 'label' => 'Editorial' ] );
		gs_assert_same( [ 'label' => 'Notice' ], $registry->get_component( 'notice' ) );
		gs_assert_same( [ 'label' => 'Editorial' ], $registry->get_profile( 'editorial' ) );
	}
);

gs_test(
	'Block attribute reset removes only the explicit value',
	static function (): void {
		$sanitizer = new StyleSanitizer( gs_fixture_registry() );
		$attrs     = [
			'className'  => 'native-class',
			'gutenstyle' => [
				'version'    => 1,
				'preset'     => null,
				'properties' => [ 'radius' => '16px' ],
			],
		];
		$reset = BlockStyleAttributes::reset_property( 'example/card', $attrs, 'radius', $sanitizer );
		gs_assert_same( [ 'className' => 'native-class' ], $reset );
	}
);

gs_test(
	'compiler emits only registered custom properties',
	static function (): void {
		$registry = gs_fixture_registry();
		$style    = ( new StyleResolver( $registry ) )->resolve(
			'example/card',
			new ScopeStyle( null, [ 'radius' => '8px', 'padding' => '14px', 'headerColor' => '#000000' ] ),
			ScopeStyle::empty(),
			ScopeStyle::empty()
		);
		$css = ( new CssVariableCompiler( $registry ) )->compile( $style );
		gs_assert_same( '8px', $css['--gutenstyle-example-radius'] );
		gs_assert_same( '14px', $css['--gutenstyle-example-padding'] );
		gs_assert_same( false, isset( $css['headerColor'] ) );
	}
);

gs_test(
	'Global storage requires administrative capability and disables autoload',
	static function (): void {
		$GLOBALS['gs_test_options'] = [];
		$store = new GlobalStyleStore( new StyleSanitizer( gs_fixture_registry() ) );
		$GLOBALS['gs_test_caps']['manage_options'] = false;
		gs_assert_same( false, $store->save_style( 'example/card', new ScopeStyle( null, [ 'radius' => '8px' ] ) ) );
		gs_assert_same( [], $GLOBALS['gs_test_options'] );

		$GLOBALS['gs_test_caps']['manage_options'] = true;
		gs_assert_same( true, $store->save_style( 'example/card', new ScopeStyle( null, [ 'radius' => '8px' ] ) ) );
		gs_assert_same( false, $GLOBALS['gs_test_option_autoload'][ GlobalStyleStore::OPTION_NAME ] );
		gs_assert_same( '8px', $store->get_style( 'example/card' )->get_properties()['radius'] );
	}
);

gs_test(
	'Post storage requires edit_post and removes empty meta',
	static function (): void {
		$GLOBALS['gs_test_post_meta'] = [];
		$store = new PostStyleStore( new StyleSanitizer( gs_fixture_registry() ) );
		$GLOBALS['gs_test_caps']['edit_post'] = false;
		gs_assert_same( false, $store->save_style( 42, 'example/card', new ScopeStyle( null, [ 'radius' => '12px' ] ) ) );
		$GLOBALS['gs_test_caps']['edit_post'] = true;
		gs_assert_same( true, $store->save_style( 42, 'example/card', new ScopeStyle( null, [ 'radius' => '12px' ] ) ) );
		gs_assert_same( '12px', $store->get_style( 42, 'example/card' )->get_properties()['radius'] );
		gs_assert_same( true, $store->reset_property( 42, 'example/card', 'radius' ) );
		gs_assert_same( '', get_post_meta( 42, PostStyleStore::META_KEY, true ) );
	}
);

gs_test(
	'Free registration hooks accept an external property adapter and preset',
	static function (): void {
		$GLOBALS['gs_test_actions'] = [];
		add_action(
			'gutenstyle/register_style_properties',
			static function ( StyleRegistry $registry ): void {
				foreach ( gs_fixture_registry()->all_properties() as $property ) {
					$registry->register_property( $property );
				}
			}
		);
		add_action(
			'gutenstyle/register_block_adapters',
			static function ( StyleRegistry $registry ): void {
				$registry->register_block_adapter( new ExampleBlockAdapter() );
			}
		);
		add_action(
			'gutenstyle/register_style_presets',
			static function ( StyleRegistry $registry ): void {
				$registry->register_preset(
					new PresetDefinition(
						'extension-clean',
						'Extension clean',
						'example/card',
						[ 'radius' => '6px' ]
					)
				);
			}
		);

		( new StyleModule() )->register();
		gs_assert_true( null !== StyleModule::registry()->get_preset( 'extension-clean' ) );
		gs_assert_true( null !== StyleModule::engine() );
	}
);

gs_test(
	'REST permissions and malformed Global writes are enforced',
	static function (): void {
		$rest = new RestModule();
		$GLOBALS['gs_test_caps']['edit_posts']    = false;
		$GLOBALS['gs_test_caps']['manage_options'] = false;
		gs_assert_same( false, $rest->can_read_styles() );
		gs_assert_same( false, $rest->can_manage_global_styles() );

		$GLOBALS['gs_test_caps']['manage_options'] = true;
		$error = $rest->update_global_styles(
			new WP_REST_Request(
				[
					'version' => 1,
					'blocks'  => [ 'example/card' => [ 'properties' => [ 'rawCss' => 'display:none' ] ] ],
				]
			)
		);
		gs_assert_true( $error instanceof WP_Error );
		gs_assert_same( 'gutenstyle_invalid_styles', $error->get_error_code() );
		gs_assert_same( 400, $error->get_error_data()['status'] );
	}
);

$passed = 0;
$failed = 0;
foreach ( $GLOBALS['gs_tests'] as $name => $test ) {
	try {
		$test();
		++$passed;
		echo "PASS: {$name}\n";
	} catch ( Throwable $error ) {
		++$failed;
		echo "FAIL: {$name}\n  {$error->getMessage()}\n";
	}
}

echo "\n{$passed} passed, {$failed} failed\n";
exit( 0 === $failed ? 0 : 1 );
