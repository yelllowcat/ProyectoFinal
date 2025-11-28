<?php

namespace App\Controllers;

use App\Models\AdminModel;

class AdminController
{
    private $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function getUsersWithMostPosts()
    {
        requireAdmin();

        $users = $this->adminModel->getUsersWithMostPosts();
        jsonSuccess($users);
    }

    public function getUsersWithMostFriends()
    {
        requireAdmin();

        $users = $this->adminModel->getUsersWithMostFriends();
        jsonSuccess($users);
    }

    public function getPostsWithMostComments()
    {
        requireAdmin();

        $posts = $this->adminModel->getPostsWithMostComments();
        jsonSuccess($posts);
    }

    public function getPostsWithMostLikes()
    {
        requireAdmin();

        $posts = $this->adminModel->getPostsWithMostLikes();
        jsonSuccess($posts);
    }
}
