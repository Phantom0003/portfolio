<?php
/**
 * Authentication Helpers
 * Galaxy Portfolio CMS
 */

require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('galaxy_portfolio');
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'secure'   => false, // set true on HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ─── Role Check Helpers ───────────────────────────────────────

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function is_visitor(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'visitor';
}

function is_admin(): bool {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
}

function is_super_admin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin';
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    return $_SESSION['current_user'] ?? null;
}

// ─── Access Guards ────────────────────────────────────────────

function require_login(string $redirect = '/portfolio/login.php'): void {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect($redirect);
    }
}

function require_admin(string $redirect = '/portfolio/login.php'): void {
    if (!is_admin()) {
        if (!is_logged_in()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        }
        redirect($redirect);
    }
}

function require_visitor(string $redirect = '/portfolio/login.php'): void {
    if (!is_visitor()) {
        redirect($redirect);
    }
}

function require_super_admin(): void {
    if (!is_super_admin()) {
        redirect('/portfolio/admin/dashboard.php');
    }
}

// ─── Login / Logout ───────────────────────────────────────────

function login_user(array $user, string $role): void {
    session_regenerate_id(true);
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['user_role']    = $role;
    $_SESSION['user_name']    = $user['name'];
    $_SESSION['user_email']   = $user['email'];
    $_SESSION['current_user'] = $user;
    $_SESSION['login_time']   = time();

    // Update last login
    $table = ($role === 'visitor') ? 'users' : 'admins';
    Database::update($table, ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
    // Reset login attempts
    Database::update($table, ['login_attempts' => 0], 'id = ?', [$user['id']]);
}

function logout(): void {
    session_unset();
    session_destroy();
    redirect('/portfolio/login.php');
}

// ─── Login Attempt Throttle ───────────────────────────────────

function check_login_throttle(string $table, int $id): bool {
    $row = Database::fetchOne(
        "SELECT login_attempts, locked_until FROM `$table` WHERE id = ?",
        [$id]
    );
    if (!$row) return true;
    if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
        return false; // still locked
    }
    return true;
}

function increment_login_attempts(string $table, int $id): void {
    $row = Database::fetchOne("SELECT login_attempts FROM `$table` WHERE id = ?", [$id]);
    $attempts = ($row['login_attempts'] ?? 0) + 1;
    $lockedUntil = null;
    if ($attempts >= 5) {
        $lockedUntil = date('Y-m-d H:i:s', time() + 900); // 15 min lockout
        $attempts = 0;
    }
    Database::update($table, [
        'login_attempts' => $attempts,
        'locked_until'   => $lockedUntil,
    ], 'id = ?', [$id]);
}

// ─── Password Utilities ───────────────────────────────────────

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// ─── Registration ─────────────────────────────────────────────

function register_visitor(string $name, string $email, string $password): array {
    $email = strtolower(trim($email));
    $existing = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        return ['success' => false, 'message' => 'An account with this email already exists.'];
    }
    $id = Database::insert('users', [
        'name'     => sanitize($name),
        'email'    => $email,
        'password' => hash_password($password),
    ]);
    // Send notification to admin
    create_notification('admin', 1, 'new_user', '👤 New Visitor Registered',
        "$name just created an account.", '/portfolio/admin/users.php');
    return ['success' => true, 'id' => $id];
}


