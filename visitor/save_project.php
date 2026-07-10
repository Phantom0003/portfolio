<?php
/**
 * visitor/save_project.php — Toggle bookmarking/saving a project
 */
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

if (!is_visitor()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to save projects']);
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

$projectId = (int)($_POST['project_id'] ?? 0);
$userId    = current_user_id();

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
    exit;
}

// Check if project exists and is active
$project = Database::fetchOne("SELECT id, title FROM projects WHERE id = ? AND status != 'archived'", [$projectId]);
if (!$project) {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit;
}

// Check if already saved
$existing = Database::fetchOne("SELECT id FROM saved_projects WHERE user_id = ? AND project_id = ?", [$userId, $projectId]);

if ($existing) {
    // Unsave
    Database::delete('saved_projects', 'user_id = ? AND project_id = ?', [$userId, $projectId]);
    echo json_encode([
        'success' => true,
        'saved'   => false,
        'message' => 'Project removed from saved list'
    ]);
} else {
    // Save
    Database::insert('saved_projects', [
        'user_id'    => $userId,
        'project_id' => $projectId
    ]);
    
    // Create a notification for the visitor themselves or admin (optional, let's just make a user notification)
    create_notification('user', $userId, 'project_saved', '🔖 Project Bookmarked', 
        "You saved project: " . $project['title'], "/portfolio/projects.php");

    echo json_encode([
        'success' => true,
        'saved'   => true,
        'message' => 'Project saved to your dashboard'
    ]);
}
