<?php
/**
 * Global Helper Functions
 * Galaxy Portfolio CMS
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/notifications.php';

// ─── Output & Sanitization ────────────────────────────────────

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_output(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function strip_and_trim(string $input): string {
    return trim(strip_tags($input));
}

// ─── Redirect ─────────────────────────────────────────────────

function redirect(string $url): never {
    header("Location: $url");
    exit;
}

function redirect_back(string $fallback = '/portfolio/'): never {
    $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
    redirect($ref);
}

// ─── Flash Messages ───────────────────────────────────────────

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function render_flash(): void {
    $flash = get_flash();
    if (!$flash) return;
    $icons = [
        'success' => 'bi-check-circle-fill',
        'error'   => 'bi-x-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'info'    => 'bi-info-circle-fill',
    ];
    $icon = $icons[$flash['type']] ?? 'bi-bell';
    echo "<div class='galaxy-alert galaxy-alert-{$flash['type']} fade-in-up'>
            <i class='bi {$icon}'></i>
            <span>" . sanitize_output($flash['message']) . "</span>
            <button onclick='this.parentElement.remove()' class='alert-close'><i class='bi bi-x'></i></button>
          </div>";
}

// ─── Settings ─────────────────────────────────────────────────

$_settings_cache = [];

function get_setting(string $key, string $default = ''): string {
    global $_settings_cache;
    if (empty($_settings_cache)) {
        try {
            $rows = Database::fetchAll("SELECT key_name, value FROM settings");
            foreach ($rows as $row) {
                $_settings_cache[$row['key_name']] = $row['value'];
            }
        } catch (Exception $e) {
            return $default;
        }
    }
    return $_settings_cache[$key] ?? $default;
}

function update_setting(string $key, string $value): void {
    global $_settings_cache;
    Database::query(
        "INSERT INTO settings (key_name, value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE value = ?",
        [$key, $value, $value]
    );
    $_settings_cache[$key] = $value;
}

// ─── Date Formatting ──────────────────────────────────────────

function format_date(?string $date, string $format = 'M j, Y'): string {
    if (!$date) return '';
    return date($format, strtotime($date));
}

function time_ago(?string $datetime): string {
    if (!$datetime) return '';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)       return 'just now';
    if ($diff < 3600)     return (int)($diff / 60) . ' min ago';
    if ($diff < 86400)    return (int)($diff / 3600) . ' hr ago';
    if ($diff < 604800)   return (int)($diff / 86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}

// ─── Slug Generator ───────────────────────────────────────────

function make_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function unique_slug(string $table, string $title, ?int $excludeId = null): string {
    $base = make_slug($title);
    $slug = $base;
    $i = 1;
    while (true) {
        $params = [$slug];
        $sql = "SELECT id FROM `$table` WHERE slug = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $existing = Database::fetchOne($sql, $params);
        if (!$existing) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ─── Reading Time ─────────────────────────────────────────────

function reading_time(string $content): int {
    $words = str_word_count(strip_tags($content));
    return max(1, (int) ceil($words / 200));
}

// ─── Stats for Admin ─────────────────────────────────────────

function get_admin_stats(): array {
    return [
        'visitors'    => (int) Database::fetchOne("SELECT COUNT(*) as c FROM users")['c'],
        'projects'    => (int) Database::fetchOne("SELECT COUNT(*) as c FROM projects WHERE status != 'archived'")['c'],
        'skills'      => (int) Database::fetchOne("SELECT COUNT(*) as c FROM skills")['c'],
        'blog_posts'  => (int) Database::fetchOne("SELECT COUNT(*) as c FROM blog_posts WHERE status = 'published'")['c'],
        'messages'    => (int) Database::fetchOne("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")['c'],
        'total_msg'   => (int) Database::fetchOne("SELECT COUNT(*) as c FROM messages")['c'],
        'comments'    => (int) Database::fetchOne("SELECT COUNT(*) as c FROM comments WHERE status = 'pending'")['c'],
        'media'       => (int) Database::fetchOne("SELECT COUNT(*) as c FROM media")['c'],
    ];
}

// ─── Pagination ───────────────────────────────────────────────

function paginate(string $sql, array $params, int $page, int $perPage = 10): array {
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as t";
    $total = (int) Database::fetchOne($countSql, $params)['total'];
    $totalPages = (int) ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    $items = Database::fetchAll("$sql LIMIT $perPage OFFSET $offset", $params);
    return [
        'items'       => $items,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => $totalPages,
    ];
}

// ─── Notifications (unread count) ────────────────────────────

function get_unread_notifications(string $type, int $id): int {
    $result = Database::fetchOne(
        "SELECT COUNT(*) as c FROM notifications WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0",
        [$type, $id]
    );
    return (int) ($result['c'] ?? 0);
}

// ─── Tech tag renderer ───────────────────────────────────────

function render_tech_tags(?string $jsonTechs): string {
    if (!$jsonTechs) return '';
    $techs = json_decode($jsonTechs, true) ?? [];
    $html = '';
    foreach ($techs as $tech) {
        $html .= '<span class="tech-tag">' . sanitize_output($tech) . '</span>';
    }
    return $html;
}

// ─── Avatar ──────────────────────────────────────────────────

function get_avatar_url(?string $path, string $name = 'User'): string {
    if ($path && file_exists(dirname(__DIR__) . '/uploads/' . $path)) {
        return '/portfolio/uploads/' . $path;
    }
    $initial = strtoupper(substr($name, 0, 1));
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=6B21A8&color=fff&size=128&bold=true";
}

// ─── JSON decode safe ────────────────────────────────────────

function json_decode_safe(?string $json): array {
    if (!$json) return [];
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

// ─── Validate Email ───────────────────────────────────────────

function is_valid_email(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ─── File size formatter ─────────────────────────────────────

function format_bytes(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
