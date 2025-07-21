<?php
/**
 * Plugin Name: AI Alt Text Generator
 * Plugin URI: https://github.com/altgen/ai-alt-text-generator
 * Description: 自動で画像の代替テキスト(alt)を AI を用いて生成する WordPress プラグイン。
 * Version: 1.0.0-alpha
 * Author: AltGen
 * Author URI: https://example.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-alt-text
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin Constants.
if ( ! defined( 'AI_ALT_TEXT_PLUGIN_FILE' ) ) {
    define( 'AI_ALT_TEXT_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'AI_ALT_TEXT_PLUGIN_DIR' ) ) {
    define( 'AI_ALT_TEXT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Autoload via Composer.
$autoload_path = AI_ALT_TEXT_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $autoload_path ) ) {
    require_once $autoload_path;
}

if ( ! function_exists( 'ai_alt_text_missing_vendor_notice' ) && ! file_exists( $autoload_path ) ) {
    // Admin notice if vendor not found.
    function ai_alt_text_missing_vendor_notice() {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'AI Alt Text Generator: Composer autoload ファイルが見つかりません。`composer install` を実行してください。', 'ai-alt-text' ) . '</p></div>';
    }
    add_action( 'admin_notices', 'ai_alt_text_missing_vendor_notice' );
    return;
}

// Bootstrap plugin.
add_action( 'plugins_loaded', function () {
    if ( class_exists( 'AiAltText\Plugin' ) ) {
        AiAltText\Plugin::init();
    }
} ); 