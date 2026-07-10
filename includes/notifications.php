<?php
/**
 * Notifications Engine
 * Galaxy Portfolio CMS
 */

require_once __DIR__ . '/database.php';

/**
 * Create a new notification
 */
function create_notification(
    string $recipientType, 
    int $recipientId, 
    string $type, 
    string $title, 
    string $message = '', 
    string $actionUrl = '', 
    string $icon = 'bi-bell', 
    string $color = '#9333EA'
): int {
    try {
        return Database::insert('notifications', [
            'recipient_type' => $recipientType,
            'recipient_id'   => $recipientId,
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'action_url'     => $actionUrl,
            'icon'           => $icon,
            'color'          => $color,
            'is_read'        => 0
        ]);
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return 0;
    }
}

/**
 * Fetch notifications for a recipient
 */
function get_notifications(string $recipientType, int $recipientId, int $limit = 20): array {
    return Database::fetchAll(
        "SELECT * FROM notifications 
         WHERE recipient_type = ? AND recipient_id = ? 
         ORDER BY created_at DESC LIMIT ?",
        [$recipientType, $recipientId, $limit]
    );
}

/**
 * Mark a specific notification as read
 */
function mark_read(int $notificationId): bool {
    return (bool) Database::update(
        'notifications', 
        ['is_read' => 1], 
        'id = ?', 
        [$notificationId]
    );
}

/**
 * Delete a notification
 */
function delete_notification(int $notificationId): bool {
    return (bool) Database::delete('notifications', 'id = ?', [$notificationId]);
}
