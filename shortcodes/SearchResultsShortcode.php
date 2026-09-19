<?php

namespace CodeCorn\Divi5SearchResults\Shortcodes;

use CodeCorn\Divi5SearchResults\Query\SearchQueryResolver;
use CodeCorn\Divi5SearchResults\Rendering\ResultTypeRule;
use CodeCorn\Divi5SearchResults\Rendering\RuleCollection;
use CodeCorn\Divi5SearchResults\Rendering\SearchResultsRenderer;

final class SearchResultsShortcode {
    private static int $instance = 0;

    public static function register(): void {
        add_shortcode( 'cc_divi5_search_results', array( self::class, 'render' ) );
    }

    /**
     * @param array<string, mixed>|string $attributes
     */
    public static function render( $attributes = array() ): string {
        $attributes = shortcode_atts(
            array(
                'ajax_pagination' => 'on',
                'columns'          => '3',
                'columns_phone'    => '1',
                'columns_tablet'   => '2',
                'cta_label'        => __( 'Vedi risultato', 'cc-divi5-search-results' ),
                'excerpt_length'   => '28',
                'post_types'       => '',
                'posts_per_page'   => '10',
                'preset'           => 'grid',
                'search'           => '',
                'show_date'        => 'on',
                'show_excerpt'     => 'on',
                'show_image'       => 'on',
                'show_search_form' => 'on',
                'show_summary'     => 'on',
                'source'           => 'current',
            ),
            is_array( $attributes ) ? $attributes : array(),
            'cc_divi5_search_results'
        );

        $post_types = array_values(
            array_filter(
                array_map( 'sanitize_key', preg_split( '/[\s,]+/', (string) $attributes['post_types'] ) ?: array() )
            )
        );

        $rules = array_map(
            static fn ( string $post_type ): ResultTypeRule => ResultTypeRule::fromFlatArray(
                array(
                    'post_type'      => $post_type,
                    'cta_label'      => $attributes['cta_label'],
                    'excerpt_length' => $attributes['excerpt_length'],
                    'show_date'      => $attributes['show_date'],
                    'show_excerpt'   => $attributes['show_excerpt'],
                    'show_image'     => $attributes['show_image'],
                )
            ),
            $post_types
        );

        $result = ( new SearchQueryResolver() )->resolve(
            array(
                'post_types'     => $post_types,
                'posts_per_page' => (int) $attributes['posts_per_page'],
                'search_term'    => (string) $attributes['search'],
                'source'         => (string) $attributes['source'],
            )
        );

        ++self::$instance;

        return ( new SearchResultsRenderer() )->render(
            $result,
            new RuleCollection( $rules ),
            array(
                'ajax_pagination'  => $attributes['ajax_pagination'],
                'columns'          => array(
                    'desktop' => (int) $attributes['columns'],
                    'tablet'  => (int) $attributes['columns_tablet'],
                    'phone'   => (int) $attributes['columns_phone'],
                ),
                'instance_id'      => 'shortcode-' . self::$instance,
                'preset'           => $attributes['preset'],
                'show_search_form' => $attributes['show_search_form'],
                'show_summary'     => $attributes['show_summary'],
            )
        );
    }
}
