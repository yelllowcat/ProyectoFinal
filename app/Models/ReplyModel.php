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

    public function getRepliesByComment($commentId, ?int $userId = null)
    {
        try {
            $currentUserId = $userId ?? 0;
            $stmt = $this->pdo->prepare("
                SELECT r.*, u.full_name, u.profile_picture,
                    (SELECT COUNT(*) FROM reply_likes rl
                     WHERE rl.reply_id = r.reply_id) as likes_count,
                    (SELECT COUNT(*) FROM reply_likes rl
                     WHERE rl.reply_id = r.reply_id AND rl.user_id = ?) as has_liked
                FROM replies r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.comment_id = ? AND r.active = 1
                ORDER BY r.created_at ASC
            ");
            $stmt->execute([$currentUserId, $commentId]);
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

    public function updateReply(int $replyId, int $userId, string $content): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT content FROM replies WHERE reply_id = ? AND user_id = ? AND active = 1"
            );
            $stmt->execute([$replyId, $userId]);
            $oldContent = $stmt->fetchColumn();

            if ($oldContent === false) {
                return false;
            }

            if ($oldContent === $content) {
                return true;
            }

            $this->pdo->beginTransaction();

            $histStmt = $this->pdo->prepare("
                INSERT INTO comment_edit_history
                (comment_type, target_id, user_id, previous_content, new_content, edited_at)
                VALUES ('reply', ?, ?, ?, ?, NOW())
            ");
            $histStmt->execute([$replyId, $userId, $oldContent, $content]);

            $upStmt = $this->pdo->prepare("
                UPDATE replies 
                SET content = ?, updated_at = NOW() 
                WHERE reply_id = ? AND user_id = ?
            ");
            $upStmt->execute([$content, $replyId, $userId]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("updateReply error: " . $e->getMessage());
            return false;
        }
    }

    public function getReplyEditHistory(int $replyId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT history_id, previous_content, new_content, edited_at
                FROM comment_edit_history
                WHERE comment_type = 'reply' AND target_id = ?
                ORDER BY edited_at ASC
            ");
            $stmt->execute([$replyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getReplyEditHistory error: " . $e->getMessage());
            return [];
        }
    }
}
