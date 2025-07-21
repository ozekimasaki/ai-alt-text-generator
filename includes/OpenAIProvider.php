<?php
namespace AiAltText;

use OpenAI;
use WP_Error;

/**
 * OpenAI Provider implementation.
 * 
 * This class implements the AI_Provider_Interface for OpenAI's API,
 * handling image alt text generation using models like GPT-4.
 * 
 * @since 1.0.0
 */
class OpenAIProvider implements AIProviderInterface {

    /**
     * OpenAI API client instance.
     *
     * @var \OpenAI\Client|null Null if API key not configured.
     */
    private ?\OpenAI\Client $client = null;

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return self The provider instance.
     */
    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize OpenAI client if API key available.
     * 
     * @since 1.0.0
     */
    private function __construct() {
        $api_key_option = Constants::OPTION_API_KEY_PREFIX . 'openai';
        $api_key = Config::get_option( $api_key_option );
        if ( ! empty( $api_key ) ) {
            $this->client = OpenAI::client( $api_key );
        }
    }

    /**
     * Generate alternative text using OpenAI from image file.
     * 
     * @inheritDoc
     */
    public function generate_alt( int $attachment_id, string $lang, string $model ): string|\WP_Error {
        if ( null === $this->client ) {
            return Logger::create_error( 'no_api_key', __( 'API キーが設定されていません。', Constants::TEXT_DOMAIN ) );
        }

        $image_path = get_attached_file( $attachment_id );
        if ( ! $image_path || ! file_exists( $image_path ) ) {
            return Logger::create_error( 'file_missing', __( '画像ファイルを取得できませんでした。', Constants::TEXT_DOMAIN ), [
                'attachment_id' => $attachment_id,
                'path'          => $image_path,
            ] );
        }

        Logger::get_instance()->debug( "Starting alt generation with OpenAI", [
            'attachment_id' => $attachment_id,
            'language'      => $lang,
            'model'         => $model,
        ] );

        try {
            $prompt = __( '以下の画像の内容を説明する代替テキストを、一文で簡潔に', Constants::TEXT_DOMAIN ) . " ({$lang})" . __( 'で生成してください。', Constants::TEXT_DOMAIN );

            $response = $this->client->chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:' . mime_content_type($image_path) . ';base64,' . base64_encode(file_get_contents($image_path)),
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            $text = $response->choices[0]->message->content;
            
            Logger::get_instance()->info( "Alt text generated successfully with OpenAI", [
                'attachment_id' => $attachment_id,
                'text_length'   => strlen( $text ),
            ] );
            
            return trim( $text );
        } catch ( \Throwable $e ) {
            return Logger::handle_exception( $e, 'OpenAI alt generation' );
        }
    }

    /**
     * Get OpenAI-specific settings fields.
     * 
     * @return array
     */
    public static function get_provider_settings(): array {
        return [
            'api_key' => [
                'label' => __( 'OpenAI API キー', Constants::TEXT_DOMAIN ),
                'callback' => 'field_api_key',
            ],
            'model' => [
                'label' => __( 'OpenAI モデル', Constants::TEXT_DOMAIN ),
                'callback' => 'field_model',
            ],
        ];
    }
} 