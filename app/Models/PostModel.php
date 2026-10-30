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

    public function getAllPostsPaginated(
        int $page = 1,
        int $perPage = 20,
        string $filter = 'all',
        ?int $currentUserId = null
    ): array {
        try {
            $offset = ($page - 1) * $perPage;

            $whereClause = "WHERE p_sub.active = 1 AND u_sub.active = 1";
            $filterParams = [];

            if ($filter === 'friends' && $currentUserId) {
                $whereClause .= " AND (
                    p_sub.user_id = ? OR
                    p_sub.user_id IN (
                        SELECT user_id1 FROM friends WHERE user_id2 = ?
                        UNION
                        SELECT user_id2 FROM friends WHERE user_id1 = ?
                    )
                )";
                $filterParams[] = $currentUserId;
                $filterParams[] = $currentUserId;
                $filterParams[] = $currentUserId;
            } elseif ($filter === 'teachers') {
                $whereClause .= " AND u_sub.role IN ('teacher', 'admin')";
            }

            $sql = "
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
                    ((SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.active = 1) +
                     (SELECT COUNT(*) FROM replies r INNER JOIN comments cr ON r.comment_id = cr.comment_id WHERE cr.post_id = p.post_id AND r.active = 1 AND cr.active = 1)) AS comments_count
                FROM (
                    SELECT p_sub.post_id
                    FROM posts p_sub
                    INNER JOIN users u_sub ON p_sub.user_id = u_sub.user_id
                    {$whereClause}
                    ORDER BY p_sub.created_at DESC
                    LIMIT ? OFFSET ?
                ) page
                INNER JOIN posts p ON p.post_id = page.post_id
                INNER JOIN users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC
            ";

            $stmt = $this->pdo->prepare($sql);
            $paramIndex = 1;
            foreach ($filterParams as $param) {
                $stmt->bindValue($paramIndex++, $param, PDO::PARAM_INT);
            }
            $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getAllPostsPaginated error: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalPostsCount(string $filter = 'all', ?int $currentUserId = null): int
    {
        try {
            $whereClause = "WHERE p.active = 1 AND u.active = 1";
            $params = [];

            if ($filter === 'friends' && $currentUserId) {
                $whereClause .= " AND (
                    p.user_id = ? OR
                    p.user_id IN (
                        SELECT user_id1 FROM friends WHERE user_id2 = ?
                        UNION
                        SELECT user_id2 FROM friends WHERE user_id1 = ?
                    )
                )";
                $params[] = $currentUserId;
                $params[] = $currentUserId;
                $params[] = $currentUserId;
            } elseif ($filter === 'teachers') {
                $whereClause .= " AND u.role IN ('teacher', 'admin')";
            }

            $stmt = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM posts p
                INNER JOIN users u ON p.user_id = u.user_id
                {$whereClause}
            ");
            $stmt->execute($params);
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
                    ((SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.active = 1) +
                     (SELECT COUNT(*) FROM replies r INNER JOIN comments cr ON r.comment_id = cr.comment_id WHERE cr.post_id = p.post_id AND r.active = 1 AND cr.active = 1)) AS comments_count
                FROM posts p
                INNER JOIN users u ON p.user_id = u.user_id
                WHERE p.post_id = ? AND p.active = 1 AND u.active = 1
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
