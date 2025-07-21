<?php
namespace AiAltText;

/**
 * Settings page handler.
 */
class Settings {

    /** @var self|null */
    private static ?self $instance = null;

    public static function init(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function hooks(): void {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Add settings menu.
     */
    public function add_menu(): void {
        add_options_page(
            __( 'AI Alt Text 設定', Constants::TEXT_DOMAIN ),
            __( 'AI Alt Text', Constants::TEXT_DOMAIN ),
            'manage_options',
            Constants::SETTINGS_PAGE_SLUG,
            [ $this, 'render_page' ]
        );
    }

    /**
     * Register settings.
     */
    public function register_settings(): void {

        // Always register common settings
        register_setting( Constants::SETTINGS_GROUP, Constants::OPTION_LANGUAGE );
        register_setting( Constants::SETTINGS_GROUP, Constants::OPTION_PROVIDER );
        register_setting( Constants::SETTINGS_GROUP, Config::get_api_key_option_key() ); // Provider-specific API key
        register_setting( Constants::SETTINGS_GROUP, Config::get_model_option_key() );  // Provider-specific model

        // Register provider-specific settings
        $provider_class = Config::get_provider_class();
        $provider_settings = $provider_class::get_provider_settings();
        foreach ( $provider_settings as $key => $field ) {
            register_setting( Constants::SETTINGS_GROUP, $key );
        }

        add_settings_section(
            Constants::SETTINGS_SECTION,
            __( 'API 設定', Constants::TEXT_DOMAIN ),
            '__return_false',
            Constants::SETTINGS_GROUP
        );

        add_settings_field(
            Constants::OPTION_PROVIDER,
            __( 'AI プロバイダー', Constants::TEXT_DOMAIN ),
            [ $this, 'field_provider' ],
            Constants::SETTINGS_GROUP,
            Constants::SETTINGS_SECTION
        );

        // Add provider-specific fields
        foreach ( $provider_settings as $key => $field ) {
        add_settings_field(
                $key,
                $field['label'],
                [ $this, $field['callback'] ],
            Constants::SETTINGS_GROUP,
            Constants::SETTINGS_SECTION
        );
        }

        add_settings_field(
            Constants::OPTION_LANGUAGE,
            __( '優先言語', Constants::TEXT_DOMAIN ),
            [ $this, 'field_language' ],
            Constants::SETTINGS_GROUP,
            Constants::SETTINGS_SECTION
        );
    }

    /**
     * Render API key field.
     */
    public function field_api_key(): void {
        $api_key_option = Config::get_api_key_option_key();
        $value = esc_attr( Config::get_option( $api_key_option ) );
        echo '<input type="password" name="' . esc_attr( $api_key_option ) . '" value="' . $value . '" class="regular-text" />';
        echo '<p class="description">';

        $provider = Config::get_provider();
        if ( 'gemini' === $provider ) {
        echo sprintf(
            /* translators: %s: Google AI Studio URL */
            __( '%s で取得した API キーを入力してください。', Constants::TEXT_DOMAIN ),
            '<a href="https://aistudio.google.com/app/apikey?hl=ja" target="_blank" rel="noopener noreferrer">Google AI Studio</a>'
        );
        } elseif ( 'claude' === $provider ) {
            echo sprintf(
                /* translators: %s: Anthropic Console URL */
                __( '%s で取得した API キーを入力してください。', Constants::TEXT_DOMAIN ),
                '<a href="https://console.anthropic.com/" target="_blank" rel="noopener noreferrer">Anthropic Console</a>'
            );
        } else {
            echo sprintf(
                /* translators: %s: OpenAI Platform URL */
                __( '%s で取得した API キーを入力してください。', Constants::TEXT_DOMAIN ),
                '<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer">OpenAI Platform</a>'
            );
        }

        echo '</p>';
    }

    /**
     * Render provider selection field.
     */
    public function field_provider(): void {
        $current_provider = Config::get_provider();
        $providers = Constants::AVAILABLE_PROVIDERS;
        
        echo '<select name="' . esc_attr( Constants::OPTION_PROVIDER ) . '" class="regular-text">';
        foreach ( $providers as $provider_id => $provider_name ) {
            $selected = selected( $current_provider, $provider_id, false );
            echo '<option value="' . esc_attr( $provider_id ) . '"' . $selected . '>' . esc_html( $provider_name ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( '使用する AI プロバイダーを選択してください。変更後、プロバイダー固有の設定項目が更新されます。', Constants::TEXT_DOMAIN ) . '</p>';
    }

    /**
     * Render model selection field.
     */
    public function field_model(): void {
        $model_key = Config::get_model_option_key();
        $current_model = Config::get_option( $model_key );
        $models = Config::get_model_options();
        
        if ( empty( $models ) ) {
            echo '<em>' . esc_html__( 'このプロバイダーには利用可能なモデルがありません。', Constants::TEXT_DOMAIN ) . '</em>';
            return;
        }
        
        echo '<select name="' . esc_attr( $model_key ) . '" class="regular-text">';
        foreach ( $models as $model_id => $model_name ) {
            $selected = selected( $current_model, $model_id, false );
            echo '<option value="' . esc_attr( $model_id ) . '"' . $selected . '>' . esc_html( $model_name ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( '代替テキスト生成に使用する AI モデルを選択してください。', Constants::TEXT_DOMAIN ) . '</p>';
    }

    /**
     * Render language selection field.
     */
    public function field_language(): void {
        $current_lang = Config::get_language();
        $languages = Config::get_language_options();
        
        echo '<select name="' . esc_attr( Constants::OPTION_LANGUAGE ) . '" class="regular-text">';
        echo '<option value="">' . esc_html__( 'サイト言語を自動検出', Constants::TEXT_DOMAIN ) . '</option>';
        foreach ( $languages as $lang_code => $lang_name ) {
            $selected = selected( $current_lang, $lang_code, false );
            echo '<option value="' . esc_attr( $lang_code ) . '"' . $selected . '>' . esc_html( $lang_name ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( '代替テキストを生成する言語を選択してください。空の場合はサイト言語を使用します。', Constants::TEXT_DOMAIN ) . '</p>';
    }

    /**
     * Render settings page.
     */
    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'AI Alt Text 設定', Constants::TEXT_DOMAIN ) . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( Constants::SETTINGS_GROUP );
        do_settings_sections( Constants::SETTINGS_GROUP );
        submit_button();
        echo '</form>';
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const providerSelect = document.querySelector('select[name="<?php echo esc_js( Constants::OPTION_PROVIDER ); ?>"]');
                if (providerSelect) {
                    providerSelect.addEventListener('change', function() {
                        const form = this.form;
                        if (form) {
                            const submitButton = form.querySelector('p.submit input[type="submit"]');
                            if (submitButton) {
                                submitButton.disabled = true;
                                submitButton.value = '<?php echo esc_js( __( '保存中...', Constants::TEXT_DOMAIN ) ); ?>';
                            }
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                }
            });
        </script>
        <?php
        echo '</div>';
    }
} 