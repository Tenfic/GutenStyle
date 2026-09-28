<?php
namespace GutenStyle\Styles;

use GutenStyle\Styles\Compiler\CssVariableCompiler;
use GutenStyle\Styles\Scope\BlockStyleScope;
use GutenStyle\Styles\Scope\GlobalStyleScope;
use GutenStyle\Styles\Scope\PostStyleScope;
use GutenStyle\Styles\Storage\GlobalStyleStore;
use GutenStyle\Styles\Storage\PostStyleStore;
use GutenStyle\Styles\Value\EffectiveStyle;

final class StyleEngine {
	private StyleRegistry $registry;
	private StyleSanitizer $sanitizer;
	private GlobalStyleStore $global_store;
	private PostStyleStore $post_store;
	private StyleResolver $resolver;
	private CssVariableCompiler $compiler;
	private EffectiveStyleService $effective_styles;

	public function __construct(
		StyleRegistry $registry,
		StyleSanitizer $sanitizer,
		GlobalStyleStore $global_store,
		PostStyleStore $post_store
	) {
		$this->registry     = $registry;
		$this->sanitizer    = $sanitizer;
		$this->global_store = $global_store;
		$this->post_store   = $post_store;
		$this->resolver     = new StyleResolver( $registry );
		$this->compiler     = new CssVariableCompiler( $registry );
		$this->effective_styles = new EffectiveStyleService(
			$this->resolver,
			new GlobalStyleScope( $global_store ),
			new PostStyleScope( $post_store ),
			new BlockStyleScope( $sanitizer )
		);
	}

	public function registry(): StyleRegistry {
		return $this->registry;
	}

	public function sanitizer(): StyleSanitizer {
		return $this->sanitizer;
	}

	public function global_store(): GlobalStyleStore {
		return $this->global_store;
	}

	public function post_store(): PostStyleStore {
		return $this->post_store;
	}

	public function resolver(): StyleResolver {
		return $this->resolver;
	}

	public function compiler(): CssVariableCompiler {
		return $this->compiler;
	}

	/**
	 * @param array<string,mixed> $block_attributes
	 */
	public function resolve( string $module, int $post_id = 0, array $block_attributes = [] ): EffectiveStyle {
		return $this->effective_styles->resolve( $module, $post_id, $block_attributes );
	}
}
