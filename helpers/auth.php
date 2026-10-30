<?php

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth($redirectTo = '/login')
{
    if (!isLoggedIn()) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        redirect($redirectTo);
    }

    if (isset($_SESSION['user_active']) && (int) $_SESSION['user_active'] === 0) {
        clearUserSession();
        $_SESSION['error'] = 'Tu cuenta ha sido suspendida. Contacta al administrador.';
        redirect('/login');
    }
}

function requireAdmin()
{
    requireAuth();

    $user = getCurrentUser();
    if (!$user || !isset($user['user_role']) || $user['user_role'] !== 'admin') {
        http_response_code(403);
        die('Access denied. Admin privileges required.');
    }

    if (
        ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
        && empty($_SESSION['flash'])
        && !isset($_SESSION['error'])
        && !isset($_SESSION['success'])
    ) {
        session_write_close();
    }
}

function requireUser()
{
    requireAuth();

    if (!isRegularUser() && !isAdmin()) {
        http_response_code(403);
        die('Access denied. User privileges required.');
    }

    if (
        ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
        && empty($_SESSION['flash'])
        && !isset($_SESSION['error'])
        && !isset($_SESSION['success'])
    ) {
        session_write_close();
    }
}

function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return $_SESSION ?? null;
}

function isAdmin()
{
    $user = getCurrentUser();
    return $user && isset($user['user_role']) && $user['user_role'] === 'admin';
}

function isRegularUser()
{
    $user = getCurrentUser();
    $nonAdminRoles = ['user', 'teacher', 'student'];
    return $user && isset($user['user_role']) && in_array($user['user_role'], $nonAdminRoles, true);
}

function isUser()
{
    return isRegularUser();
}
function isOwner($resourceUserId)
{
    return getCurrentUserId() == $resourceUserId;
}

function canEdit($resourceUserId)
{
    return isOwner($resourceUserId) || isAdmin();
}



function clearUserSession()
{
    $_SESSION = [];

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    session_destroy();
}

function getIntendedUrl($default = '/posts')
{
    $url = $_SESSION['intended_url'] ?? $default;
    unset($_SESSION['intended_url']);
    return $url;
}

function redirect($path)
{
    header('Location: ' . $path);
    exit();
}

function redirectBack($default = '/')
{
    $referrer = $_SERVER['HTTP_REFERER'] ?? $default;
    redirect($referrer);
}

function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function jsonSuccess($data = null, $message = '')
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function jsonError($message = 'Error', $code = 400)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

function flash($type, $message)
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function hasFlash()
{
    return !empty($_SESSION['flash']);
}

function clean_input($data)
{
    return strip_tags(trim($data));
}

function safe_output($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function getProfilePicture($filename)
{
    static $cache = [];
    static $defaultImage = null;

    $key = (string) ($filename ?? '');
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? (__DIR__ . '/..');

    if ($defaultImage === null) {
        $defaultPath = $docRoot . "/assets/imagesProfile/default_avatar.png";
        $defaultVersion = file_exists($defaultPath) ? filemtime($defaultPath) : 1;
        $defaultImage = "/assets/imagesProfile/default_avatar.png?v=" . $defaultVersion;
    }

    if ($key === '') {
        return $cache[$key] = $defaultImage;
    }

    $imagePath = $docRoot . "/assets/imagesProfile/{$key}";
    if (!file_exists($imagePath)) {
        return $cache[$key] = $defaultImage;
    }

    return $cache[$key] = "/assets/imagesProfile/{$key}?v=" . filemtime($imagePath);
}


function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function validateCsrfToken(?string $token = null): bool
{
    $token = $token ?? ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate password strength: minimum 8 characters, at least 1 uppercase letter, at least 1 number.
 *
 * @param string $password
 * @return string|null Error message if invalid, or null if valid.
 */
function validatePasswordStrength(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'La contraseña debe tener al menos 8 caracteres.';
    }
    if (strlen($password) > 64) {
        return 'La contraseña no puede exceder los 64 caracteres.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'La contraseña debe contener al menos una letra mayúscula.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'La contraseña debe contener al menos un número.';
    }
    return null;
}
