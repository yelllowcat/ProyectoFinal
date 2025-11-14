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

        require __DIR__ . '/../../views/profile.php';
    }


    public function edit()
    {
        requireAuth();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        require __DIR__ . '/../../views/editProfile.php';
    }


}