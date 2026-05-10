<?php

namespace App\Models;

use PDO;
use PDOException;

class ReplyModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function createReply($commentId, $userId, $content)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_create_reply(?, ?, ?)");
            $stmt->execute([$commentId, $userId, $content]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['reply_id'] ?? false;

        } catch (PDOException $e) {
            error_log("createReply error: " . $e->getMessage());
            return false;
        }
    }

    public function getRepliesByComment($commentId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_replies_by_comment(?)");
            $stmt->execute([$commentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getRepliesByComment error: " . $e->getMessage());
            return [];
        }
    }

    public function deleteReply($replyId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_delete_reply(?, ?)");
            $stmt->execute([$replyId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['affected_rows'] ?? 0) > 0;

        } catch (PDOException $e) {
            error_log("deleteReply error: " . $e->getMessage());
            return false;
        }
    }

    public function getReplyCount($commentId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_reply_count(?)");
            $stmt->execute([$commentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['reply_count'] ?? 0;

        } catch (PDOException $e) {
            error_log("getReplyCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function getReplyById($replyId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_reply_by_id(?)");
            $stmt->execute([$replyId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getReplyById error: " . $e->getMessage());
            return false;
        }
    }
}
