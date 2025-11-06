<?php

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    
    return null;
}

/**
 * Set user session after successful login
 * @param array $userData User data from database
 */
function setUserSession($userData) {
    $_SESSION['user_id'] = $userData['id_usuario'];
    $_SESSION['user_data'] = [
        'id' => $userData['id_usuario'],
        'username' => $userData['nombre_usuario'],
        'email' => $userData['correo_electronico'],
        'name' => $userData['nombre_completo'] ?? '',
        'profile_picture' => $userData['foto_perfil'] ?? ''
    ];
}

/**
 * Redirect to a specific route
 * @param string $path
 */
function redirect($path) {
    header('Location: ' . $path);
    exit();
}

/**
 * Return JSON response
 * @param mixed $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}