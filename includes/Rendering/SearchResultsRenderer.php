<?php

namespace CodeCorn\Divi5SearchResults\Rendering;

use CodeCorn\Divi5SearchResults\Query\QueryResult;
use WP_Post;

final class SearchResultsRenderer {
    /**
     * @param array<string, mixed> $options
     */
    public function render( QueryResult $result, RuleCollection $rules, array $options = array() ): string {
        $show_summary     = $this->flag( $options['show_summary'] ?? true );
        $show_search_form = $this->flag( $options['show_search_form'] ?? true );
        $columns          = max( 1, min( 6, (int) ( $options['columns'] ?? 3 ) ) );

        $summary = $show_summary ? $this->summary( $result ) : '';
        $form    = $show_search_form ? $this->searchForm( $result->searchTerm ) : '';
        $body    = $result->posts
            ? $this->resultsGrid( $result, $rules, $columns )
            : $this->emptyState( $result, $options );

        return sprintf(
            '<div class="cc-d5sr__inner">%s%s%s</div>',
            $summary,
            $form,
            $body
        );
    }

    private function summary( QueryResult $result ): string {
        $term = '' !== $result->searchTerm
            ? esc_html( sprintf( __( ' for “%s”', 'cc-divi5-search-results' ), $result->searchTerm ) )
            : '';

        return sprintf(
            '<div class="cc-d5sr__summary" aria-live="polite"><strong>%s</strong><span>%s</span></div>',
            esc_html( sprintf( _n( '%d result', '%d results', $result->foundPosts, 'cc-divi5-search-results' ), $result->foundPosts ) ),
            $term
        );
    }

    private function searchForm( string $search_term ): string {
        $input_id = wp_unique_id( 'cc-d5sr-search-' );

        return sprintf(
            '<form class="cc-d5sr__search" role="search" method="get" action="%1$s"><label class="screen-reader-text" for="%2$s">%3$s</label><input id="%2$s" type="search" name="s" value="%4$s" placeholder="%5$s"><button type="submit">%6$s</button></form>',
            esc_url( home_url( '/' ) ),
            esc_attr( $input_id ),
            esc_html__( 'Search for:', 'cc-divi5-search-results' ),
            esc_attr( $search_term ),
            esc_attr__( 'Search the site', 'cc-divi5-search-results' ),
            esc_html__( 'Search', 'cc-divi5-search-results' )
        );
    }

    private function resultsGrid( QueryResult $result, RuleCollection $rules, int $columns ): string {
        $cards = '';

        foreach ( $result->posts as $post ) {
            $cards .= $this->card( $post, $rules->forPost( $post ) );
        }

        return sprintf(
            '<div class="cc-d5sr__grid" style="--cc-d5sr-columns:%d">%s</div>%s',
            $columns,
            $cards,
            $this->pagination( $result )
        );
    }

    private function card( WP_Post $post, ResultTypeRule $rule ): string {
        $permalink = get_permalink( $post );
        $title     = get_the_title( $post );
        $image     = '';

        if ( $rule->showImage && has_post_thumbnail( $post ) ) {
            $image = sprintf(
                '<a class="cc-d5sr__image" href="%1$s" tabindex="-1" aria-hidden="true">%2$s</a>',
                esc_url( $permalink ),
                get_the_post_thumbnail(
                    $post,
                    'large',
                    array(
                        'loading' => 'lazy',
                        'alt'     => '',
                    )
                )
            );
        }

        $date = $rule->showDate
            ? sprintf( '<time datetime="%1$s">%2$s</time>', esc_attr( get_the_date( DATE_W3C, $post ) ), esc_html( get_the_date( '', $post ) ) )
            : '';

        $excerpt = '';
        if ( $rule->showExcerpt && 0 < $rule->excerptLength ) {
            $excerpt_text = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), $rule->excerptLength, '…' );
            $excerpt      = '' !== $excerpt_text ? sprintf( '<p class="cc-d5sr__excerpt">%s</p>', esc_html( $excerpt_text ) ) : '';
        }

        return sprintf(
            '<article class="cc-d5sr__card" data-post-type="%1$s" style="--cc-d5sr-accent:%2$s">%3$s<div class="cc-d5sr__content"><div class="cc-d5sr__meta"><span class="cc-d5sr__badge">%4$s</span>%5$s</div><h2 class="cc-d5sr__title"><a href="%6$s">%7$s</a></h2>%8$s<a class="cc-d5sr__cta" href="%6$s">%9$s<span aria-hidden="true"> →</span></a></div></article>',
            esc_attr( $rule->postType ),
            esc_attr( $rule->accentColor ),
            $image,
            esc_html( $rule->badgeLabel ),
            $date,
            esc_url( $permalink ),
            esc_html( $title ),
            $excerpt,
            esc_html( $rule->ctaLabel )
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function emptyState( QueryResult $result, array $options ): string {
        $title = sanitize_text_field( (string) ( $options['empty_title'] ?? __( 'No results found', 'cc-divi5-search-results' ) ) );
        $body  = sanitize_text_field( (string) ( $options['empty_body'] ?? __( 'Try a different search term or browse another section of the site.', 'cc-divi5-search-results' ) ) );

        return sprintf(
            '<section class="cc-d5sr__empty"><h2>%1$s</h2><p>%2$s</p>%3$s</section>',
            esc_html( $title ),
            esc_html( $body ),
            '' !== $result->searchTerm
                ? sprintf( '<p class="cc-d5sr__empty-query">%s</p>', esc_html( $result->searchTerm ) )
                : ''
        );
    }

    private function pagination( QueryResult $result ): string {
        if ( $result->maxPages < 2 ) {
            return '';
        }

        $links = paginate_links(
            array(
                'current'   => $result->currentPage,
                'mid_size'  => 2,
                'prev_text' => __( 'Previous', 'cc-divi5-search-results' ),
                'next_text' => __( 'Next', 'cc-divi5-search-results' ),
                'total'     => $result->maxPages,
                'type'      => 'list',
            )
        );

        if ( ! is_string( $links ) || '' === $links ) {
            return '';
        }

        return sprintf(
            '<nav class="cc-d5sr__pagination" aria-label="%1$s">%2$s</nav>',
            esc_attr__( 'Search results pages', 'cc-divi5-search-results' ),
            wp_kses_post( $links )
        );
    }

    private function flag( $value ): bool {
        if ( is_bool( $value ) ) {
            return $value;
        }

        return in_array( strtolower( (string) $value ), array( '1', 'on', 'true', 'yes' ), true );
    }
}
