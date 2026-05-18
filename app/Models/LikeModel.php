<?php

namespace App\Models;

use PDO;
use PDOException;

class LikeModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function addLike($postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_add_like(?, ?)");
            $stmt->execute([$postId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("addLike error: " . $e->getMessage());
            return false;
        }
    }

    public function removeLike($postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_remove_like(?, ?)");
            $stmt->execute([$postId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("removeLike error: " . $e->getMessage());
            return false;
        }
    }

    public function getLikeCount($postId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_like_count(?)");
            $stmt->execute([$postId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['like_count'] ?? 0;

        } catch (PDOException $e) {
            error_log("getLikeCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function hasLiked($postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_has_liked(?, ?)");
            $stmt->execute([$postId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['has_liked'] ?? 0) == 1;

        } catch (PDOException $e) {
            error_log("hasLiked error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserLikes($userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_user_likes(?)");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getUserLikes error: " . $e->getMessage());
            return [];
        }
    }

    public function getPostLikers($postId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_post_likers(?)");
            $stmt->execute([$postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getPostLikers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total likes count across all active posts by a specific user.
     */
    public function getTotalLikesForUser(int $userId): int
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM likes l
                JOIN posts p ON l.post_id = p.post_id
                WHERE p.user_id = ? AND p.active = 1
            ");
            $stmt->execute([$userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getTotalLikesForUser error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Bulk check if user has liked multiple posts in a single query.
     * Reduces N+1 queries to 1.
     */
    public function bulkHasLiked(array $postIds, int $userId): array
    {
        if (empty($postIds)) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($postIds), '?'));
            $stmt = $this->pdo->prepare("
                SELECT post_id FROM likes
                WHERE user_id = ? AND post_id IN ($placeholders)
            ");
            $stmt->execute(array_merge([$userId], $postIds));

            $liked = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $liked[$row['post_id']] = true;
            }
            return $liked;
        } catch (PDOException $e) {
            error_log("bulkHasLiked error: " . $e->getMessage());
            return [];
        }
    }
}