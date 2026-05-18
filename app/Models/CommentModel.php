<?php

namespace App\Models;

use PDO;
use PDOException;

class CommentModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function createComment($postId, $userId, $content)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_create_comment(?, ?, ?)");
            $stmt->execute([$postId, $userId, $content]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['comment_id'] ?? false;
            
        } catch (PDOException $e) {
            error_log("createComment error: " . $e->getMessage());
            return false;
        }
    }

    public function getCommentsByPost($postId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_comments_by_post(?)");
            $stmt->execute([$postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("getCommentsByPost error: " . $e->getMessage());
            return [];
        }
    }

    public function deleteComment($commentId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_delete_comment(?, ?)");
            $stmt->execute([$commentId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['affected_rows'] ?? 0) > 0;
            
        } catch (PDOException $e) {
            error_log("deleteComment error: " . $e->getMessage());
            return false;
        }
    }

    public function getCommentCount($postId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_comment_count(?)");
            $stmt->execute([$postId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['comment_count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("getCommentCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function getCommentById($commentId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_comment_by_id(?)");
            $stmt->execute([$commentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("getCommentById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk fetch the first N comments for multiple posts in a single query.
     * Reduces N+1 comment queries to 1.
     */
    public function getFirstCommentsForPosts(array $postIds, int $limitPerPost = 3): array
    {
        if (empty($postIds)) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($postIds), '?'));
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.full_name, u.profile_picture,
                    (SELECT COUNT(*) FROM replies r WHERE r.comment_id = c.comment_id AND r.active = 1) as reply_count
                FROM comments c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.post_id IN ($placeholders) AND c.active = 1
                ORDER BY c.post_id, c.created_at ASC
            ");
            $stmt->execute($postIds);
            $allComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by post_id and limit per post in PHP (MySQL 5.7 compatible)
            $grouped = [];
            $counts = [];
            foreach ($allComments as $row) {
                $pid = $row['post_id'];
                if (!isset($counts[$pid])) {
                    $counts[$pid] = 0;
                }
                if ($counts[$pid] < $limitPerPost) {
                    $grouped[$pid][] = $row;
                    $counts[$pid]++;
                }
            }
            return $grouped;
        } catch (PDOException $e) {
            error_log("getFirstCommentsForPosts error: " . $e->getMessage());
            return [];
        }
    }
}