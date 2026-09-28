<?php
namespace GutenStyle\Styles\Scope;

use GutenStyle\Styles\Contracts\StyleScopeInterface;
use GutenStyle\Styles\Storage\PostStyleStore;
use GutenStyle\Styles\Value\ScopeStyle;

final class PostStyleScope implements StyleScopeInterface {
	private PostStyleStore $store;

	public function __construct( PostStyleStore $store ) {
		$this->store = $store;
	}

	public function get_name(): string {
		return 'post';
	}

	public function get_style( string $module, array $context = [] ): ScopeStyle {
		$post_id = isset( $context['post_id'] ) ? (int) $context['post_id'] : 0;
		return $post_id > 0 ? $this->store->get_style( $post_id, $module ) : ScopeStyle::empty();
	}
}
