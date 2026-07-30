<?php

namespace CodeCorn\Divi5SearchResults\Rendering;

use WP_Post;

final class RuleCollection {
    /** @var array<string, ResultTypeRule> */
    private array $rules = array();

    /**
     * @param iterable<ResultTypeRule> $rules
     */
    public function __construct( iterable $rules = array() ) {
        foreach ( $rules as $rule ) {
            $this->rules[ $rule->postType ] = $rule;
        }

        uasort(
            $this->rules,
            static fn ( ResultTypeRule $left, ResultTypeRule $right ): int => $left->priority <=> $right->priority
        );
    }

    public function forPost( WP_Post $post ): ResultTypeRule {
        return $this->rules[ $post->post_type ] ?? ResultTypeRule::fallback( $post->post_type );
    }

    /**
     * @return list<string>
     */
    public function postTypes(): array {
        return array_keys( $this->rules );
    }
}
