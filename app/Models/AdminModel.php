<?php

namespace App\Models;

use PDO;
use PDOException;

class AdminModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function getUsersWithMostPosts($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.user_id,
                    u.full_name,
                    u.email,
                    u.profile_picture,
                    COUNT(p.post_id) as post_count
                FROM users u
                LEFT JOIN posts p ON u.user_id = p.user_id AND p.active = 1
                WHERE u.active = 1
                GROUP BY u.user_id, u.full_name, u.email, u.profile_picture
                ORDER BY post_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getUsersWithMostPosts error: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersWithMostFriends($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.user_id,
                    u.full_name,
                    u.email,
                    u.profile_picture,
                    (
                        SELECT COUNT(*) 
                        FROM friends f 
                        WHERE f.user_id1 = u.user_id OR f.user_id2 = u.user_id
                    ) as friend_count
                FROM users u
                WHERE u.active = 1
                ORDER BY friend_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getUsersWithMostFriends error: " . $e->getMessage());
            return [];
        }
    }

    public function getPostsWithMostComments($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.post_id,
                    p.content,
                    p.created_at,
                    u.user_id,
                    u.full_name as author_name,
                    u.email as author_email,
                    u.profile_picture as author_picture,
                    COUNT(c.comment_id) as comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN comments c ON p.post_id = c.post_id AND c.active = 1
                WHERE p.active = 1
                GROUP BY p.post_id, p.content, p.created_at, u.user_id, u.full_name, u.email, u.profile_picture
                ORDER BY comment_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPostsWithMostComments error: " . $e->getMessage());
            return [];
        }
    }

    public function getPostsWithMostLikes($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.post_id,
                    p.content,
                    p.created_at,
                    u.user_id,
                    u.full_name as author_name,
                    u.email as author_email,
                    u.profile_picture as author_picture,
                    COUNT(l.like_id) as like_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN likes l ON p.post_id = l.post_id
                WHERE p.active = 1
                GROUP BY p.post_id, p.content, p.created_at, u.user_id, u.full_name, u.email, u.profile_picture
                ORDER BY like_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPostsWithMostLikes error: " . $e->getMessage());
            return [];
        }
    }
}
