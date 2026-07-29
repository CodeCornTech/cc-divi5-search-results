<?php

namespace CodeCorn\Divi5SearchResults\Modules\SearchResults;

use CodeCorn\Divi5SearchResults\Query\SearchQueryResolver;
use CodeCorn\Divi5SearchResults\Rendering\ResultTypeRule;
use CodeCorn\Divi5SearchResults\Rendering\RuleCollection;
use CodeCorn\Divi5SearchResults\Rendering\SearchResultsRenderer;
use CodeCorn\Divi5SearchResults\Support\AttributeValue;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;

final class SearchResultsModule implements DependencyInterface {
    public function load(): void {
        $module_json_folder_path = CC_D5SR_DIR . 'modules-json/search-results/';

        add_action(
            'init',
            static function () use ( $module_json_folder_path ): void {
                if ( ! is_readable( $module_json_folder_path . 'module.json' ) ) {
                    return;
                }

                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    array(
                        'render_callback' => array( self::class, 'renderCallback' ),
                    )
                );
            }
        );
    }

    /**
     * @param array<string, mixed> $attrs
     * @param array<string, mixed> $default_printed_style_attrs
     */
    public static function renderCallback(
        array $attrs,
        string $child_modules_content,
        WP_Block $block,
        ModuleElements $elements,
        array $default_printed_style_attrs
    ): string {
        unset( $child_modules_content );

        $children = self::childrenFromBlock( $block );
        $options  = self::optionsFromAttrs( $attrs );
        $rules    = self::rulesFromChildren( $children );
        $result   = ( new SearchQueryResolver() )->resolve(
            array(
                'post_types'     => $rules->postTypes(),
                'posts_per_page' => $options['posts_per_page'],
                'search_term'    => $options['search_term'],
                'source'         => $options['source'],
            )
        );
        $content = ( new SearchResultsRenderer() )->render( $result, $rules, $options );

        return Module::render(
            array(
                'attrs'                    => $attrs,
                'children'                 => array(
                    $elements->style_components(
                        array(
                            'attrName' => 'module',
                        )
                    ),
                    $content,
                ),
                'childrenIds'              => array_values(
                    array_filter(
                        array_map(
                            static fn ( $child ): string => is_string( $child->id ?? null ) ? $child->id : '',
                            $children
                        )
                    )
                ),
                'classnamesFunction'       => array( self::class, 'moduleClassnames' ),
                'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
                'elements'                 => $elements,
                'id'                       => $block->parsed_block['id'],
                'moduleCategory'           => $block->block_type->category,
                'name'                     => $block->block_type->name,
                'orderIndex'               => $block->parsed_block['orderIndex'],
                'storeInstance'            => $block->parsed_block['storeInstance'],
                'stylesComponent'          => array( self::class, 'moduleStyles' ),
            )
        );
    }

    /**
     * @param array<string, mixed> $args
     */
    public static function moduleClassnames( array $args ): void {
        $args['classnamesInstance']->add( 'cc-d5sr' );
    }

    /**
     * @param array<string, mixed> $args
     */
    public static function moduleStyles( array $args ): void {
        $default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? array();

        Style::add(
            array(
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => array(
                    $args['elements']->style(
                        array(
                            'attrName'   => 'module',
                            'styleProps' => array(
                                'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? array(),
                                'disabledOn'               => array(
                                    'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
                                ),
                            ),
                        )
                    ),
                ),
            )
        );
    }

    /**
     * @param array<string, mixed> $attrs
     *
     * @return array<string, mixed>
     */
    private static function optionsFromAttrs( array $attrs ): array {
        return array(
            'columns'          => (int) AttributeValue::desktopField( $attrs, array( 'display', 'innerContent' ), 'columns', '3' ),
            'empty_body'       => AttributeValue::desktopField( $attrs, array( 'emptyState', 'innerContent' ), 'body', __( 'Try a different search term or browse another section of the site.', 'cc-divi5-search-results' ) ),
            'empty_title'      => AttributeValue::desktopField( $attrs, array( 'emptyState', 'innerContent' ), 'title', __( 'No results found', 'cc-divi5-search-results' ) ),
            'posts_per_page'   => (int) AttributeValue::desktopField( $attrs, array( 'query', 'innerContent' ), 'postsPerPage', '10' ),
            'search_term'      => AttributeValue::desktopField( $attrs, array( 'query', 'innerContent' ), 'searchTerm', '' ),
            'show_search_form' => AttributeValue::desktopField( $attrs, array( 'display', 'innerContent' ), 'showSearchForm', 'on' ),
            'show_summary'     => AttributeValue::desktopField( $attrs, array( 'display', 'innerContent' ), 'showSummary', 'on' ),
            'source'           => AttributeValue::desktopField( $attrs, array( 'query', 'innerContent' ), 'source', 'current' ),
        );
    }

    /**
     * @return array<int, object>
     */
    private static function childrenFromBlock( WP_Block $block ): array {
        $block_id       = $block->parsed_block['id'] ?? '';
        $store_instance = $block->parsed_block['storeInstance'] ?? null;

        return BlockParserStore::get_children( $block_id, $store_instance );
    }

    /**
     * @param array<int, object> $children
     */
    private static function rulesFromChildren( array $children ): RuleCollection {
        $rules = array();

        foreach ( $children as $child ) {
            if ( 'codecorn/search-result-type' !== ( $child->blockName ?? '' ) ) {
                continue;
            }

            $rules[] = ResultTypeRule::fromDiviAttrs( is_array( $child->attrs ?? null ) ? $child->attrs : array() );
        }

        return new RuleCollection( $rules );
    }
}
