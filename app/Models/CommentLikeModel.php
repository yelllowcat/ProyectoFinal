<?php

namespace App\Models;

use PDO;
use PDOException;

class CommentLikeModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function addLike($commentId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_add_comment_like(?, ?)");
            $stmt->execute([$commentId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("addCommentLike error: " . $e->getMessage());
            return false;
        }
    }

    public function removeLike($commentId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_remove_comment_like(?, ?)");
            $stmt->execute([$commentId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("removeCommentLike error: " . $e->getMessage());
            return false;
        }
    }

    public function getLikeCount($commentId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_comment_like_count(?)");
            $stmt->execute([$commentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['like_count'] ?? 0;

        } catch (PDOException $e) {
            error_log("getCommentLikeCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function hasLiked($commentId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_has_comment_liked(?, ?)");
            $stmt->execute([$commentId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['has_liked'] ?? 0) == 1;

        } catch (PDOException $e) {
            error_log("hasCommentLiked error: " . $e->getMessage());
            return false;
        }
    }
}
