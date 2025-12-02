<?php

namespace App\Models;

use PDO;
use PDOException;

class PostModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function createPost($userId, $content, $image = null)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_create_post(?, ?, ?)");
            $stmt->execute([$userId, $content, $image]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['post_id'] ?? false;

        } catch (PDOException $e) {
            error_log("createPost error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllPosts()
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT *
            FROM v_posts_stats
            ORDER BY created_at DESC
        ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getAllPosts error: " . $e->getMessage());
            return [];
        }
    }


    public function getPostById($postId)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT *
            FROM v_posts_stats
            WHERE post_id = ?
        ");
            $stmt->execute([$postId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getPostById error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePost($postId, $content, $image = null)
    {
        try {
            $stmt = $this->pdo->prepare("
            UPDATE posts 
            SET content = ?, updated_at = NOW() 
            WHERE post_id = ?
        ");
            return $stmt->execute([$content, $postId]);

        } catch (PDOException $e) {
            error_log("updatePost error: " . $e->getMessage());
            return false;
        }
    }

    public function deletePost($postId)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE posts SET active = 0 WHERE post_id = ?
            ");
            return $stmt->execute([$postId]);

        } catch (PDOException $e) {
            error_log("deletePost error: " . $e->getMessage());
            return false;
        }
    }

    public function getPostsByUserId($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT *
            FROM v_posts_stats
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getPostsByUserId error: " . $e->getMessage());
            return [];
        }
    }


    public function getPostsWithCounts()
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT *
            FROM v_posts_stats
            ORDER BY created_at DESC
        ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getPostsWithCounts error: " . $e->getMessage());
            return [];
        }
    }

}