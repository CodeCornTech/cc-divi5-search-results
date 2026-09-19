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

        $resolver     = new SearchQueryResolver();
        $query_result = $resolver->resolve( $query_options );
        $items        = array();

        foreach ( $query_result->posts as $post ) {
            $rule    = $collection->forPost( $post );
            $context = ResultContext::resolve( $post, $rule );
            $items[] = SearchResultItem::fromPost( $post, $rule, $context );
        }

        $facets = ! empty( $options['include_facets'] )
            ? $this->facets( $resolver, $collection, $options, $post_types )
            : array();

        return new SearchResultSet(
            $items,
            $query_result->foundPosts,
            $query_result->maxPages,
            $query_result->currentPage,
            $query_result->searchTerm,
            $facets
        );
    }

    /**
     * @param list<string> $post_types
     *
     * @return list<SearchFacet>
     */
    private function facets(
        SearchQueryResolver $resolver,
        RuleCollection $collection,
        array $options,
        array $post_types
    ): array {
        $facets = array();

        foreach ( $post_types as $post_type ) {
            $facet_options                   = $options;
            $facet_options['post_types']     = array( $post_type );
            $facet_options['posts_per_page'] = 1;
            $facet_options['current_page']   = 1;
            $facet_options['include_facets'] = false;

            $result = $resolver->resolve( $facet_options );
            $rule   = $collection->forPostType( $post_type );
            $post   = $result->posts[0] ?? null;

            if ( $post instanceof \WP_Post ) {
                $context = ResultContext::resolve( $post, $rule );
            } else {
                $context = new ResultContext(
                    sanitize_key( $post_type ) ?: 'result',
                    $rule->singularLabel,
                    $rule->badgeLabel,
                    $rule->accentColor
                );
            }

            $facets[] = new SearchFacet(
                $post_type,
                $context->key,
                $context->label,
                $context->badgeLabel,
                $context->accentColor,
                max( 0, $result->foundPosts )
            );
        }

        return $facets;
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
