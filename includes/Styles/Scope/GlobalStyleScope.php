<?php
namespace GutenStyle\Styles\Scope;

use GutenStyle\Styles\Contracts\StyleScopeInterface;
use GutenStyle\Styles\Storage\GlobalStyleStore;
use GutenStyle\Styles\Value\ScopeStyle;

final class GlobalStyleScope implements StyleScopeInterface {
	private GlobalStyleStore $store;

	public function __construct( GlobalStyleStore $store ) {
		$this->store = $store;
	}

	public function get_name(): string {
		return 'global';
	}

	public function get_style( string $module, array $context = [] ): ScopeStyle {
		unset( $context );
		return $this->store->get_style( $module );
	}
}
