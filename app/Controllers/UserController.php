<?php

namespace App\Controllers;

use App\Models\UserModel;

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

        $posts = [];
        require __DIR__ . '/../../views/profile.php';
    }


    public function edit()
    {
        requireAuth();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        require __DIR__ . '/../../views/editProfile.php';
    }

    public function update()
    {
        requireAuth();

        $userId = getCurrentUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $full_name = clean_input($_POST['full_name'] ?? '');
            $biography = clean_input($_POST['biography'] ?? '');

            if (empty($full_name)) {
                flash('error', 'El nombre es obligatorio');
                redirect('/editProfile');
            }

            $result = $this->userModel->updateUser($userId, [
                'full_name' => $full_name,
                'biography' => $biography
            ]);

            if ($result) {
                flash('success', 'Perfil actualizado correctamente');
                redirect('/profile');
            } else {
                flash('error', 'Error al actualizar el perfil');
                redirect('/editProfile');
            }
        }

        redirect('/editProfile');
    }

}