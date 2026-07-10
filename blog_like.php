<?php
/**
 * blog_like.php — Handle AJAX likes for blog posts
 */
header('Content-Type: application/json');
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

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

$postId = (int)($_POST['post_id'] ?? 0);
if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Check if post exists and is published
$post = Database::fetchOne("SELECT id, title, likes FROM blog_posts WHERE id = ? AND status = 'published'", [$postId]);
if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$userId = is_logged_in() ? current_user_id() : null;
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Check if already liked
if ($userId) {
    $existing = Database::fetchOne("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?", [$postId, $userId]);
} else {
    $existing = Database::fetchOne("SELECT id FROM post_likes WHERE post_id = ? AND ip_address = ? AND user_id IS NULL", [$postId, $ip]);
}

if ($existing) {
    // Unlike
    Database::delete('post_likes', 'id = ?', [$existing['id']]);
    
    // Decrement likes
    $newLikes = max(0, $post['likes'] - 1);
    Database::update('blog_posts', ['likes' => $newLikes], 'id = ?', [$postId]);
    
    echo json_encode([
        'success' => true,
        'liked'   => false,
        'likes'   => $newLikes
    ]);
} else {
    // Like
    Database::insert('post_likes', [
        'post_id'    => $postId,
        'user_id'    => $userId,
        'ip_address' => $userId ? null : $ip
    ]);
    
    // Increment likes
    $newLikes = $post['likes'] + 1;
    Database::update('blog_posts', ['likes' => $newLikes], 'id = ?', [$postId]);
    
    echo json_encode([
        'success' => true,
        'liked'   => true,
        'likes'   => $newLikes
    ]);
}
