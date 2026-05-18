<?php

namespace App\Controllers;

use App\Models\SearchModel;
use App\Models\HashtagModel;

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

        if (!empty($query) && strlen($query) >= 2) {
            $results = $searchModel->search($query, $type ?: null);
            $totalResults = $searchModel->getTotalResults($results);
        }

        // Always fetch trending hashtags for the widget
        $trendingHashtags = $hashtagModel->getTrendingHashtags(8);

        // Pass data to the view
        require __DIR__ . '/../../views/search.php';
    }
}
