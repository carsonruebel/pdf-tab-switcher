<?php
/**
 * Tab Switcher widget for Elementor
 *
 * Provides a custom Elementor widget that displays two selectable PDF tabs
 * on a clipboard-style backdrop.
 *
 * @package     PDF_Tab_Switcher
 * @since       1.0.0
 * @author      Carson Ruebel
 * @copyright   2025 Carson Ruebel
 * @license     GPL-2.0-or-later
 * @link        https://carsonruebel.com
 */

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Switcher_Widget extends Widget_Base {

    public function get_name() {
        return 'switcher-widget';
    }

    public function get_title() {
        return __('PDF Switcher', 'pdf-tab-switcher');
    }

    public function get_icon() {
        return 'eicon-document-file';
    }

    public function get_categories() {
        return ['general'];
    }

    public function register_controls() {

        $this->start_controls_section('pdf_tab_section', [
            'label' => __('PDF Tab Switcher Settings', 'pdf-tab-switcher'),
        ]);

        $this->add_control('upload_header', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => '<h4 style="text-align:center; font-weight:bold; margin:10px 0;">Upload PDFs</h4>',
            'separator' => 'before', // Optional: adds a horizontal line before
        ]);

        $this->add_control('editor_visibility_note', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => '<p style="margin-top:10px;"><em>PDFs and tab formatting will display in the \'preview changes\' and live site, but may not appear inside the Elementor editor view.</em></p>',
        ]);

        $this->add_control('background_toggle', [
            'label' => __( 'Background Toggle', 'pdf-tab-switcher' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'clipboard' => 'Clipboard Background',
                'none' => 'No Background',
            ],
            'default' => 'clipboard',
            'separator' => 'after',
        ]);

        // Commented out for future feature addition
        /*
        $this->add_control('background_image', [
            'label' => __('Background Image', 'pdf-tab-switcher'),
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);
        */

        $this->add_control('tab1_title', [
            'label' => __('Tab 1 Label', 'pdf-tab-switcher'),
            'type' => Controls_Manager::TEXT,
            'default' => __('Tab 1', 'pdf-tab-switcher'),
        ]);

        $this->add_control('tab2_title', [
            'label' => __('Tab 2 Label', 'pdf-tab-switcher'),
            'type' => Controls_Manager::TEXT,
            'default' => __('Tab 2', 'pdf-tab-switcher'),
        ]);

        $this->add_control('t1_pdf', [
            'label' => __('PDF 1', 'pdf-tab-switcher'),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'media_types' => ['application/pdf'],
            'default' => [],
        ]);

        $this->add_control('t2_pdf', [
            'label' => __('PDF 2', 'pdf-tab-switcher'),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'media_types' => ['application/pdf'],
            'default' => [],
        ]);

        $this->end_controls_section();
    }

    public function render() {
        $settings = $this->get_settings_for_display();
        $t1_url = $settings['t1_pdf']['url'] ?? '';
        $t2_url = $settings['t2_pdf']['url'] ?? '';
        $background_mode = $settings['background_toggle'] ?? 'clipboard';
        $bg_image_url = $settings['background_image']['url'] ?? '';
        $t1_label = $settings['tab1_title'] ?? 'Tab 1';
        $t2_label = $settings['tab2_title'] ?? 'Tab 2';

        echo '<div class="clipboard-widget-container">';

        if ($background_mode === 'clipboard') {
            $plugin_base_url = defined('PDF_TAB_SWITCHER_URL') ? PDF_TAB_SWITCHER_URL : '';
            $default_clipboard_url = $plugin_base_url . 'assets/backdrops/clipboard.png';
            echo '<img src="' . esc_url($default_clipboard_url) . '" class="clipboard-background">';
        } elseif ($background_mode === 'none') {
            // Spacer to establish container height for positioning tabs
            echo '<div class="clipboard-spacer" style="width:100%; padding-top:141.4%;"></div>';
        }

        // ─────────────────────────────
        // 2️⃣ Render tab toggle buttons
        // ─────────────────────────────
        // Define hardcoded coordinates
        $coords = [
            'clipboard' => ['top' => 10, 'left' => 3, 'width' => 94, 'height' => 0, 'rotation' => 0],
        ];

        // Define override positions for tabs
        $tab1_override = [
            'no_padding' => true,
            'left' => 6,
            'top' => 8,
            'width' => 20,
            'height' => 3.5,
        ];

        $tab2_override = [
            'no_padding' => true,
            'left' => 26,
            'top' => 8,
            'width' => 20,
            'height' => 3.5,
        ];

        if ($t1_url && !$t2_url) {
            $this->render_overlay_element($coords, [], '', ['no_padding' => true], 'clipboard', '[pdf-embedder url="' . esc_url($t1_url) . '"]', 'tab-1-pdf auto-resize');
        } elseif ($t2_url && !$t1_url) {
            $this->render_overlay_element($coords, [], '', ['no_padding' => true], 'clipboard', '[pdf-embedder url="' . esc_url($t2_url) . '"]', 'tab-2-pdf auto-resize');
        } elseif ($t1_url && $t2_url) {
            $this->render_overlay_element($coords, ['title' => $t1_label], '', $tab1_override, 'clipboard', null, 'tab-1-btn auto-resize');
            $this->render_overlay_element($coords, ['title' => $t2_label], '', $tab2_override, 'clipboard', null, 'tab-2-btn auto-resize');

            $this->render_overlay_element($coords, [], '', ['no_padding' => true], 'clipboard', '[pdf-embedder url="' . esc_url($t1_url) . '"]', 'tab-1-pdf auto-resize');
            $this->render_overlay_element($coords, [], '', ['no_padding' => true], 'clipboard', '[pdf-embedder url="' . esc_url($t2_url) . '"]', 'tab-2-pdf auto-resize');
        }

        echo '</div>'; // .clipboard-widget-container
    }

    /**
     * Renders an absolutely positioned overlay element.
     *
     * @param array       $layout_elements    Base coordinate layout definitions.
     * @param array       $content            Content like text title.
     * @param string      $screen_class       Additional CSS class.
     * @param array       $style_overrides    Coordinate overrides.
     * @param string      $layout_key         Key for coordinate layout.
     * @param string|null $shortcode_content  Optional shortcode content.
     * @param string      $extra_class        Extra CSS class.
     * @param string|null $html_content       Optional raw HTML content.
     *
     * @return void
     */
    private function render_overlay_element(
        array $layout_elements,
        array $content,
        string $screen_class,
        array $style_overrides,
        string $layout_key,
        ?string $shortcode_content = null,
        string $extra_class = '',
        ?string $html_content = null,
    ) {
        if (!$layout_key || !isset($layout_elements[$layout_key])) return;

        $coords = $layout_elements[$layout_key];
        $box_left   = $style_overrides['left'] ?? $coords['left'];
        $box_top    = $style_overrides['top'] ?? $coords['top'];
        $box_width  = $style_overrides['width'] ?? $coords['width'];
        $box_height = $style_overrides['height'] ?? $coords['height'];
        $rotation   = $coords['rotation'] ?? 0;

        $font_size  = $box_height * 0.8 * 12;
        $style = sprintf(
            'left: %.2f%%; top: %.2f%%; width: %.2f%%; height: %.2f%%; font-size: %.2fpx; z-index: 10; transform: rotate(%.2fdeg);',
            $box_left, $box_top, $box_width, $box_height, $font_size, $rotation
        );

        $label = $content['title'] ?? '[No Title]';
        $text = esc_html($label);
        $inner_html = $shortcode_content ? do_shortcode($shortcode_content) : ($html_content ?? '<span>' . $text . '</span>');

        echo sprintf(
            '<div class="%s %s" style="position:absolute;%s">%s</div>',
            esc_attr($screen_class),
            esc_attr($extra_class),
            esc_attr($style),
            $inner_html
        );
    }



    public function get_style_depends() {
        return [ 
            'clipboard-style' 
        ];
    }

    public function get_script_depends() {
        return [ 
            'tab-switcher-clipboard',
            'tab-switcher-resize'
        ];
    }
}