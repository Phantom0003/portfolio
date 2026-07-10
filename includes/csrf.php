<?php
/**
 * CSRF Protection Helpers
 * Galaxy Portfolio CMS
 */

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(?string $token = null): bool {
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            http_response_code(403);
            set_flash('error', 'Invalid security token. Please try again.');
            redirect_back();
        }
    }
}
