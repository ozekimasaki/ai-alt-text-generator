<?php
namespace AiAltText;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Unified logging and error handling for the plugin.
 *
 * This class implements the PSR-3 LoggerInterface to provide a standardized
 * logging mechanism. It handles different log levels and directs output
 * to the WordPress debug log when enabled.
 *
 * @since 1.1.0
 */
class Logger implements LoggerInterface {
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Log prefix for this plugin.
     */
    private const LOG_PREFIX = '[AI Alt Text] ';

    /**
     * System is unusable.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function emergency(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function alert(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function critical(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function error(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function warning(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function notice(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function info(string|\Stringable $message, array $context = []): void {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function debug(string|\Stringable $message, array $context = []): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->log(LogLevel::DEBUG, $message, $context);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void {
        if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }

        $formatted_message = self::LOG_PREFIX . strtoupper($level) . ': ' . $message;

        if (!empty($context)) {
            $formatted_message .= ' | Context: ' . wp_json_encode($context);
        }

        error_log($formatted_message);
    }

    /**
     * Create a standardized WP_Error object.
     *
     * @param string $code    Error code.
     * @param string $message Error message (already translated).
     * @param mixed  $data    Additional error data.
     * @return \WP_Error
     */
    public static function create_error(string $code, string $message, $data = null): \WP_Error {
        self::get_instance()->error("WP_Error created: [{$code}] {$message}", ['data' => $data]);
        return new \WP_Error($code, $message, $data);
    }

    /**
     * Handle exceptions and convert to WP_Error.
     *
     * @param \Throwable $exception Exception to handle.
     * @param string     $context   Context where exception occurred.
     * @return \WP_Error
     */
    public static function handle_exception(\Throwable $exception, string $context = ''): \WP_Error {
        $message = $exception->getMessage();
        $code = 'exception_' . strtolower(str_replace('\\', '_', get_class($exception)));

        self::get_instance()->error("Exception in {$context}: {$message}", [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return new \WP_Error($code, $message);
    }

    /**
     * Validate API response and return WP_Error if invalid.
     *
     * @param mixed  $response API response to validate.
     * @param string $context  Context for error reporting.
     * @return \WP_Error|null Returns WP_Error if invalid, null if valid.
     */
    public static function validate_api_response($response, string $context = 'API call'): ?\WP_Error {
        if (is_wp_error($response)) {
            self::get_instance()->error("API error in {$context}: " . $response->get_error_message(), [
                'error_code' => $response->get_error_code(),
                'error_data' => $response->get_error_data(),
            ]);
            return $response;
        }

        if (empty($response)) {
            return self::create_error('empty_response', __('API からの応答が空です。', Constants::TEXT_DOMAIN));
        }

        return null;
    }
} 