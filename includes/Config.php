<?php
namespace AiAltText;

/**
 * Manages plugin configuration and settings retrieval.
 *
 * This class centralizes access to all plugin options, providing
 * default values and handling dynamic settings based on the selected AI provider.
 * It abstracts the underlying WordPress `get_option` calls and adds a
 * layer of logic for determining correct, context-aware configuration values.
 *
 * @since 1.1.0
 */
class Config {

    /**
     * Get option value with a fallback to a default.
     *
     * @param string $option_name The name of the option to retrieve.
     * @param mixed  $default     Optional. Default value to return if the option does not exist.
     * @return mixed The value of the option, or the default.
     */
    public static function get_option( string $option_name, $default = '' ) {
        return get_option( $option_name, $default );
    }

    /**
     * Get the configured language for alt text generation.
     *
     * Falls back to the WordPress site language if no specific language is set.
     *
     * @return string The language code (e.g., 'en', 'ja').
     */
    public static function get_language(): string {
        $lang = self::get_option( Constants::OPTION_LANGUAGE );
        if ( empty( $lang ) ) {
            $lang = get_bloginfo( 'language' ) ?: Constants::DEFAULT_LANGUAGE;
        }
        return $lang;
    }

    /**
     * Get the currently selected AI provider.
     *
     * @return string The identifier of the AI provider (e.g., 'gemini').
     */
    public static function get_provider(): string {
        $provider = self::get_option( Constants::OPTION_PROVIDER, Constants::DEFAULT_PROVIDER );
        if ( ! array_key_exists( $provider, Constants::AVAILABLE_PROVIDERS ) ) {
            $provider = Constants::DEFAULT_PROVIDER;
        }
        return $provider;
    }

    /**
     * Get the fully qualified class name for the current AI provider.
     *
     * @return string The class name of the provider implementation.
     */
    public static function get_provider_class(): string {
        $provider_id = self::get_provider();
        $provider_map = [
            'gemini' => __NAMESPACE__ . '\\GeminiProvider',
            'openai' => __NAMESPACE__ . '\\OpenAIProvider',
        ];

        return $provider_map[$provider_id] ?? $provider_map[Constants::DEFAULT_PROVIDER];
    }

    /**
     * Get the database option key for the current provider's API key.
     *
     * @return string The option name for the API key.
     */
    public static function get_api_key_option_key(): string {
        return Constants::OPTION_API_KEY_PREFIX . self::get_provider();
    }

    /**
     * Get the database option key for the current provider's model setting.
     *
     * @return string The option name for the model.
     */
    public static function get_model_option_key(): string {
        return Constants::OPTION_MODEL_PREFIX . self::get_provider();
    }

    /**
     * Get the configured AI model for the current provider.
     *
     * @return string The model identifier.
     */
    public static function get_model(): string {
        $provider = self::get_provider();
        $model_key = self::get_model_option_key();
        $model = self::get_option( $model_key );
        $models = self::get_model_options();

        if ( empty( $model ) || ! array_key_exists( $model, $models ) ) {
            // providerごとのデフォルトモデルを返す
            if ( 'gemini' === $provider ) {
                return Constants::DEFAULT_MODEL_GEMINI;
            }
            if ( 'openai' === $provider ) {
                return Constants::DEFAULT_MODEL_OPENAI;
            }
        }
        return $model;
    }

    /**
     * Get the list of available models for the current provider.
     *
     * @return array An associative array of model identifiers to display names.
     */
    public static function get_model_options(): array {
        $provider = self::get_provider();
        return Constants::AVAILABLE_MODELS[$provider] ?? [];
    }

    /**
     * Get the list of available languages.
     *
     * @return array An associative array of language codes to display names.
     */
    public static function get_language_options(): array {
        return Constants::AVAILABLE_LANGUAGES;
    }
} 