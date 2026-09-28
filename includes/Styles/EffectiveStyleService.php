<?php
namespace GutenStyle\Styles;

use GutenStyle\Styles\Contracts\StyleScopeInterface;
use GutenStyle\Styles\Value\EffectiveStyle;
use InvalidArgumentException;

final class EffectiveStyleService {
	private StyleResolver $resolver;
	private StyleScopeInterface $global_scope;
	private StyleScopeInterface $post_scope;
	private StyleScopeInterface $block_scope;

	public function __construct(
		StyleResolver $resolver,
		StyleScopeInterface $global_scope,
		StyleScopeInterface $post_scope,
		StyleScopeInterface $block_scope
	) {
		$this->resolver     = $resolver;
		$this->global_scope = $global_scope;
		$this->post_scope   = $post_scope;
		$this->block_scope  = $block_scope;
	}

	/**
	 * @param array<string,mixed> $block_attributes
	 */
	public function resolve( string $module, int $post_id = 0, array $block_attributes = [] ): EffectiveStyle {
		$this->assert_scope( $this->global_scope, 'global' );
		$this->assert_scope( $this->post_scope, 'post' );
		$this->assert_scope( $this->block_scope, 'block' );

		$context = [
			'post_id'          => $post_id,
			'block_attributes' => $block_attributes,
		];

		return $this->resolver->resolve(
			$module,
			$this->global_scope->get_style( $module, $context ),
			$this->post_scope->get_style( $module, $context ),
			$this->block_scope->get_style( $module, $context )
		);
	}

	private function assert_scope( StyleScopeInterface $scope, string $expected ): void {
		if ( $expected !== $scope->get_name() ) {
			throw new InvalidArgumentException( 'Effective style scopes must use the documented precedence order.' );
		}
	}
}
