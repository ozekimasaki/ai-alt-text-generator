<?php

// Composer のオートローダーを読み込む
require_once dirname(__DIR__) . '/vendor/autoload.php';

// WordPress関数のモック
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '') {
        return 'en-US';
    }
} 