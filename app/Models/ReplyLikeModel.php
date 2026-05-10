<?php

namespace App\Models;

use PDO;
use PDOException;

class ReplyLikeModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function addLike($replyId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_add_reply_like(?, ?)");
            $stmt->execute([$replyId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("addReplyLike error: " . $e->getMessage());
            return false;
        }
    }

    public function removeLike($replyId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_remove_reply_like(?, ?)");
            $stmt->execute([$replyId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("removeReplyLike error: " . $e->getMessage());
            return false;
        }
    }

    public function getLikeCount($replyId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_reply_like_count(?)");
            $stmt->execute([$replyId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['like_count'] ?? 0;

        } catch (PDOException $e) {
            error_log("getReplyLikeCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function hasLiked($replyId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_has_reply_liked(?, ?)");
            $stmt->execute([$replyId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['has_liked'] ?? 0) == 1;

        } catch (PDOException $e) {
            error_log("hasReplyLiked error: " . $e->getMessage());
            return false;
        }
    }
}
