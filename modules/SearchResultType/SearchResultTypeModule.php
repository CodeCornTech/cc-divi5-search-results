<?php

namespace CodeCorn\Divi5SearchResults\Modules\SearchResultType;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

final class SearchResultTypeModule implements DependencyInterface {
    public function load(): void {
        $module_json_folder_path = CC_D5SR_DIR . 'modules-json/search-result-type/';

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
     * Configuration-only child modules are consumed by the parent module and do not render frontend markup.
     */
    public static function renderCallback(): string {
        return '';
    }
}
