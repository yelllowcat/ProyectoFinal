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

        if (!$user) {
            flash('error', 'User not found');
            redirect('/posts');
        }

        $user['full_name'] = safe_output($user['full_name'] ?? '');
        $user['biography'] = safe_output($user['biography'] ?? '');
        $user['email'] = safe_output($user['email'] ?? '');

        $posts = [];
        require __DIR__ . '/../../views/profile.php';
    }

    public function edit()
    {
        requireAuth();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        if ($user) {
            $user['full_name'] = safe_output($user['full_name'] ?? '');
            $user['biography'] = safe_output($user['biography'] ?? '');
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

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                $profilePicture = $this->handleProfileImageUpload($_FILES['profile_picture']);
            }

            $safeFullName = safe_output($fullName);
            $safeBiography = safe_output($biography);
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
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($imageFile['type'], $allowedTypes)) {
            flash('error', 'Solo se permiten imágenes JPEG y PNG');
            return null;
        }

        if ($imageFile['size'] > $maxSize) {
            flash('error', 'La imagen no puede ser mayor a 5MB');
            return null;
        }

        $uploadDir = __DIR__ . '/../../assets/imagesProfile/';

        $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $fileName = microtime(true) . '_' . uniqid() . '.' . $extension;
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
                session_destroy();
                exit; 
            } else {
                exit;
            }

        } catch (Exception $e) {
            exit;
        }
    }
}