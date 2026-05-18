<?php

namespace App\Controllers;

use App\Models\SearchModel;
use App\Models\HashtagModel;
use App\Models\FriendModel;
use App\Models\UserModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Components\Post;

class SearchController
{
    public function index()
    {
        requireAuth();

        $query = clean_input($_GET['q'] ?? '');
        $type = clean_input($_GET['type'] ?? '');

        // Validate type
        $validTypes = ['', 'users', 'posts', 'hashtags'];
        if (!in_array($type, $validTypes, true)) {
            $type = '';
        }

        $searchModel = new SearchModel();
        $hashtagModel = new HashtagModel();

        // Only perform search if query is present and valid
        $results = [
            'users' => [],
            'posts' => [],
            'hashtags' => []
        ];
        $totalResults = 0;
        $friendModel = new FriendModel();
        $currentUserId = getCurrentUserId();

        $friendshipMap = [];
        $likedMap = [];

        if (!empty($query) && strlen($query) >= 2) {
            $results = $searchModel->search($query, $type ?: null);
            $totalResults = $searchModel->getTotalResults($results);

            // Bulk enrich users with friendship status (eliminates N+1)
            $userIds = array_column($results['users'], 'user_id');
            $friendshipMap = $friendModel->bulkFriendshipStatus($currentUserId, $userIds);
            foreach ($results['users'] as &$user) {
                $user['friendship_status'] = $friendshipMap[$user['user_id']] ?? 'none';
            }
            unset($user);

            // Bulk pre-fetch like status for posts (eliminates N+1)
            $postIds = array_column($results['posts'], 'post_id');
            $likeModel = new LikeModel();
            $likedMap = $likeModel->bulkHasLiked($postIds, $currentUserId);
        }

        // Always fetch trending hashtags for the widget
        $trendingHashtags = $hashtagModel->getTrendingHashtags(8);

        // Pass data to the view
        require __DIR__ . '/../../views/search.php';
    }

    public function apiSearch()
    {
        requireAuth();
        header('Content-Type: application/json');

        $query = clean_input($_GET['q'] ?? '');
        $type = clean_input($_GET['type'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $sort = clean_input($_GET['sort'] ?? '');
        $dateFilter = clean_input($_GET['date'] ?? '');

        $validTypes = ['users', 'posts', 'hashtags'];
        if (!in_array($type, $validTypes, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid type']);
            return;
        }

        if (empty($query) || strlen($query) < 2) {
            echo json_encode([
                'results' => [],
                'total' => 0,
                'page' => $page,
                'has_more' => false
            ]);
            return;
        }

        $perPage = 20;
        $searchModel = new SearchModel();
        $friendModel = new FriendModel();
        $currentUserId = getCurrentUserId();

        $results = $searchModel->search($query, $type, $page, $perPage, $sort, $dateFilter);
        $total = $searchModel->getTotalCount($query, $type, $dateFilter);
        $hasMore = ($page * $perPage) < $total;

        // Bulk enrich user results with friendship status (eliminates N+1)
        if ($type === 'users') {
            $userIds = array_column($results['users'], 'user_id');
            $friendshipMap = $friendModel->bulkFriendshipStatus($currentUserId, $userIds);
            foreach ($results['users'] as &$user) {
                $user['friendship_status'] = $friendshipMap[$user['user_id']] ?? 'none';
            }
            unset($user);
        }

        // Render posts HTML for JSON using pre-fetched bulk data
        if ($type === 'posts') {
            $likeModel = new LikeModel();
            $postIds = array_column($results['posts'], 'post_id');
            $likedMap = $likeModel->bulkHasLiked($postIds, $currentUserId);

            $postsHtml = [];
            foreach ($results['posts'] as $postData) {
                $hasLiked = $likedMap[$postData['post_id']] ?? false;
                $likesCount = $postData['likes_count'] ?? 0;
                $commentsCount = $postData['comments_count'] ?? 0;
                $authorPicture = getProfilePicture($postData['author_picture'] ?? '');

                $postComponent = new Post([
                    'id' => $postData['post_id'],
                    'author' => $postData['author_name'] ?? 'Usuario',
                    'author_role' => $postData['author_role'] ?? 'user',
                    'date' => date('d/m/Y', strtotime($postData['created_at'])),
                    'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                    'image_alt' => 'Imagen del post',
                    'text' => $postData['content'],
                    'likes' => $likesCount,
                    'comments_count' => $commentsCount,
                    'comments' => [],
                    'user_id' => $postData['user_id'],
                    'current_user_id' => $currentUserId,
                    'has_liked' => $hasLiked,
                    'user_avatar' => $authorPicture,
                    'highlight_term' => $query
                ]);
                $postsHtml[] = $postComponent->render();
            }
            $results = ['posts_html' => $postsHtml];
        }

        echo json_encode([
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'has_more' => $hasMore
        ]);
    }
}
