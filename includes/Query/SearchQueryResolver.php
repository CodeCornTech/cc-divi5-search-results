<?php

namespace CodeCorn\Divi5SearchResults\Query;

use WP_Query;

final class SearchQueryResolver {
    /**
     * @param array<string, mixed> $options
     */
    public function resolve( array $options ): QueryResult {
        $source = sanitize_key( (string) ( $options['source'] ?? 'current' ) );

        if ( 'current' === $source ) {
            $current = $this->currentSearchQuery();

            if ( $current instanceof WP_Query ) {
                return $this->fromQuery( $current, get_search_query( false ) );
            }
        }

        $search_term    = sanitize_text_field( (string) ( $options['search_term'] ?? get_search_query( false ) ) );
        $posts_per_page = max( 1, min( 100, (int) ( $options['posts_per_page'] ?? 10 ) ) );
        $current_page   = max( 1, (int) ( $options['current_page'] ?? $this->currentPage() ) );
        $post_types     = $this->sanitizePostTypes( $options['post_types'] ?? array() );

        $query_args = array(
            'ignore_sticky_posts' => true,
            'no_found_rows'       => false,
            'paged'               => $current_page,
            'post_status'         => 'publish',
            'post_type'           => $post_types ?: 'any',
            'posts_per_page'      => $posts_per_page,
            's'                   => $search_term,
        );

        /**
         * Filter the isolated query used outside the current WordPress search request.
         *
         * @param array<string, mixed> $query_args
         * @param array<string, mixed> $options
         */
        $query_args = apply_filters( 'cc_d5sr_query_args', $query_args, $options );
        $query      = new WP_Query( $query_args );

        return $this->fromQuery( $query, $search_term );
    }

    private function currentSearchQuery(): ?WP_Query {
        global $wp_query;

        if ( ! is_search() || ! $wp_query instanceof WP_Query ) {
            return null;
        }

        return $wp_query;
    }

    private function currentPage(): int {
        return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
    }

    /**
     * @param mixed $post_types
     *
     * @return list<string>
     */
    private function sanitizePostTypes( $post_types ): array {
        if ( is_string( $post_types ) ) {
            $post_types = preg_split( '/[\s,]+/', $post_types ) ?: array();
        }

        if ( ! is_array( $post_types ) ) {
            return array();
        }

        $post_types = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', $post_types ),
                    static fn ( string $post_type ): bool => '' !== $post_type && post_type_exists( $post_type )
                )
            )
        );

        return $post_types;
    }

    private function fromQuery( WP_Query $query, string $search_term ): QueryResult {
        $posts = array_values(
            array_filter(
                $query->posts,
                static fn ( $post ): bool => $post instanceof \WP_Post
            )
        );

        return new QueryResult(
            $posts,
            (int) $query->found_posts,
            max( 1, (int) $query->max_num_pages ),
            max( 1, (int) $query->get( 'paged', 1 ) ),
            $search_term
        );
    }
}
