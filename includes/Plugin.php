<?php

namespace CodeCorn\Divi5SearchResults;

final class Plugin {
    private static bool $booted = false;

    public static function boot(): void {
        if ( self::$booted ) {
            return;
        }

        self::$booted = true;
        add_action( 'plugins_loaded', array( self::class, 'loadTextdomain' ) );
    }

    public static function loadTextdomain(): void {
        load_plugin_textdomain(
            'cc-divi5-search-results',
            false,
            dirname( plugin_basename( CC_D5SR_FILE ) ) . '/languages'
        );
    }
}
