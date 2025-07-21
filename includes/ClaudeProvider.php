<?php
namespace AiAltText;

use Claude\Claude3Api\Client;
use Claude\Claude3Api\Config as ClaudeConfig;
use Claude\Claude3Api\Models\Message;
use Claude\Claude3Api\Models\Content\TextContent;
use Claude\Claude3Api\Models\Content\ImageContent;
use Claude\Claude3Api\Requests\MessageRequest;

/**
 * Claude Provider implementation.
 * 
 * This class implements the AIProviderInterface for Anthropic's Claude API,
 * handling image alt text generation using Claude models.
 * 
 * @since 1.0.0
 */
class ClaudeProvider implements AIProviderInterface {

    /**
     * Claude API client instance.
     *
     * @var Client|null Null if API key not configured.
     */
    private ?Client $client = null;

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
     * Constructor - Initialize Claude client if API key available.
     * 
     * @since 1.0.0
     */
    private function __construct() {
        $api_key_option = Constants::OPTION_API_KEY_PREFIX . 'claude';
        $api_key = Config::get_option( $api_key_option );
        
        if ( ! empty( $api_key ) ) {
            $claude_config = new ClaudeConfig( $api_key );
            $this->client = new Client( $claude_config );
        }
    }

    /**
     * Generate alternative text for an image.
     * 
     * @param int    $attachment_id WordPress attachment ID.
     * @param string $lang          Language code for generation.
     * @param string $model         Model identifier for the provider.
     * @return string|\WP_Error Generated alt text or error.
     */
    public function generate_alt( int $attachment_id, string $lang, string $model ): string|\WP_Error {
        if ( null === $this->client ) {
            Logger::get_instance()->warning( 'Claude client not initialized - API key may be missing' );
            return new \WP_Error( 'client_not_initialized', __( 'Claude APIキーが設定されていません。', Constants::TEXT_DOMAIN ) );
        }

        try {
            // Get image URL from attachment ID
            $image_url = wp_get_attachment_url( $attachment_id );
            if ( ! $image_url ) {
                throw new \Exception( 'Unable to get image URL for attachment ID: ' . $attachment_id );
            }

            // Get the image data from the URL
            $image_data = wp_remote_get( $image_url );
            if ( is_wp_error( $image_data ) ) {
                throw new \Exception( 'Failed to download image: ' . $image_data->get_error_message() );
            }

            $body = wp_remote_retrieve_body( $image_data );
            if ( empty( $body ) ) {
                throw new \Exception( 'Empty image data received' );
            }

            // Get the MIME type of the image
            $finfo = new \finfo( FILEINFO_MIME_TYPE );
            $mime_type = $finfo->buffer( $body );

            // Base64 encode the image data
            $base64_image = base64_encode( $body );

            // Create prompt for alt text generation
            $prompt = sprintf(
                'As an expert in image accessibility, please provide a concise and descriptive alternative text for this image. The description should be brief and not exceed one sentence. Do not include any introductory phrases such as "This image shows" or "A picture of". Generate the text in %s.',
                $lang
            );

            // Create a proper MessageRequest using the library's classes
            $messageRequest = new MessageRequest();
            $messageRequest->setModel( $model );
            $messageRequest->setMaxTokens( 200 );

            // Create content objects
            $imageContent = new ImageContent( $base64_image, $mime_type );
            $textContent = new TextContent( $prompt );

            // Create a message with both image and text content
            $message = new Message( 'user', [ $imageContent, $textContent ] );
            $messageRequest->addMessage( $message );

            // Send the message using sendMessage method
            $response = $this->client->sendMessage( $messageRequest );

            // Extract response content
            $content = $response->getContent();
            if ( ! empty( $content ) && isset( $content[0]['text'] ) ) {
                $alt_text = trim( $content[0]['text'] );
                
                Logger::get_instance()->info( 'Claude alt text generated successfully', [
                    'attachment_id' => $attachment_id,
                    'model' => $model,
                    'lang' => $lang,
                ] );

                return $alt_text;
            }

            throw new \Exception( 'Failed to get alt text from Claude API response' );

        } catch ( \Exception $e ) {
            Logger::get_instance()->error( 'Claude API Error: ' . $e->getMessage(), [
                'attachment_id' => $attachment_id,
                'model' => $model,
                'lang' => $lang,
            ] );
            return new \WP_Error( 'claude_api_error', sprintf( 
                __( 'Claude API エラー: %s', Constants::TEXT_DOMAIN ),
                $e->getMessage()
            ) );
        }
    }

    /**
     * Get provider-specific settings fields.
     *
     * @since 1.0.0
     * @return array Provider settings configuration.
     */
    public static function get_provider_settings(): array {
        return [
            Constants::OPTION_API_KEY_PREFIX . 'claude' => [
                'label'    => __( 'Claude API キー', Constants::TEXT_DOMAIN ),
                'callback' => 'field_api_key',
            ],
            Constants::OPTION_MODEL_PREFIX . 'claude' => [
                'label'    => __( 'Claude モデル', Constants::TEXT_DOMAIN ),
                'callback' => 'field_model',
            ],
        ];
    }
} 