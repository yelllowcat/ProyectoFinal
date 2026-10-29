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
            $result = $this->userModel->updateUser(
                $userId,
                $safeFullName,
                $safeBiography,
                $profilePicture
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

    public function deleteAccount()
    {
        requireAuth();

        try {

            if (!isset($_SESSION['user_id'])) {
                exit;
            }

            $userId = $_SESSION['user_id'];
            $userModel = new UserModel();

            $user = $userModel->getUserById($userId);

            if (!$user) {
                exit;
            }
            
            $result = $userModel->deleteUser($userId);

            if ($result) {
                if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default_avatar.png') {
                    $avatarPath = __DIR__ . '/../../assets/imagesProfile/' . $user['profile_picture'];
                    if (file_exists($avatarPath)) {
                        @unlink($avatarPath);
                    }
                }
                clearUserSession();
                jsonSuccess(null, 'Cuenta eliminada exitosamente');
                exit; 
            } else {
                jsonError('Error al eliminar la cuenta');
                exit;
            }

        } catch (Exception $e) {
            exit;
        }
    }
}
