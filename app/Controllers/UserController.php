<?php

namespace App\Controllers;

use App\Models\UserModel;
use Exception;


class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function show($id)
    {
        requireAuth();

        $user = $this->userModel->getUserById($id);

        if (!$user || ($user['role'] ?? '') === 'admin') {
            flash('error', 'Usuario no encontrado');
            redirect('/posts');
        }

        $user['full_name'] = $user['full_name'] ?? '';
        $user['biography'] = $user['biography'] ?? '';
        $user['email'] = $user['email'] ?? '';

        $posts = [];
        require __DIR__ . '/../../views/profile.php';
    }

    public function edit()
    {
        requireAuth();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        if ($user) {
            $user['full_name'] = $user['full_name'] ?? '';
            $user['biography'] = $user['biography'] ?? '';
        }

        require __DIR__ . '/../../views/editProfile.php';
    }

    public function settings()
    {
        requireAuth();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        require __DIR__ . '/../../views/settings.php';
    }

    public function update()
    {
        requireAuth();

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = clean_input($_POST['full_name'] ?? '');
            $biography = clean_input($_POST['biography'] ?? '');

            if (empty($fullName) || strlen(trim($fullName)) < 1) {
                flash('error', 'El nombre debe tener al menos 1 caracteres');
                redirect('/editProfile');
            }

            $profilePicture = null;
            $oldUser = $this->userModel->getUserById($userId);

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                $profilePicture = $this->handleProfileImageUpload($_FILES['profile_picture']);
                if ($profilePicture && !empty($oldUser['profile_picture']) && $oldUser['profile_picture'] !== 'default_avatar.png') {
                    $oldPath = __DIR__ . '/../../assets/imagesProfile/' . $oldUser['profile_picture'];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }

            $safeFullName = clean_input($fullName);
            $safeBiography = clean_input($biography);
            $department = clean_input($_POST['department'] ?? '');
            $career = clean_input($_POST['career'] ?? '');
            $semester = isset($_POST['semester']) && $_POST['semester'] !== '' ? intval($_POST['semester']) : null;
            if ($oldUser && ($oldUser['role'] ?? '') === 'teacher') {
                $semester = null;
            }

            $result = $this->userModel->updateUser(
                $userId,
                $safeFullName,
                $safeBiography,
                $profilePicture,
                $department ?: null,
                $career ?: null,
                $semester
            );

            if ($result) {
                $_SESSION['user_name'] = $safeFullName;
                flash('success', 'Perfil actualizado correctamente' . ($profilePicture ? ' con nueva foto de perfil' : ''));
                redirect('/profile');
            } else {
                flash('error', 'Error al actualizar el perfil');
                redirect('/editProfile');
            }
        }

        redirect('/editProfile');
    }

    private function handleProfileImageUpload($imageFile)
    {
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp'
        ];
        $maxSize = 5 * 1024 * 1024;

        if ($imageFile['size'] > $maxSize) {
            flash('error', 'La imagen no puede ser mayor a 5MB');
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $imageFile['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!$mime || !isset($allowedMimes[$mime])) {
            flash('error', 'Solo se permiten imagenes JPEG, PNG, GIF y WebP validas');
            return null;
        }

        $uploadDir = __DIR__ . '/../../assets/imagesProfile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = $allowedMimes[$mime];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($imageFile['tmp_name'], $filePath)) {
            return $fileName;
        } else {
            flash('error', 'Error al subir la imagen');
            return null;
        }
    }

        public function changePassword()
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/settings');
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            flash('error', 'Por favor completa todos los campos para cambiar tu contraseña.');
            redirect('/settings');
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'La nueva contraseña y su confirmación no coinciden.');
            redirect('/settings');
        }

        $strengthError = validatePasswordStrength($newPassword);
        if ($strengthError !== null) {
            flash('error', $strengthError);
            redirect('/settings');
        }

        if ($currentPassword === $newPassword) {
            flash('error', 'La nueva contraseña debe ser diferente de la contraseña actual.');
            redirect('/settings');
        }

        $user = $this->userModel->getUserWithPasswordById($userId);
        if (!$user) {
            flash('error', 'Usuario no encontrado.');
            redirect('/settings');
        }

        if (!password_verify($currentPassword, $user['password'])) {
            flash('error', 'La contraseña actual es incorrecta.');
            redirect('/settings');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = $this->userModel->updatePassword($userId, $hashedPassword);

        if ($updated) {
            flash('success', 'Contraseña actualizada exitosamente.');
        } else {
            flash('error', 'No se pudo actualizar la contraseña. Por favor intenta más tarde.');
        }

        redirect('/settings');
    }

    public function deleteAccount()
    {
        requireAuth();

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method !== 'DELETE' && $method !== 'POST') {
            jsonError('Método no permitido.', 405);
            return;
        }

        try {
            if (!isset($_SESSION['user_id'])) {
                jsonError('Sesión no válida o expirada.', 401);
                return;
            }

            $userId = $_SESSION['user_id'];
            $user = $this->userModel->getUserWithPasswordById($userId);

            if (!$user) {
                jsonError('Usuario no encontrado.', 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $password = $input['password'] ?? ($_POST['password'] ?? '');

            if (empty($password)) {
                jsonError('Debes ingresar tu contraseña para confirmar la eliminación de la cuenta.', 400);
                return;
            }

            if (!password_verify($password, $user['password'])) {
                jsonError('Contraseña incorrecta. No se pudo eliminar la cuenta.', 403);
                return;
            }

            $result = $this->userModel->deleteUser($userId);

            if ($result) {
                if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default_avatar.png') {
                    $avatarPath = __DIR__ . '/../../assets/imagesProfile/' . $user['profile_picture'];
                    if (file_exists($avatarPath)) {
                        @unlink($avatarPath);
                    }
                }
                clearUserSession();
                jsonSuccess(['redirect' => '/login'], 'Cuenta eliminada exitosamente.');
                return;
            } else {
                jsonError('Error al eliminar la cuenta de la base de datos.', 500);
                return;
            }

        } catch (Exception $e) {
            error_log("deleteAccount error: " . $e->getMessage());
            jsonError('Ocurrió un error inesperado al eliminar la cuenta.', 500);
            return;
        }
    }
}
