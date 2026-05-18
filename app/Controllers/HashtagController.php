<?php

namespace App\Controllers;

use App\Models\HashtagModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\UserModel;
use App\Components\Post;

class HashtagController
{
    public function show($tag)
    {
        requireAuth();

        $tag = strtolower(trim($tag));
        if (empty($tag)) {
            flash('error', 'Hashtag no válido');
            redirect('/posts');
        }

        $hashtagModel = new HashtagModel();
        $postsData = $hashtagModel->getPostsByHashtag($tag);
        $postCount = count($postsData);

        $likeModel = new LikeModel();
        $commentModel = new CommentModel();
        $userModel = new UserModel();
        $currentUserId = getCurrentUserId();

        require __DIR__ . '/../../views/hashtag.php';
    }

    public function suggest()
    {
        requireAuth();

        $query = clean_input($_GET['q'] ?? '');

        if (strlen($query) < 1) {
            return jsonSuccess([]);
        }

        $hashtagModel = new HashtagModel();
        $suggestions = $hashtagModel->searchHashtags($query, 8);

        return jsonSuccess($suggestions);
    }
}
