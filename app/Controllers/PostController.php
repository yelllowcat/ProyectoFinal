<?php

namespace App\Controllers;

class PostController
{

    public function store()
    {
        requireAuth();

        $content = $_POST['content'] ?? '';
        $userId = $_SESSION['user_id'];

        header('Location: /posts');
        exit();
    }

    public function update($id)
    {
        requireAuth();

        $content = $_POST['content'] ?? '';

        return ['success' => true, 'message' => 'Post updated'];
    }

    public function destroy($id)
    {
        requireAuth();

        return ['success' => true, 'message' => 'Post deleted'];
    }

    public function like($id)
    {
        requireAuth();

        return ['success' => true];
    }

    public function unlike($id)
    {
        requireAuth();

        return ['success' => true];
    }

    public function addComment($postId)
    {
        requireAuth();

        $comment = $_POST['comment'] ?? '';

        return ['success' => true, 'message' => 'Comment added'];
    }

    public function deleteComment($id)
    {
        requireAuth();

        return ['success' => true, 'message' => 'Comment deleted'];
    }
}