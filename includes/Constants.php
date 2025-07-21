<?php
namespace AiAltText;

/**
 * Plugin constants and configuration values.
 */
class Constants {

    // Option names
    public const OPTION_API_KEY_PREFIX  = 'ai_alt_text_api_key_'; // e.g. ai_alt_text_api_key_gemini
    public const OPTION_LANGUAGE        = 'ai_alt_text_language';
    public const OPTION_MODEL_PREFIX    = 'ai_alt_text_model_'; // e.g. ai_alt_text_model_gemini
    public const OPTION_PROVIDER        = 'ai_alt_text_provider';
    public const META_ALT_GENERATED     = '_ai_alt_generated';
    public const META_WP_ALT            = '_wp_attachment_image_alt';

    // Settings
    public const SETTINGS_GROUP         = 'ai_alt_text_settings';
    public const SETTINGS_SECTION       = 'ai_alt_text_section';
    public const SETTINGS_PAGE_SLUG     = 'ai-alt-text-settings';

    // AI API
    public const DEFAULT_MODEL_GEMINI   = 'models/gemini-2.5-flash-lite-preview-06-17';
    public const DEFAULT_MODEL_OPENAI   = 'gpt-4.1-mini-2025-04-14';
    public const DEFAULT_LANGUAGE       = 'ja';
    public const DEFAULT_PROVIDER       = 'gemini';

    // Available AI Providers
    public const AVAILABLE_PROVIDERS = [
        'gemini' => 'Google Gemini',
        'openai' => 'OpenAI',
        // 'claude' => 'Anthropic Claude', // Future support
    ];

    // Available AI models (based on https://ai.google.dev/gemini-api/docs/models)
    public const AVAILABLE_MODELS = [
        'gemini' => [
            'gemini-2.5-pro'                           => 'Gemini 2.5 Pro (最高性能・思考機能)',
            'gemini-2.5-flash'                         => 'Gemini 2.5 Flash (バランス型・思考機能)',
            'gemini-2.5-flash-lite-preview-06-17'     => 'Gemini 2.5 Flash-Lite (コスト効率・高スループット)',
            'gemini-2.0-flash'                         => 'Gemini 2.0 Flash (次世代・リアルタイム)',
            'gemini-2.0-flash-lite'                    => 'Gemini 2.0 Flash-Lite (コスト効率・低レイテンシ)',
        ],
        'openai' => [
            'gpt-4.1-mini-2025-04-14' => 'GPT-4.1 mini',
            'gpt-4.1-nano-2025-04-14' => 'GPT-4.1 nano',
        ],
    ];

    // Available languages (based on Gemini API supported languages)
    public const AVAILABLE_LANGUAGES = [
        'ja' => '日本語 (Japanese)',
        'en' => 'English',
        'zh' => '中文 (Chinese)',
        'ko' => '한국어 (Korean)',
        'fr' => 'Français (French)',
        'de' => 'Deutsch (German)',
    ];

    // Ajax actions
    public const AJAX_GENERATE_SINGLE   = 'ai_generate_alt';
    public const AJAX_GENERATE_BULK     = 'ai_generate_bulk_alt';

    // Nonces
    public const NONCE_GENERATE         = 'ai_generate_alt';
    public const NONCE_BULK_GENERATE    = 'ai_bulk_generate_alt';

    // Assets
    public const SCRIPT_HANDLE_ADMIN    = 'ai-alt-text-admin';
    public const SCRIPT_HANDLE_BULK     = 'ai-alt-text-bulk';

    // Text domain
    public const TEXT_DOMAIN            = 'ai-alt-text';
} 