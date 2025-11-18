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
            $stmt = $this->pdo->prepare("
                INSERT INTO comments (post_id, user_id, content, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$postId, $userId, $content]);
            
        } catch (PDOException $e) {
            error_log("createComment error: " . $e->getMessage());
            return false;
        }
    }

    public function getCommentsByPost($postId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.full_name, u.profile_picture 
                FROM comments c 
                JOIN users u ON c.user_id = u.user_id 
                WHERE c.post_id = ? AND c.active = 1 
                ORDER BY c.created_at ASC
            ");
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
            $stmt = $this->pdo->prepare("
                UPDATE comments SET active = 0 
                WHERE comment_id = ? AND user_id = ?
            ");
            return $stmt->execute([$commentId, $userId]);
            
        } catch (PDOException $e) {
            error_log("deleteComment error: " . $e->getMessage());
            return false;
        }
    }

    public function getCommentCount($postId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM comments 
                WHERE post_id = ? AND active = 1
            ");
            $stmt->execute([$postId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("getCommentCount error: " . $e->getMessage());
            return 0;
        }
    }
}