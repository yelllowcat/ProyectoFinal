<?php

namespace App\Models;

use PDO;
use PDOException;

class PostModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createPost($userId, $content, $image = null)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, content, image, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");

            return $stmt->execute([$userId, $content, $image]);

        } catch (PDOException $e) {
            error_log("createPost error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllPosts()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, u.full_name, u.profile_picture 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.active = 1 
                ORDER BY p.created_at DESC
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
                SELECT p.*, u.full_name, u.profile_picture 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.post_id = ? AND p.active = 1
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
            SELECT p.*, u.full_name, u.profile_picture 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            WHERE p.user_id = ? AND p.active = 1 
            ORDER BY p.created_at DESC
        ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("getPostsByUserId error: " . $e->getMessage());
            return [];
        }
    }
}