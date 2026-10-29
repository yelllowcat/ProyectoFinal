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

    public function getAllPostsPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $stmt = $this->pdo->prepare("
                SELECT
                    p.post_id,
                    p.user_id,
                    p.content,
                    p.image,
                    p.created_at,
                    p.updated_at,
                    u.full_name AS author_name,
                    u.profile_picture AS author_picture,
                    u.email AS author_email,
                    u.role AS author_role,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS likes_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.active = 1) AS comments_count
                FROM (
                    SELECT post_id FROM posts WHERE active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?
                ) page
                INNER JOIN posts p ON p.post_id = page.post_id
                INNER JOIN users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$perPage, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getAllPostsPaginated error: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalPostsCount(): int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE active = 1");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getTotalPostsCount error: " . $e->getMessage());
            return 0;
        }
    }


    public function getPostById($postId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    p.post_id,
                    p.user_id,
                    p.content,
                    p.image,
                    p.created_at,
                    p.updated_at,
                    u.full_name AS author_name,
                    u.profile_picture AS author_picture,
                    u.email AS author_email,
                    u.role AS author_role,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS likes_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.active = 1) AS comments_count
                FROM posts p
                INNER JOIN users u ON p.user_id = u.user_id
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
                SELECT
                    p.post_id,
                    p.user_id,
                    p.content,
                    p.image,
                    p.created_at,
                    p.updated_at,
                    u.full_name AS author_name,
                    u.profile_picture AS author_picture,
                    u.email AS author_email,
                    u.role AS author_role,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS likes_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.active = 1) AS comments_count
                FROM posts p
                INNER JOIN users u ON p.user_id = u.user_id
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

    public function getPostsByUserIdPaginated(int $userId, int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $stmt = $this->pdo->prepare("
                SELECT
                    p.post_id,
                    p.user_id,
                    p.content,
                    p.image,
                    p.created_at,
                    p.updated_at,
                    u.full_name AS author_name,
                    u.profile_picture AS author_picture,
                    u.email AS author_email,
                    u.role AS author_role,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS likes_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.active = 1) AS comments_count
                FROM (
                    SELECT post_id FROM posts WHERE user_id = ? AND active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?
                ) page
                INNER JOIN posts p ON p.post_id = page.post_id
                INNER JOIN users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId, $perPage, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPostsByUserIdPaginated error: " . $e->getMessage());
            return [];
        }
    }

    public function countPostsByUserId(int $userId): int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ? AND active = 1");
            $stmt->execute([$userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("countPostsByUserId error: " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Alias for getAllPosts for backward compatibility.
     *
     * @return array
     */
    public function getPostsWithCounts(): array
    {
        return $this->getAllPosts();
    }

}
