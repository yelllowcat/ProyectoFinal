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
}

function requireAdmin()
{
    requireAuth();

    $user = getCurrentUser();
    if (!$user || !isset($user['role']) || $user['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied. Admin privileges required.');
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

    return $_SESSION['user_data'] ?? null;
}

function isAdmin()
{
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function isOwner($resourceUserId)
{
    return getCurrentUserId() == $resourceUserId;
}

function canEdit($resourceUserId)
{
    return isOwner($resourceUserId) || isAdmin();
}

function setUserSession($userData)
{
    $_SESSION['user_id'] = $userData['id_usuario'];
    $_SESSION['user_data'] = [
        'id' => $userData['id_usuario'],
        'username' => $userData['nombre_usuario'],
        'email' => $userData['correo_electronico'],
        'name' => $userData['nombre_completo'] ?? '',
        'profile_picture' => $userData['foto_perfil'] ?? '',
        'role' => $userData['rol'] ?? 'user',
        'created_at' => $userData['fecha_registro'] ?? null
    ];

    session_regenerate_id(true);
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

function jsonSuccess($data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function jsonError($message = 'Error', $code = 400) {
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

function clean_input($data) {
    return strip_tags(trim($data));
}

function safe_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}