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
        $ajax_pagination  = $this->flag( $options['ajax_pagination'] ?? true );
        $preset           = $this->preset( (string) ( $options['preset'] ?? 'grid' ) );
        $columns          = $this->columns( $options['columns'] ?? array() );
        $instance_id      = sanitize_html_class( (string) ( $options['instance_id'] ?? '' ) );

        $summary = $show_summary ? $this->summary( $result ) : '';
        $form    = $show_search_form ? $this->searchForm( $result->searchTerm ) : '';
        $body    = $result->posts
            ? $this->results( $result, $rules, $preset, $columns )
            : $this->emptyState( $result, $options );

        return sprintf(
            '<div class="cc-d5sr__inner cc-d5sr--preset-%1$s" data-cc-d5sr-instance="%2$s" data-cc-d5sr-ajax="%3$s">%4$s%5$s%6$s</div>',
            esc_attr( $preset ),
            esc_attr( $instance_id ),
            $ajax_pagination && '' !== $instance_id ? 'on' : 'off',
            $summary,
            $form,
            $body
        );
    }

    private function summary( QueryResult $result ): string {
        $count = sprintf(
            _n( '%d risultato', '%d risultati', $result->foundPosts, 'cc-divi5-search-results' ),
            $result->foundPosts
        );
        $term = '' !== $result->searchTerm
            ? sprintf( __( ' per “%s”', 'cc-divi5-search-results' ), $result->searchTerm )
            : '';

        return sprintf(
            '<div class="cc-d5sr__summary" aria-live="polite"><strong>%1$s</strong><span>%2$s</span></div>',
            esc_html( $count ),
            esc_html( $term )
        );
    }

    private function searchForm( string $search_term ): string {
        $input_id = wp_unique_id( 'cc-d5sr-search-' );

        return sprintf(
            '<form class="cc-d5sr__search" role="search" method="get" action="%1$s"><label class="screen-reader-text" for="%2$s">%3$s</label><input id="%2$s" type="search" name="s" value="%4$s" placeholder="%5$s" autocomplete="off"><button type="submit">%6$s</button></form>',
            esc_url( home_url( '/' ) ),
            esc_attr( $input_id ),
            esc_html__( 'Cerca nel sito:', 'cc-divi5-search-results' ),
            esc_attr( $search_term ),
            esc_attr__( 'Cerca nel sito', 'cc-divi5-search-results' ),
            esc_html__( 'Cerca', 'cc-divi5-search-results' )
        );
    }

    /**
     * @param array{desktop:int,tablet:int,phone:int} $columns
     */
    private function results( QueryResult $result, RuleCollection $rules, string $preset, array $columns ): string {
        $cards = '';

        foreach ( $result->posts as $post ) {
            $cards .= $this->card( $post, $rules->forPost( $post ) );
        }

        return sprintf(
            '<div class="cc-d5sr__results" tabindex="-1" style="--cc-d5sr-columns-desktop:%1$d;--cc-d5sr-columns-tablet:%2$d;--cc-d5sr-columns-phone:%3$d" data-preset="%4$s">%5$s</div>%6$s',
            $columns['desktop'],
            $columns['tablet'],
            $columns['phone'],
            esc_attr( $preset ),
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

        $card_classes = array( 'cc-d5sr__card' );
        if ( '' === $image ) {
            $card_classes[] = 'cc-d5sr__card--no-image';
        }

        return sprintf(
            '<article class="%1$s" data-post-type="%2$s" style="--cc-d5sr-accent:%3$s">%4$s<div class="cc-d5sr__content"><div class="cc-d5sr__meta"><span class="cc-d5sr__badge">%5$s</span>%6$s</div><h2 class="cc-d5sr__title"><a href="%7$s">%8$s</a></h2>%9$s<a class="cc-d5sr__cta" href="%7$s">%10$s<span aria-hidden="true"> →</span></a></div></article>',
            esc_attr( implode( ' ', $card_classes ) ),
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
        $title = sanitize_text_field( (string) ( $options['empty_title'] ?? __( 'Nessun risultato trovato', 'cc-divi5-search-results' ) ) );
        $body  = sanitize_text_field( (string) ( $options['empty_body'] ?? __( 'Prova con un termine diverso oppure visita un’altra sezione del sito.', 'cc-divi5-search-results' ) ) );

        return sprintf(
            '<section class="cc-d5sr__empty" tabindex="-1"><h2>%1$s</h2><p>%2$s</p>%3$s</section>',
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
                'prev_text' => __( 'Precedente', 'cc-divi5-search-results' ),
                'next_text' => __( 'Successiva', 'cc-divi5-search-results' ),
                'total'     => $result->maxPages,
                'type'      => 'list',
            )
        );

        if ( ! is_string( $links ) || '' === $links ) {
            return '';
        }

        return sprintf(
            '<nav class="cc-d5sr__pagination" aria-label="%1$s">%2$s</nav>',
            esc_attr__( 'Pagine dei risultati di ricerca', 'cc-divi5-search-results' ),
            wp_kses_post( $links )
        );
    }

    /**
     * @param mixed $value
     */
    private function flag( $value ): bool {
        if ( is_bool( $value ) ) {
            return $value;
        }

        return in_array( strtolower( (string) $value ), array( '1', 'on', 'true', 'yes' ), true );
    }

    private function preset( string $preset ): string {
        $preset = sanitize_key( $preset );

        return in_array( $preset, array( 'grid', 'compact-list', 'classic-card' ), true )
            ? $preset
            : 'grid';
    }

    /**
     * @param mixed $columns
     *
     * @return array{desktop:int,tablet:int,phone:int}
     */
    private function columns( $columns ): array {
        $columns = is_array( $columns ) ? $columns : array( 'desktop' => $columns );
        $desktop = max( 1, min( 6, (int) ( $columns['desktop'] ?? 3 ) ) );
        $tablet  = max( 1, min( 4, (int) ( $columns['tablet'] ?? min( 2, $desktop ) ) ) );
        $phone   = max( 1, min( 2, (int) ( $columns['phone'] ?? 1 ) ) );

        return compact( 'desktop', 'tablet', 'phone' );
    }
}
