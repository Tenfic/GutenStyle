<?php
declare(strict_types=1);

use GutenStyle\Styles\Storage\PostStyleStore;
use GutenStyle\Table\TableAdapter;

gs_test(
	'plugin translates on init and registers services before Core blocks',
	static function (): void {
		gs_assert_same( [], $GLOBALS['gs_test_translations'] );
		gs_assert_same( null, gutenstyle_style_engine() );
		$order = [];
		foreach ( [ 'register_style_properties', 'register_block_adapters', 'register_style_presets', 'register_styles', 'loaded' ] as $name ) {
			add_action( 'gutenstyle/' . $name, static function () use ( &$order, $name ): void {
				$order[] = $name;
			} );
		}
		do_action( 'plugins_loaded' );
		do_action( 'after_setup_theme' );
		gs_assert_same( [], $GLOBALS['gs_test_translations'], 'Translation ran before init.' );
		gs_assert_same( null, gutenstyle_style_engine() );

		$GLOBALS['gs_test_translation_values']['gutenstyle'] = [ 'Border style' => 'Translated border style', 'Clean' => 'Translated clean' ];
		$ready_before_core = false;
		add_action( 'init', static function () use ( &$ready_before_core ): void {
			gs_assert_true( null !== gutenstyle_style_registry()->get_block_adapter( TableAdapter::BLOCK_NAME ) );
			gs_assert_true( isset( $GLOBALS['gs_test_actions']['register_block_type_args'] ) );
			$ready_before_core = true;
		}, 5 );
		do_action( 'init' );
		gs_assert_true( $ready_before_core );
		gs_assert_same( [ 'register_style_properties', 'register_block_adapters', 'register_style_presets', 'register_styles', 'loaded' ], $order );
		gs_assert_same( 26, count( gutenstyle_style_registry()->all_properties() ) );
		gs_assert_same( 6, count( gutenstyle_style_registry()->all_presets() ) );
		foreach ( $GLOBALS['gs_test_translations'] as $call ) {
			gs_assert_same( 1, $call['init'], 'Early translation: ' . $call['text'] );
		}
		gs_assert_same( 'Translated border style', gutenstyle_style_registry()->get_property( 'tableBorderStyle' )->to_array()['label'] );
		gs_assert_same( 'Translated clean', gutenstyle_style_registry()->get_preset( 'table-clean' )->get_label() );
		foreach ( [ 'post', 'page', 'book' ] as $post_type ) {
			gs_assert_true( isset( $GLOBALS['gs_test_registered_meta'][ $post_type ][ PostStyleStore::META_KEY ] ) );
		}
		do_action( 'rest_api_init' );
		gs_assert_true( isset( $GLOBALS['gs_test_registered_routes']['gutenstyle/v1/schema'] ) );
		$GLOBALS['gs_test_translation_values'] = [];
	}
);
