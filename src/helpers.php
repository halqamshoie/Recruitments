<?php

require_once __DIR__ . '/config.php';

if (!function_exists('url')) {
    /**
     * Generate a full URL for the application.
     * @param string $path
     * @return string
     */
    function url($path = '') {
        $path = ltrim($path, '/');
        return BASE_URL . '/' . $path;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a specific path within the application.
     * @param string $path
     */
    function redirect($path = '') {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a URL for an asset (css, js, images).
     * @param string $path
     * @return string
     */
    function asset($path = '') {
        return url($path);
    }
}
