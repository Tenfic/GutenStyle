<?php
declare(strict_types=1);

use GutenStyle\Styles\Block\BlockStyleAttributes;
use GutenStyle\Styles\Compiler\CssVariableCompiler;
use GutenStyle\Styles\Compiler\WordPressStyleEngineCompiler;
use GutenStyle\Styles\Storage\GlobalStyleStore;
use GutenStyle\Styles\Storage\PostStyleStore;
use GutenStyle\Styles\StyleRegistry;
use GutenStyle\Styles\StyleResolver;
use GutenStyle\Styles\StyleSanitizer;
use GutenStyle\Styles\Value\ScopeStyle;
use GutenStyle\Table\TableAdapter;
use GutenStyle\Table\TableModule;
use GutenStyle\Table\TableRenderer;

function gs_table_engine(): \GutenStyle\Styles\StyleEngine {
	$registry   = gs_table_registry();
	$sanitizer  = new StyleSanitizer( $registry );
	$global     = new GlobalStyleStore( $sanitizer );
	$post       = new PostStyleStore( $sanitizer );
	return new \GutenStyle\Styles\StyleEngine( $registry, $sanitizer, $global, $post );
}

function gs_native_table_markup(): string {
	return '<figure class="wp-block-table"><table><thead><tr><th scope="col">Plan</th><th scope="col">Price</th></tr></thead><tbody><tr><td>Starter</td><td>$9</td></tr></tbody></table><figcaption class="wp-element-caption">Plan comparison</figcaption></figure>';
}

function gs_table_registry(): StyleRegistry {
	$registry = new StyleRegistry();
	$module   = new TableModule();
	$module->register_properties( $registry );
	$module->register_adapter( $registry );
	$module->register_presets( $registry );
	return $registry;
}

gs_test(
	'Table adapter properties and presets register through the generic registry',
	static function (): void {
		$registry = gs_table_registry();
		$adapter  = $registry->get_block_adapter( TableAdapter::BLOCK_NAME );
		gs_assert_true( $adapter instanceof TableAdapter );
		gs_assert_same( 26, count( $registry->properties_for_module( TableAdapter::BLOCK_NAME ) ) );
		foreach (
			[
				'table-theme-default',
				'table-clean',
				'table-bordered',
				'table-striped',
				'table-editorial',
				'table-comparison',
			] as $preset_id
		) {
			gs_assert_true( null !== $registry->get_preset( $preset_id ), $preset_id . ' is not registered.' );
		}
		gs_assert_same( '.wp-block-table > table > thead', $adapter->get_selectors()['head'] );
		gs_assert_same( '.wp-block-table > figcaption', $adapter->get_selectors()['caption'] );
	}
);

gs_test(
	'Table Global Post and Block values resolve with property origins',
	static function (): void {
		$result = ( new StyleResolver( gs_table_registry() ) )->resolve(
			TableAdapter::BLOCK_NAME,
			new ScopeStyle( null, [ 'tableBorderRadius' => '8px', 'cellPaddingHorizontal' => '14px' ] ),
			new ScopeStyle( null, [ 'headerTextColor' => '#800080' ] ),
			new ScopeStyle( null, [ 'tableBorderRadius' => '16px' ] )
		);
		gs_assert_same( '16px', $result->get_property( 'tableBorderRadius' )->get_value() );
		gs_assert_same( 'block', $result->get_property( 'tableBorderRadius' )->get_source() );
		gs_assert_same( '14px', $result->get_property( 'cellPaddingHorizontal' )->get_value() );
		gs_assert_same( 'global', $result->get_property( 'cellPaddingHorizontal' )->get_source() );
		gs_assert_same( '#800080', $result->get_property( 'headerTextColor' )->get_value() );
		gs_assert_same( 'post', $result->get_property( 'headerTextColor' )->get_source() );
	}
);

gs_test(
	'Table Block reset restores Post then Global inheritance',
	static function (): void {
		$resolver = new StyleResolver( gs_table_registry() );
		$global   = new ScopeStyle( null, [ 'tableBorderRadius' => '8px' ] );
		$post     = new ScopeStyle( null, [ 'tableBorderRadius' => '12px' ] );
		$block    = ( new ScopeStyle( null, [ 'tableBorderRadius' => '16px' ] ) )->without_property( 'tableBorderRadius' );
		$result   = $resolver->resolve( TableAdapter::BLOCK_NAME, $global, $post, $block );
		gs_assert_same( '12px', $result->get_property( 'tableBorderRadius' )->get_value() );
		gs_assert_same( 'post', $result->get_property( 'tableBorderRadius' )->get_source() );

		$result = $resolver->resolve(
			TableAdapter::BLOCK_NAME,
			$global,
			$post->without_property( 'tableBorderRadius' ),
			ScopeStyle::empty()
		);
		gs_assert_same( '8px', $result->get_property( 'tableBorderRadius' )->get_value() );
		gs_assert_same( 'global', $result->get_property( 'tableBorderRadius' )->get_source() );
	}
);

gs_test(
	'Two native Tables remain isolated at Block scope',
	static function (): void {
		$resolver = new StyleResolver( gs_table_registry() );
		$global   = new ScopeStyle( null, [ 'tableBorderRadius' => '8px' ] );
		$table_a  = $resolver->resolve(
			TableAdapter::BLOCK_NAME,
			$global,
			ScopeStyle::empty(),
			new ScopeStyle( null, [ 'tableBorderRadius' => '20px' ] )
		);
		$table_b = $resolver->resolve( TableAdapter::BLOCK_NAME, $global, ScopeStyle::empty(), ScopeStyle::empty() );
		gs_assert_same( '20px', $table_a->get_property( 'tableBorderRadius' )->get_value() );
		gs_assert_same( '8px', $table_b->get_property( 'tableBorderRadius' )->get_value() );
	}
);

gs_test(
	'Table preset values merge with explicit Post and Block properties',
	static function (): void {
		$result = ( new StyleResolver( gs_table_registry() ) )->resolve(
			TableAdapter::BLOCK_NAME,
			new ScopeStyle( 'table-clean' ),
			new ScopeStyle( null, [ 'headerBackgroundColor' => '#800080' ] ),
			new ScopeStyle( null, [ 'cellPaddingHorizontal' => '20px' ] )
		);
		gs_assert_same( '8px', $result->get_property( 'tableBorderRadius' )->get_value() );
		gs_assert_same( 'preset', $result->get_property( 'tableBorderRadius' )->get_via() );
		gs_assert_same( '#800080', $result->get_property( 'headerBackgroundColor' )->get_value() );
		gs_assert_same( 'post', $result->get_property( 'headerBackgroundColor' )->get_source() );
		gs_assert_same( '20px', $result->get_property( 'cellPaddingHorizontal' )->get_value() );
		gs_assert_same( 'block', $result->get_property( 'cellPaddingHorizontal' )->get_source() );
	}
);

gs_test(
	'Table sanitizer rejects invalid colors enums dimensions booleans and alignments',
	static function (): void {
		$sanitizer = new StyleSanitizer( gs_table_registry() );
		$invalid   = [
			'tableBorderColor'     => 'red',
			'tableBorderStyle'     => 'groove',
			'tableBorderWidth'     => '-1px',
			'stripedRows'          => 'sometimes',
			'cellTextAlign'        => 'diagonal',
			'cellVerticalAlign'    => 'baseline',
			'tableResponsiveMode'  => 'stacked',
		];
		foreach ( $invalid as $property_id => $value ) {
			gs_assert_throws(
				static function () use ( $sanitizer, $property_id, $value ): void {
					$sanitizer->sanitize_scope_style(
						TableAdapter::BLOCK_NAME,
						[ 'properties' => [ $property_id => $value ] ]
					);
				},
				InvalidArgumentException::class
			);
		}
	}
);

gs_test(
	'Theme Default Table preset produces no forced style output',
	static function (): void {
		$registry = gs_table_registry();
		$result   = ( new StyleResolver( $registry ) )->resolve(
			TableAdapter::BLOCK_NAME,
			new ScopeStyle( 'table-theme-default' ),
			ScopeStyle::empty(),
			ScopeStyle::empty()
		);
		gs_assert_same( 'table-theme-default', $result->get_preset() );
		gs_assert_same( [], ( new CssVariableCompiler( $registry ) )->compile( $result ) );
		gs_assert_same( [], ( new WordPressStyleEngineCompiler( $registry ) )->compile_style_object( $result ) );
	}
);

gs_test(
	'Table output mappings separate native Style Engine paths from custom properties',
	static function (): void {
		$registry = gs_table_registry();
		$result   = ( new StyleResolver( $registry ) )->resolve(
			TableAdapter::BLOCK_NAME,
			new ScopeStyle(
				null,
				[
					'tableBorderColor'       => '#123456',
					'tableBorderRadius'      => '6px',
					'cellPaddingHorizontal'  => '12px',
				]
			),
			ScopeStyle::empty(),
			ScopeStyle::empty()
		);
		$custom = ( new CssVariableCompiler( $registry ) )->compile( $result );
		$native = ( new WordPressStyleEngineCompiler( $registry ) )->compile_style_object( $result );
		gs_assert_same( '#123456', $native['border']['color'] );
		gs_assert_same( false, isset( $custom['--gutenstyle-table-border-color'] ) );
		gs_assert_same( '6px', $custom['--gutenstyle-table-border-radius'] );
		gs_assert_same( '12px', $custom['--gutenstyle-table-cell-padding-x'] );
	}
);

gs_test(
	'Table Global Post and Block storage round-trips and removes reset keys',
	static function (): void {
		$registry  = gs_table_registry();
		$sanitizer = new StyleSanitizer( $registry );
		$GLOBALS['gs_test_options']   = [];
		$GLOBALS['gs_test_post_meta'] = [];

		$global = new GlobalStyleStore( $sanitizer );
		$GLOBALS['gs_test_caps']['manage_options'] = false;
		gs_assert_same( false, $global->save_style( TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-clean' ) ) );
		$GLOBALS['gs_test_caps']['manage_options'] = true;
		gs_assert_same( true, $global->save_style( TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-clean', [ 'tableBorderRadius' => '9px' ] ) ) );
		gs_assert_same( '9px', $global->get_style( TableAdapter::BLOCK_NAME )->get_properties()['tableBorderRadius'] );
		gs_assert_same( true, $global->reset_property( TableAdapter::BLOCK_NAME, 'tableBorderRadius' ) );
		gs_assert_same( false, isset( $global->get_style( TableAdapter::BLOCK_NAME )->get_properties()['tableBorderRadius'] ) );

		$post = new PostStyleStore( $sanitizer );
		$GLOBALS['gs_test_caps']['edit_post'] = false;
		gs_assert_same( false, $post->save_style( 7, TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-striped' ) ) );
		$GLOBALS['gs_test_caps']['edit_post'] = true;
		gs_assert_same( true, $post->save_style( 7, TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-striped', [ 'hoverRows' => true ] ) ) );
		gs_assert_same( true, $post->get_style( 7, TableAdapter::BLOCK_NAME )->get_properties()['hoverRows'] );
	}
);

gs_test(
	'Table Block attribute contract preserves native attributes and removes empty scope',
	static function (): void {
		$sanitizer = new StyleSanitizer( gs_table_registry() );
		$native    = [ 'hasFixedLayout' => true, 'caption' => 'Native caption' ];
		$written   = BlockStyleAttributes::write(
			$native,
			new ScopeStyle( null, [ 'tableBorderRadius' => '16px' ] )
		);
		gs_assert_same( true, $written['hasFixedLayout'] );
		gs_assert_same( '16px', BlockStyleAttributes::read( TableAdapter::BLOCK_NAME, $written, $sanitizer )->get_properties()['tableBorderRadius'] );
		$reset = BlockStyleAttributes::reset_property( TableAdapter::BLOCK_NAME, $written, 'tableBorderRadius', $sanitizer );
		gs_assert_same( $native, $reset );
	}
);

gs_test(
	'Table block attribute registers without altering other native block settings',
	static function (): void {
		$module = new TableModule();
		$args   = $module->register_block_attribute( [ 'attributes' => [ 'caption' => [ 'type' => 'string' ] ] ], TableAdapter::BLOCK_NAME );
		gs_assert_same( 'string', $args['attributes']['caption']['type'] );
		gs_assert_same( 'object', $args['attributes']['gutenstyle']['type'] );
		$other = $module->register_block_attribute( [ 'attributes' => [] ], 'core/paragraph' );
		gs_assert_same( false, isset( $other['attributes']['gutenstyle'] ) );
	}
);

gs_test(
	'Table renderer leaves native markup untouched when no GutenStyle value resolves',
	static function (): void {
		$GLOBALS['gs_test_options']          = [];
		$GLOBALS['gs_test_post_meta']        = [];
		$GLOBALS['gs_test_current_post_id']  = 12;
		$markup   = gs_native_table_markup();
		$engine   = gs_table_engine();
		$renderer = new TableRenderer( $engine, new TableAdapter() );
		gs_assert_same( $markup, $renderer->render( $markup, [ 'attrs' => [] ] ) );
	}
);

gs_test(
	'Table renderer applies resolved native declarations and custom variables without rewriting content',
	static function (): void {
		$GLOBALS['gs_test_options']         = [];
		$GLOBALS['gs_test_post_meta']       = [];
		$GLOBALS['gs_test_current_post_id'] = 12;
		$GLOBALS['gs_test_caps']['manage_options'] = true;
		$GLOBALS['gs_test_caps']['edit_post']       = true;
		$engine = gs_table_engine();
		$engine->global_store()->save_style( TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-clean' ) );
		$engine->post_store()->save_style(
			12,
			TableAdapter::BLOCK_NAME,
			new ScopeStyle( null, [ 'headerBackgroundColor' => '#800080' ] )
		);

		$markup = gs_native_table_markup();
		$output = ( new TableRenderer( $engine, new TableAdapter() ) )->render(
			$markup,
			[
				'attrs' => [
					'gutenstyle' => [
						'version'    => 1,
						'preset'     => null,
						'properties' => [ 'tableBorderRadius' => '16px' ],
					],
				],
			]
		);
		gs_assert_true( false !== strpos( $output, 'gutenstyle-table' ) );
		gs_assert_true( false !== strpos( $output, '--gutenstyle-table-border-radius:16px;' ) );
		gs_assert_true( false !== strpos( $output, '--gutenstyle-table-header-background:#800080;' ) );
		gs_assert_true( false !== strpos( $output, 'border-color:#e2e8f0;' ) );
		gs_assert_true( false !== strpos( $output, '<thead><tr><th scope="col">Plan</th>' ) );
		gs_assert_true( false !== strpos( $output, '<figcaption class="wp-element-caption">Plan comparison</figcaption>' ) );
	}
);

gs_test(
	'Post Table style affects every Table without adding Block attributes',
	static function (): void {
		$GLOBALS['gs_test_options']         = [];
		$GLOBALS['gs_test_post_meta']       = [];
		$GLOBALS['gs_test_current_post_id'] = 44;
		$GLOBALS['gs_test_caps']['edit_post'] = true;
		$engine = gs_table_engine();
		$engine->post_store()->save_style(
			44,
			TableAdapter::BLOCK_NAME,
			new ScopeStyle( null, [ 'headerBackgroundColor' => '#800080' ] )
		);
		$renderer   = new TableRenderer( $engine, new TableAdapter() );
		$attributes = [];
		$table_a    = $renderer->render( gs_native_table_markup(), [ 'attrs' => $attributes ] );
		$table_b    = $renderer->render( gs_native_table_markup(), [ 'attrs' => $attributes ] );

		gs_assert_true( false !== strpos( $table_a, '--gutenstyle-table-header-background:#800080;' ) );
		gs_assert_true( false !== strpos( $table_b, '--gutenstyle-table-header-background:#800080;' ) );
		gs_assert_same( [], $attributes );
	}
);

gs_test(
	'Table renderer yields to explicit native Gutenberg color border typography and stripe settings',
	static function (): void {
		$GLOBALS['gs_test_options']         = [];
		$GLOBALS['gs_test_post_meta']       = [];
		$GLOBALS['gs_test_current_post_id'] = 3;
		$GLOBALS['gs_test_caps']['manage_options'] = true;
		$engine = gs_table_engine();
		$engine->global_store()->save_style( TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-striped' ) );

		$output = ( new TableRenderer( $engine, new TableAdapter() ) )->render(
			gs_native_table_markup(),
			[
				'attrs' => [
					'className' => 'is-style-stripes',
					'textColor' => 'contrast',
					'style'     => [
						'border'     => [ 'color' => '#abcdef', 'style' => 'dashed', 'width' => '3px' ],
						'typography' => [ 'fontWeight' => '400' ],
					],
				],
			]
		);
		gs_assert_same( false, false !== strpos( $output, 'border-color:#e2e8f0;' ) );
		gs_assert_same( false, false !== strpos( $output, 'gutenstyle-table--striped' ) );
		gs_assert_same( false, false !== strpos( $output, '--gutenstyle-table-header-color:#ffffff;' ) );
		gs_assert_same( false, false !== strpos( $output, '--gutenstyle-table-header-font-weight:600;' ) );
	}
);

gs_test(
	'Horizontal scroll and row states use scoped marker classes without JavaScript',
	static function (): void {
		$GLOBALS['gs_test_options']         = [];
		$GLOBALS['gs_test_post_meta']       = [];
		$GLOBALS['gs_test_current_post_id'] = 8;
		$GLOBALS['gs_test_caps']['manage_options'] = true;
		$engine = gs_table_engine();
		$engine->global_store()->save_style( TableAdapter::BLOCK_NAME, new ScopeStyle( 'table-comparison' ) );
		$output = ( new TableRenderer( $engine, new TableAdapter() ) )->render( gs_native_table_markup(), [ 'attrs' => [] ] );
		gs_assert_true( false !== strpos( $output, 'gutenstyle-table--horizontal-scroll' ) );
		gs_assert_true( false !== strpos( $output, 'gutenstyle-table--hover' ) );
	}
);
