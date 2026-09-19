<?php

namespace CodeCorn\Divi5SearchResults\Api;

use CodeCorn\Divi5SearchResults\Context\ResultContext;
use CodeCorn\Divi5SearchResults\Query\SearchQueryResolver;
use CodeCorn\Divi5SearchResults\Rendering\ResultTypeRule;
use CodeCorn\Divi5SearchResults\Rendering\RuleCollection;

final class SearchEngine {
    /**
     * Public integration facade for query resolution and semantic result classification.
     *
     * @param array<string, mixed>                                      $options
     * @param iterable<ResultTypeRule|array<string, mixed>>             $rules
     */
    public function resolve( array $options = array(), iterable $rules = array() ): SearchResultSet {
        $collection = $this->ruleCollection( $rules );

        $post_types = $collection->postTypes();
        if ( array_key_exists( 'post_types', $options ) && array() === $post_types ) {
            $post_types = $options['post_types'];
        }

        $query_options               = $options;
        $query_options['post_types'] = $post_types;

        $query_result = ( new SearchQueryResolver() )->resolve( $query_options );
        $items        = array();

        foreach ( $query_result->posts as $post ) {
            $rule    = $collection->forPost( $post );
            $context = ResultContext::resolve( $post, $rule );
            $items[] = SearchResultItem::fromPost( $post, $rule, $context );
        }

        return new SearchResultSet(
            $items,
            $query_result->foundPosts,
            $query_result->maxPages,
            $query_result->currentPage,
            $query_result->searchTerm
        );
    }

    /**
     * @param iterable<ResultTypeRule|array<string, mixed>> $rules
     */
    private function ruleCollection( iterable $rules ): RuleCollection {
        $normalized = array();

        foreach ( $rules as $rule ) {
            if ( $rule instanceof ResultTypeRule ) {
                $normalized[] = $rule;
                continue;
            }

            if ( is_array( $rule ) ) {
                $normalized[] = ResultTypeRule::fromFlatArray( $rule );
            }
        }

        return new RuleCollection( $normalized );
    }
}
