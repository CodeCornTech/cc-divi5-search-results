<?php

namespace CodeCorn\Divi5SearchResults\Query;

use WP_Query;

final class SearchQueryResolver {
    /**
     * @param array<string, mixed> $options
     */
    public function resolve( array $options ): QueryResult {
        $source          = sanitize_key( (string) ( $options['source'] ?? 'current' ) );
        $posts_per_page  = max( 1, min( 100, (int) ( $options['posts_per_page'] ?? 10 ) ) );
        $current_page    = max( 1, (int) ( $options['current_page'] ?? $this->currentPage() ) );
        $post_types      = $this->sanitizePostTypes( $options['post_types'] ?? array() );
        $requested_term  = sanitize_text_field( (string) ( $options['search_term'] ?? '' ) );

        if ( 'current' === $source ) {
            $current = $this->currentSearchQuery();

            if ( $current instanceof WP_Query ) {
                $search_term = sanitize_text_field(
                    (string) $current->get( 's', get_search_query( false ) )
                );
                $query_args  = $this->currentQueryArgs(
                    $current,
                    $search_term,
                    $post_types,
                    $posts_per_page,
                    $current_page
                );

                /**
                 * Filter the isolated query derived from the current WordPress search request.
                 *
                 * The main global query is never mutated.
                 *
                 * @param array<string, mixed> $query_args
                 * @param array<string, mixed> $options
                 * @param WP_Query             $current
                 */
                $query_args = apply_filters( 'cc_d5sr_current_query_args', $query_args, $options, $current );
                $query      = new WP_Query( $query_args );

                return $this->fromQuery( $query, $search_term );
            }
        }

        $search_term = '' !== $requested_term
            ? $requested_term
            : sanitize_text_field( get_search_query( false ) );
        $query_args  = array(
            'ignore_sticky_posts' => true,
            'no_found_rows'       => false,
            'nopaging'            => false,
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

    /**
     * Build a private query from the resolved search request while allowing the module
     * to own result types, page size and pagination.
     *
     * @param list<string> $post_types
     *
     * @return array<string, mixed>
     */
    private function currentQueryArgs(
        WP_Query $current,
        string $search_term,
        array $post_types,
        int $posts_per_page,
        int $current_page
    ): array {
        $query_args = is_array( $current->query_vars ) ? $current->query_vars : array();

        unset(
            $query_args['offset'],
            $query_args['posts_per_archive_page'],
            $query_args['fields']
        );

        $query_args['ignore_sticky_posts'] = true;
        $query_args['no_found_rows']       = false;
        $query_args['nopaging']            = false;
        $query_args['paged']               = $current_page;
        $query_args['page']                = 0;
        $query_args['post_status']         = 'publish';
        $query_args['post_type']           = $post_types ?: 'any';
        $query_args['posts_per_page']      = $posts_per_page;
        $query_args['s']                   = $search_term;

        return $query_args;
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
