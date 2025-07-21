<?php
namespace AiAltText;

/**
 * Core plugin class responsible for initialization and hook management.
 * 
 * This class serves as the main entry point for the plugin, handling:
 * - Service container initialization
 * - WordPress hook registration
 * - Admin interface setup
 * - Asset enqueueing
 * - Attachment field modifications
 * 
 * @since 1.0.0
 */
class Plugin {

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance and initialize if needed.
     * 
     * This method ensures only one instance of the plugin class exists
     * throughout the WordPress request lifecycle.
     *
     * @since 1.0.0
     * @return self The plugin instance.
     */
    public static function init(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->setup_hooks();
        }
        return self::$instance;
    }

    /**
     * Register WordPress hooks and initialize services.
     * 
     * Sets up the service container and registers core WordPress hooks.
     * Admin-specific functionality is conditionally loaded only in admin context.
     *
     * @since 1.0.0
     */
    private function setup_hooks(): void {
        // Initialize container and services
        Container::init();
        
        // This needs to be hooked early to register the menu.
        Container::get( 'settings' );

        // Core hooks
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

        // Admin-only functionality
        if ( is_admin() ) {
            add_action( 'admin_init', [ $this, 'init_admin_services' ] );
        }
    }

    /**
     * Initialize admin-specific services and hooks.
     * 
     * Loads admin-only functionality including:
     * - Settings page via container
     * - Ajax controller for API requests
     * - Asset enqueueing hooks
     * - Attachment field modification filters
     *
     * @since 1.0.0
     */
    public function init_admin_services(): void {
        // Initialize Ajax controller via container
        $ajax_controller = Container::get( 'ajax_controller' );
        if ( $ajax_controller ) {
            $ajax_controller->init();
        }

        // Add generation button to media library views
        add_filter( 'attachment_fields_to_edit', [ $this, 'add_alt_generate_button' ], 10, 2 );
    }

    /**
     * Load plugin textdomain for internationalization.
     * 
     * Loads translation files from the /languages directory.
     * Called on 'init' hook to ensure WordPress is fully loaded.
     *
     * @since 1.0.0
     */
    public function load_textdomain(): void {
        load_plugin_textdomain( Constants::TEXT_DOMAIN, false, dirname( plugin_basename( AI_ALT_TEXT_PLUGIN_FILE ) ) . '/languages' );
        Logger::get_instance()->debug( 'Textdomain loaded' );
    }

    /**
     * Enqueue JavaScript for admin attachment screens.
     * 
     * Conditionally loads admin.js only on relevant pages (post.php, upload.php).
     * Includes localized data for Ajax endpoints and translated strings.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_scripts( string $hook ): void {
        $settings_page_hook = 'settings_page_' . Constants::SETTINGS_PAGE_SLUG;

        // Enqueue settings-specific script
        if ( $settings_page_hook === $hook ) {
            wp_enqueue_script(
                'ai-alt-text-settings',
                plugins_url( 'assets/js/settings.js', AI_ALT_TEXT_PLUGIN_FILE ),
                [],
                '1.0.1', // version bump for cache busting
                true
            );
            return; // Don't load the other script on this page
        }

        // Enqueue media library script
        $screen = get_current_screen();
        if ( $screen && ( 'post' === $screen->base || 'upload' === $screen->base ) ) {
        wp_register_script(
            Constants::SCRIPT_HANDLE_ADMIN,
                plugins_url( 'assets/js/admin.js', AI_ALT_TEXT_PLUGIN_FILE ),
            [],
                '1.0.1', // version bump
            true
        );

            wp_localize_script( Constants::SCRIPT_HANDLE_ADMIN, 'AiAltText', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( Constants::NONCE_GENERATE ),
            'i18n'    => [
                'generating' => __( '生成中...', Constants::TEXT_DOMAIN ),
                'error'      => __( '生成に失敗しました', Constants::TEXT_DOMAIN ),
            ],
                'ajaxAction' => Constants::AJAX_GENERATE_SINGLE, // Pass action string to JS
        ] );

        wp_enqueue_script( Constants::SCRIPT_HANDLE_ADMIN );
        }
    }

    /**
     * Add AI alt text generation button to attachment edit fields.
     * 
     * Conditionally adds generation/regeneration buttons based on:
     * - Empty alt text: Shows "AI で代替テキスト生成" button
     * - AI-generated alt text: Shows "AI で代替テキスト再生成" button
     * - Manual alt text: No button (preserves user input)
     *
     * @since 1.0.0
     * @param array  $form_fields Existing attachment form fields.
     * @param object $post        WP_Post attachment object.
     * @return array Modified form fields with added button if applicable.
     */
    public function add_alt_generate_button( array $form_fields, $post ): array {
        $alt = get_post_meta( $post->ID, Constants::META_WP_ALT, true );

        $is_alt_empty = empty($alt);
        $label_text = $is_alt_empty ? __( 'AI で代替テキスト生成', Constants::TEXT_DOMAIN ) : __( 'AI で代替テキスト再生成', Constants::TEXT_DOMAIN );

        $button  = '<button type="button" class="button ai-generate-alt" data-attachment-id="' . esc_attr( $post->ID ) . '">';
        $button .= esc_html( $label_text );
        $button .= '</button> <span class="ai-alt-status" style="margin-left:8px;"></span>';

        $form_fields['ai_generate_alt'] = [
            'label' => __( 'AI Alt Text', Constants::TEXT_DOMAIN ),
            'input' => 'html',
            'html'  => $button,
        ];

        return $form_fields;
    }
} 