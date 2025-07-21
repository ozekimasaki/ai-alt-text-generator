<?php
namespace AiAltText;

/**
 * Simple service container for dependency injection.
 */
class Container {

    /**
     * Service instances.
     *
     * @var array<string, object>
     */
    private static array $instances = [];

    /**
     * Service factories.
     *
     * @var array<string, callable>
     */
    private static array $factories = [];

    /**
     * Register a service factory.
     *
     * @param string   $name    Service name.
     * @param callable $factory Factory function.
     */
    public static function register( string $name, callable $factory ): void {
        self::$factories[$name] = $factory;
    }

    /**
     * Get service instance (singleton).
     *
     * @param string $name Service name.
     * @return object|null
     */
    public static function get( string $name ): ?object {
        if ( isset( self::$instances[$name] ) ) {
            return self::$instances[$name];
        }

        if ( isset( self::$factories[$name] ) ) {
            $instance = call_user_func( self::$factories[$name] );
            if ( is_object( $instance ) ) {
                self::$instances[$name] = $instance;
                return $instance;
            }
        }

        Logger::get_instance()->warning( "Service not found: {$name}" );
        return null;
    }

    /**
     * Check if service is registered.
     *
     * @param string $name Service name.
     * @return bool
     */
    public static function has( string $name ): bool {
        return isset( self::$factories[$name] ) || isset( self::$instances[$name] );
    }

    /**
     * Register all core services.
     */
    public static function init(): void {
        // Register active AI provider
        self::register( 'ai_provider', function() {
            $provider_class = Config::get_provider_class();
            return $provider_class::get_instance();
        });

        // Register Settings
        self::register( 'settings', function() {
            return Settings::init();
        });

        // Register Ajax Controller (stateless, but for consistency)
        self::register( 'ajax_controller', function() {
            return new class {
                public function init(): void {
                    AjaxController::init();
                }
            };
        });

        Logger::get_instance()->debug( 'Container services registered' );
    }

    /**
     * Clear all instances (for testing).
     */
    public static function clear(): void {
        self::$instances = [];
        self::$factories = [];
    }
} 