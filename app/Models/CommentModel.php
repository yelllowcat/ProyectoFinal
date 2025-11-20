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
}