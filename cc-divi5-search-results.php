<?php
/**
 * Plugin Name:       CC Divi 5 Search Results
 * Plugin URI:        https://github.com/CodeCornTech/cc-divi5-search-results
 * Description:       Native, type-aware search results for Divi 5.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            CodeCorn Technology S.R.L.S.
 * Author URI:        https://codecorn.it
 * License:           MIT
 * Text Domain:       cc-divi5-search-results
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'CC_D5SR_VERSION', '0.1.0' );
define( 'CC_D5SR_FILE', __FILE__ );
define( 'CC_D5SR_DIR', __DIR__ );

$cc_d5sr_autoload = CC_D5SR_DIR . '/vendor/autoload.php';

if ( is_readable( $cc_d5sr_autoload ) ) {
    require_once $cc_d5sr_autoload;
}

if ( class_exists( \CodeCorn\Divi5SearchResults\Plugin::class ) ) {
    \CodeCorn\Divi5SearchResults\Plugin::boot();
}
