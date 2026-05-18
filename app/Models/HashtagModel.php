<?php

namespace App\Models;

use PDO;
use PDOException;

class HashtagModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public static function extractHashtags(string $content): array
    {
        preg_match_all('/#(\w{1,50})/', $content, $matches);
        $hashtags = array_map('strtolower', $matches[1]);
        return array_values(array_unique($hashtags));
    }

    public function linkPostHashtags(int $postId, array $hashtagNames): void
    {
        if (empty($hashtagNames)) {
            return;
        }

        try {
            $this->pdo->beginTransaction();

            foreach ($hashtagNames as $name) {
                $name = strtolower(trim($name));
                if (empty($name)) {
                    continue;
                }

                // Get or create hashtag
                $stmt = $this->pdo->prepare("CALL sp_get_or_create_hashtag(:name)");
                $stmt->execute([':name' => $name]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $hashtagId = $result['hashtag_id'] ?? null;
                $stmt->closeCursor();

                if ($hashtagId) {
                    // Link to post
                    $stmt = $this->pdo->prepare("CALL sp_link_post_hashtag(:post_id, :hashtag_id)");
                    $stmt->execute([':post_id' => $postId, ':hashtag_id' => $hashtagId]);
                    $stmt->closeCursor();
                }
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("linkPostHashtags error: " . $e->getMessage());
        }
    }

    public function unlinkPostHashtags(int $postId): void
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_unlink_post_hashtags(:post_id)");
            $stmt->execute([':post_id' => $postId]);
        } catch (PDOException $e) {
            error_log("unlinkPostHashtags error: " . $e->getMessage());
        }
    }

    public function getHashtagsForPost(int $postId): array
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_hashtags_for_post(:post_id)");
            $stmt->execute([':post_id' => $postId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_column($results, 'name');
        } catch (PDOException $e) {
            error_log("getHashtagsForPost error: " . $e->getMessage());
            return [];
        }
    }

    public function getHashtagsForPosts(array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($postIds), '?'));
            $stmt = $this->pdo->prepare("
                SELECT ph.post_id, h.name
                FROM hashtags h
                JOIN post_hashtags ph ON h.hashtag_id = ph.hashtag_id
                WHERE ph.post_id IN ({$placeholders})
                ORDER BY h.name ASC
            ");
            $stmt->execute($postIds);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped = [];
            foreach ($results as $row) {
                $grouped[$row['post_id']][] = $row['name'];
            }
            return $grouped;
        } catch (PDOException $e) {
            error_log("getHashtagsForPosts error: " . $e->getMessage());
            return [];
        }
    }

    public function getPostsByHashtag(string $name): array
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_posts_by_hashtag(:name)");
            $stmt->execute([':name' => strtolower(trim($name))]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPostsByHashtag error: " . $e->getMessage());
            return [];
        }
    }

    public function getTrendingHashtags(int $limit = 10): array
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_trending_hashtags(:limit)");
            $stmt->execute([':limit' => $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getTrendingHashtags error: " . $e->getMessage());
            return [];
        }
    }

    public function searchHashtags(string $query, int $limit = 5): array
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
                LIMIT :limit
            ");
            $stmt->execute([':term' => '%' . strtolower(trim($query)) . '%', ':limit' => $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchHashtags error: " . $e->getMessage());
            return [];
        }
    }
}
