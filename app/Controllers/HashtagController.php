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
        $currentUserId = getCurrentUserId();

        // Pagination for hashtag posts
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $postsData = $hashtagModel->getPostsByHashtagPaginated($tag, $page, $perPage);
        $postCount = $hashtagModel->countPostsByHashtag($tag);
        $totalPages = (int) ceil($postCount / $perPage);

        // Bulk fetch data for all posts (eliminates N+1 queries)
        $postIds = array_column($postsData, 'post_id');
        $likeModel = new LikeModel();
        $commentModel = new CommentModel();
        $likedMap = $likeModel->bulkHasLiked($postIds, $currentUserId);
        $commentsMap = $commentModel->getFirstCommentsForPosts($postIds, 3);

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
