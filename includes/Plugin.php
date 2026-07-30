<?php

namespace CodeCorn\Divi5SearchResults;

use CodeCorn\Divi5SearchResults\Modules\SearchResultType\SearchResultTypeModule;
use CodeCorn\Divi5SearchResults\Modules\SearchResults\SearchResultsModule;
use CodeCorn\Divi5SearchResults\Shortcodes\SearchResultsShortcode;

final class Plugin {
    private static bool $booted = false;

    public static function boot(): void {
        if ( self::$booted ) {
            return;
        }

        self::$booted = true;

        add_action( 'plugins_loaded', array( self::class, 'loadTextdomain' ) );
        add_action( 'init', array( SearchResultsShortcode::class, 'register' ) );
        add_action( 'wp_enqueue_scripts', array( self::class, 'enqueueFrontendAssets' ) );
        add_action( 'divi_module_library_modules_dependency_tree', array( self::class, 'registerDiviDependencies' ) );
        add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array( self::class, 'registerVisualBuilderAssets' ) );
    }

    public static function loadTextdomain(): void {
        load_plugin_textdomain(
            'cc-divi5-search-results',
            false,
            dirname( plugin_basename( CC_D5SR_FILE ) ) . '/languages'
        );
    }

    public static function enqueueFrontendAssets(): void {
        wp_enqueue_style(
            'cc-divi5-search-results',
            CC_D5SR_URL . 'assets/css/search-results.css',
            array(),
            self::assetVersion( 'assets/css/search-results.css' )
        );

        wp_enqueue_style(
            'cc-divi5-search-results-theme',
            CC_D5SR_URL . 'assets/css/search-results-theme.css',
            array( 'cc-divi5-search-results' ),
            self::assetVersion( 'assets/css/search-results-theme.css' )
        );

        wp_enqueue_script(
            'cc-divi5-search-results',
            CC_D5SR_URL . 'assets/js/search-results.js',
            array(),
            self::assetVersion( 'assets/js/search-results.js' ),
            true
        );
    }

    /**
     * @param object $dependency_tree Divi dependency tree.
     */
    public static function registerDiviDependencies( object $dependency_tree ): void {
        if ( ! method_exists( $dependency_tree, 'add_dependency' ) ) {
            return;
        }

        if ( ! interface_exists( 'ET\\Builder\\Framework\\DependencyManagement\\Interfaces\\DependencyInterface' ) ) {
            return;
        }

        $dependency_tree->add_dependency( new SearchResultsModule() );
        $dependency_tree->add_dependency( new SearchResultTypeModule() );
    }

    public static function registerVisualBuilderAssets(): void {
        $bundle_path = CC_D5SR_DIR . 'scripts/bundle.js';

        if (
            ! is_readable( $bundle_path )
            || ! class_exists( 'ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager' )
        ) {
            return;
        }

        $builder_data_handle = 'cc-divi5-search-results-vb-data';

        wp_register_script(
            $builder_data_handle,
            false,
            array(),
            CC_D5SR_VERSION,
            true
        );

        wp_add_inline_script(
            $builder_data_handle,
            'window.ccD5srBuilderData = ' . wp_json_encode(
                array(
                    'postTypes' => self::getBuilderPostTypeOptions(),
                )
            ) . ';',
            'before'
        );

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
            array(
                'name'    => 'cc-divi5-search-results-vb',
                'version' => self::assetVersion( 'scripts/bundle.js' ),
                'script'  => array(
                    'src'                => CC_D5SR_URL . 'scripts/bundle.js',
                    'deps'               => array(
                        $builder_data_handle,
                        'divi-module-library',
                        'divi-vendor-wp-hooks',
                    ),
                    'enqueue_top_window' => false,
                    'enqueue_app_window' => true,
                ),
            )
        );

        $style_path = CC_D5SR_DIR . 'styles/vb-bundle.css';

        if ( is_readable( $style_path ) ) {
            \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
                array(
                    'name'    => 'cc-divi5-search-results-vb-style',
                    'version' => self::assetVersion( 'styles/vb-bundle.css' ),
                    'style'   => array(
                        'src'                => CC_D5SR_URL . 'styles/vb-bundle.css',
                        'deps'               => array(),
                        'enqueue_top_window' => false,
                        'enqueue_app_window' => true,
                    ),
                )
            );
        }
    }

    private static function assetVersion( string $relative_path ): string {
        $path = CC_D5SR_DIR . ltrim( $relative_path, '/' );
        $time = is_readable( $path ) ? filemtime( $path ) : false;

        return false !== $time
            ? CC_D5SR_VERSION . '.' . (string) $time
            : CC_D5SR_VERSION;
    }

    /**
     * Return public WordPress post types as Divi select options.
     *
     * @return array<string, array{label:string}>
     */
    private static function getBuilderPostTypeOptions(): array {
        $post_type_objects = get_post_types(
            array(
                'public' => true,
            ),
            'objects'
        );

        unset( $post_type_objects['attachment'] );

        $options = array();

        foreach ( $post_type_objects as $post_type_slug => $post_type_object ) {
            if ( ! $post_type_object instanceof \WP_Post_Type ) {
                continue;
            }

            $label = $post_type_object->labels->name ?: $post_type_object->label;
            $label = $label ?: $post_type_slug;

            $options[ $post_type_slug ] = array(
                'label' => sprintf(
                    /* translators: 1: post type label, 2: post type slug. */
                    __( '%1$s (%2$s)', 'cc-divi5-search-results' ),
                    $label,
                    $post_type_slug
                ),
            );
        }

        uasort(
            $options,
            static function ( array $left, array $right ): int {
                return strnatcasecmp( $left['label'], $right['label'] );
            }
        );

        /**
         * Filter post types exposed by the CC Result Type selector.
         *
         * @param array<string, array{label:string}> $options           Divi select options keyed by post type slug.
         * @param array<string, \WP_Post_Type>      $post_type_objects Public post type objects, excluding attachments.
         */
        $options = apply_filters(
            'cc_d5sr_builder_post_type_options',
            $options,
            $post_type_objects
        );

        return is_array( $options ) ? $options : array();
    }
}
