<?php
namespace AiAltText;

use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use WP_Error;

/**
 * Gemini AI Provider implementation.
 * 
 * This class implements the AI_Provider_Interface for Google's Gemini API,
 * handling image alt text generation using Gemini models.
 * 
 * @since 1.0.0
 */
class GeminiProvider implements AIProviderInterface {

    /**
     * Gemini API client instance.
     *
     * @var \Gemini\Client|null Null if API key not configured.
     */
    private ?\Gemini\Client $client = null;

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance.
     * 
     * Creates and returns a single instance of the provider.
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
     * Constructor - Initialize Gemini API client if API key available.
     * 
     * @since 1.0.0
     */
    private function __construct() {
        $api_key_option = Constants::OPTION_API_KEY_PREFIX . 'gemini';
        $api_key = Config::get_option( $api_key_option );
        if ( ! empty( $api_key ) ) {
            $this->client = \Gemini::factory()
                ->withApiKey( $api_key )
                ->make();
        }
    }

    /**
     * Generate alternative text using Gemini AI from image file.
     * 
     * @inheritDoc
     */
    public function generate_alt( int $attachment_id, string $lang, string $model ): string|\WP_Error {
        if ( null === $this->client ) {
            return Logger::create_error( 'no_api_key', __( 'API キーが設定されていません。', Constants::TEXT_DOMAIN ) );
        }

        Logger::get_instance()->debug( "Starting alt generation with Gemini", [
            'attachment_id' => $attachment_id,
            'language'      => $lang,
            'model'         => $model,
        ] );

        try {
            $file_path = get_attached_file( $attachment_id );
            if ( ! $file_path || ! file_exists( $file_path ) ) {
                return Logger::create_error( 'file_missing', __( '画像ファイルを取得できませんでした。', Constants::TEXT_DOMAIN ), [
                    'attachment_id' => $attachment_id,
                    'file_path'     => $file_path,
                ] );
            }

            $prompt = __( '以下の画像の内容を説明する代替テキストを、一文で簡潔に', Constants::TEXT_DOMAIN ) . " ({$lang})" . __( 'で生成してください。', Constants::TEXT_DOMAIN );

            $mime  = wp_check_filetype( $file_path )['type'] ?? 'image/jpeg';
            $mimeEnum = MimeType::tryFrom( $mime ) ?? MimeType::JPEG;
            $blob  = new Blob( $mimeEnum, base64_encode( file_get_contents( $file_path ) ) );

            $response = $this->client
                ->generativeModel( model: $model )
                ->generateContent( $prompt, $blob );

            /** @var GenerateContentResponse $response */
            $text = trim( $response->text() );
            
            Logger::get_instance()->info( "Alt text generated successfully with Gemini", [
                'attachment_id' => $attachment_id,
                'text_length'   => strlen( $text ),
            ] );
            
            return $text;
        } catch ( \Throwable $e ) {
            return Logger::handle_exception( $e, 'Gemini alt generation' );
        }
    }

    /**
     * Get Gemini-specific settings fields.
     * 
     * @return array
     */
    public static function get_provider_settings(): array {
        return [
            'api_key' => [
                'label' => __( 'Gemini API キー', Constants::TEXT_DOMAIN ),
                'callback' => 'field_api_key',
            ],
            'model' => [
                'label' => __( 'Gemini モデル', Constants::TEXT_DOMAIN ),
                'callback' => 'field_model',
            ],
        ];
    }
} 