<?php

namespace App\Models;

use PDO;
use PDOException;

class SearchModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function search(string $query, ?string $type = null): array
    {
        $results = [
            'users' => [],
            'posts' => [],
            'hashtags' => []
        ];

        if (strlen(trim($query)) < 2) {
            return $results;
        }

        $searchTerm = '%' . trim($query) . '%';

        if ($type === null || $type === 'users') {
            $results['users'] = $this->searchUsers($searchTerm);
        }

        if ($type === null || $type === 'posts') {
            $results['posts'] = $this->searchPosts($searchTerm);
        }

        if ($type === null || $type === 'hashtags') {
            $results['hashtags'] = $this->searchHashtags($searchTerm);
        }

        return $results;
    }

    private function searchUsers(string $searchTerm): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT user_id, full_name, profile_picture, biography, role, registration_date
                FROM users
                WHERE active = 1
                AND (full_name LIKE ? OR email LIKE ?)
                ORDER BY full_name ASC
                LIMIT 20
            ");
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchUsers error: " . $e->getMessage());
            return [];
        }
    }

    private function searchPosts(string $searchTerm): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM v_posts_stats
                WHERE content LIKE :term
                ORDER BY created_at DESC
                LIMIT 20
            ");
            $stmt->execute([':term' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchPosts error: " . $e->getMessage());
            return [];
        }
    }

    private function searchHashtags(string $searchTerm): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT h.name, COUNT(DISTINCT ph.post_id) as post_count
                FROM hashtags h
                JOIN post_hashtags ph ON h.hashtag_id = ph.hashtag_id
                JOIN posts p ON ph.post_id = p.post_id AND p.active = 1
                WHERE h.name LIKE :term
                GROUP BY h.hashtag_id, h.name
                ORDER BY post_count DESC
                LIMIT 20
            ");
            $stmt->execute([':term' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchHashtags error: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalResults(array $results): int
    {
        return count($results['users']) + count($results['posts']) + count($results['hashtags']);
    }
}
