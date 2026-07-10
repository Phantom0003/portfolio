<?php
/**
 * visitor/mark_notifs.php — Mark all visitor notifications as read via AJAX
 */
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

if (!is_visitor()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check CSRF
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token mismatch']);
    exit;
}

$userId = current_user_id();

Database::update(
    'notifications',
    ['is_read' => 1],
    "recipient_type = 'user' AND recipient_id = ? AND is_read = 0",
    [$userId]
);

echo json_encode(['success' => true, 'message' => 'Notifications marked as read']);
