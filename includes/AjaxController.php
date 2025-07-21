<?php
namespace AiAltText;

use WP_Error;

/**
 * Ajax request handler for alt text generation operations.
 * 
 * This class manages all Ajax endpoints for the plugin, handling:
 * - Security validation (nonces, user capabilities)
 * - Request parameter validation and sanitization
 * - Service orchestration via dependency injection
 * - Response formatting and error handling
 * - Comprehensive logging for debugging and monitoring
 * 
 * All methods are static as this class serves as a stateless controller.
 * 
 * @since 1.0.0
 */
class AjaxController {

    /**
     * Register WordPress Ajax action hooks.
     * 
     * Sets up the Ajax endpoints for authenticated users.
     * Currently supports:
     * - Single image alt text generation
     * 
     * Future endpoints (bulk generation) will be added here.
     *
     * @since 1.0.0
     */
    public static function init(): void {
        add_action( 'wp_ajax_' . Constants::AJAX_GENERATE_SINGLE, [ self::class, 'handle_single' ] );
    }

    /**
     * Handle single image alt text generation Ajax request.
     * 
     * This endpoint processes requests to generate alt text for a single image.
     * The workflow includes:
     * 1. Security validation (nonce verification, capability check)
     * 2. Request parameter validation
     * 3. Service retrieval from container
     * 4. Alt text generation via AI API
     * 5. Database updates (alt text + generation flag)
     * 6. JSON response with success/error status
     * 
     * Expected POST parameters:
     * - nonce: Security nonce for verification
     * - attachment_id: WordPress attachment ID (integer)
     * 
     * Response format:
     * - Success: { success: true, data: { alt: "generated text" } }
     * - Error: { success: false, data: "error message" }
     *
     * @since 1.0.0
     * @return void Outputs JSON response and terminates.
     */
    public static function handle_single(): void {
        check_ajax_referer( Constants::NONCE_GENERATE, 'nonce' );

        if ( ! current_user_can( 'upload_files' ) ) {
            Logger::get_instance()->warning( 'Unauthorized alt generation attempt', [
                'user_id' => get_current_user_id(),
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ] );
            wp_send_json_error( __( '権限がありません。', Constants::TEXT_DOMAIN ), 403 );
        }

        $attachment_id = absint( $_POST['attachment_id'] ?? 0 );
        if ( ! $attachment_id ) {
            Logger::get_instance()->warning( 'Invalid attachment ID in alt generation request', [
                'post_data' => $_POST,
            ] );
            wp_send_json_error( __( '添付 ID が不正です。', Constants::TEXT_DOMAIN ), 400 );
        }

        Logger::get_instance()->info( 'Starting single alt generation', [
            'attachment_id' => $attachment_id,
            'user_id'       => get_current_user_id(),
        ] );

        /** @var AIProviderInterface $service */
        $service = Container::get( 'ai_provider' );

        if ( ! $service ) {
            Logger::get_instance()->error( 'AI service not available' );
            wp_send_json_error( __( 'サービスが利用できません。', Constants::TEXT_DOMAIN ), 500 );
        }
        
        $lang = Config::get_language();
        $model = Config::get_model();
        $result  = $service->generate_alt( $attachment_id, $lang, $model );

        if ( is_wp_error( $result ) ) {
            Logger::get_instance()->error( 'Alt generation failed', [
                'attachment_id' => $attachment_id,
                'error_code'    => $result->get_error_code(),
                'error_message' => $result->get_error_message(),
            ] );
            wp_send_json_error( $result->get_error_message(), 500 );
        }

        // Update alt text.
        update_post_meta( $attachment_id, Constants::META_WP_ALT, $result );
        update_post_meta( $attachment_id, Constants::META_ALT_GENERATED, '1' );

        Logger::get_instance()->info( 'Single alt generation completed successfully', [
            'attachment_id' => $attachment_id,
            'alt_length'    => strlen( $result ),
        ] );

        wp_send_json_success( [ 'alt' => $result ] );
    }
} 