<?php
namespace AiAltText;

/**
 * Interface for AI providers to generate alt text.
 * 
 * This interface defines the contract for all AI service providers,
 * ensuring consistent method signatures across different implementations
 * (e.g., Gemini, OpenAI, Claude).
 * 
 * @since 1.0.0
 */
interface AIProviderInterface {

    /**
     * Generate alternative text for an image.
     * 
     * @param int    $attachment_id WordPress attachment ID.
     * @param string $lang          Language code for generation.
     * @param string $model         Model identifier for the provider.
     * @return string|\WP_Error Generated alt text or error.
     */
    public function generate_alt( int $attachment_id, string $lang, string $model ): string|\WP_Error;

    /**
     * Get provider-specific configuration options.
     * 
     * Returns an array of settings fields specific to this provider,
     * such as API key fields or provider-specific parameters.
     * 
     * @return array Associative array of setting fields.
     */
    public static function get_provider_settings(): array;
} 