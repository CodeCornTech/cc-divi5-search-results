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
            CC_D5SR_VERSION
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

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
            array(
                'name'    => 'cc-divi5-search-results-vb',
                'version' => CC_D5SR_VERSION,
                'script'  => array(
                    'src'                => CC_D5SR_URL . 'scripts/bundle.js',
                    'deps'               => array(
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
                    'version' => CC_D5SR_VERSION,
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
}
