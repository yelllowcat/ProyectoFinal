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
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO likes (post_id, user_id, liked_at) 
                VALUES (?, ?, NOW())
            ");
            return $stmt->execute([$postId, $userId]);
            
        } catch (PDOException $e) {
            error_log("addLike error: " . $e->getMessage());
            return false;
        }
    }

    public function removeLike($postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM likes WHERE post_id = ? AND user_id = ?
            ");
            return $stmt->execute([$postId, $userId]);
            
        } catch (PDOException $e) {
            error_log("removeLike error: " . $e->getMessage());
            return false;
        }
    }

    public function getLikeCount($postId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM likes 
                WHERE post_id = ?
            ");
            $stmt->execute([$postId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("getLikeCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function hasLiked($postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 1 FROM likes 
                WHERE post_id = ? AND user_id = ?
            ");
            $stmt->execute([$postId, $userId]);
            return $stmt->fetch() !== false;
            
        } catch (PDOException $e) {
            error_log("hasLiked error: " . $e->getMessage());
            return false;
        }
    }
}