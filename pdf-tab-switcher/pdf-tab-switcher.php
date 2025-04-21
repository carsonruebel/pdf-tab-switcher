<?php
/**
 * Plugin Name: PDF Tab Switcher
 * Description: A custom Elementor widget to embed and switch between two PDF files on an overlay.
 * Version: 1.0.0
 * Author: Carson Ruebel
 * Text Domain: pdf-tab-switcher
 * Requires Plugins: elementor, pdf-embedder
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ✅ Define plugin base URL early so it's globally available
define('PDF_TAB_SWITCHER_URL', plugin_dir_url(__FILE__));

// ✅ Toggle this to true when actively developing
define('SWITCHER_DEV_MODE', false); // or false in production

final class PDF_Tab_Switcher_Plugin {

    public function __construct() {
        // Hook into Elementor
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        // Register frontend scripts and styles
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );

        // Register Elementor editor scripts (admin/backend)
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'register_editor_assets' ] );

    }

    public function register_widgets( $widgets_manager ) {
        require_once( __DIR__ . '/widgets/class-switcher-widget.php' );
        $widgets_manager->register( new \Switcher_Widget() );
    }

    public function register_assets() {
        $is_dev = defined('SWITCHER_DEV_MODE') && SWITCHER_DEV_MODE;
    
        $css_base = plugin_dir_url(__FILE__) . ($is_dev ? 'src/css/' : 'assets/css/');
        $js_base  = plugin_dir_url(__FILE__) . ($is_dev ? 'src/js/'  : 'assets/js/');
    
        // Register CSS Files
        wp_enqueue_style(
            'clipboard-style',
            $css_base . ($is_dev ? 'clipboard.css' : 'clipboard.min.css'),
            [],
            '1.0.0'
        );
    
        // Register JS Files
        wp_enqueue_script(
            'tab-switcher-resize',
            $js_base . ($is_dev ? 'resize.js' : 'resize.min.js'),
            ['jquery'],
            '1.0.0',
            true
        );
    
        wp_enqueue_script(
            'tab-switcher-clipboard',
            $js_base . ($is_dev ? 'clipboard_toggle.js' : 'clipboard_toggle.min.js'),
            ['jquery'],
            '1.0.0',
            true
        );
    }
    
    public function register_editor_assets() {
        $is_dev = defined('SWITCHER_DEV_MODE') && SWITCHER_DEV_MODE;
    
        $js_base = plugin_dir_url(__FILE__) . ($is_dev ? 'src/js/' : 'assets/js/');
       
        wp_enqueue_script(
            'tab-switcher-resize',
            $js_base . ($is_dev ? 'resize.js' : 'resize.min.js'),
            ['jquery'],
            '1.0.0',
            true
        );
    }
    


}

new PDF_Tab_Switcher_Plugin();

// Force Elementor to load Google Fonts over HTTPS
add_filter('elementor/frontend/print_google_fonts_url', function ($url) {
    return preg_replace('/^http:/i', 'https:', $url);
});
