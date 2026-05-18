<?php

namespace App\Models;

use PDO;
use PDOException;

class SearchModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function search(string $query, ?string $type = null, int $page = 1, int $perPage = 20, string $sort = '', string $dateFilter = ''): array
    {
        $results = [
            'users' => [],
            'posts' => [],
            'hashtags' => []
        ];

        if (strlen(trim($query)) < 2) {
            return $results;
        }

        $searchTerm = '%' . trim($query) . '%';
        $offset = ($page - 1) * $perPage;

        if ($type === null || $type === 'users') {
            $results['users'] = $this->searchUsers($searchTerm, $perPage, $offset, $sort);
        }

        if ($type === null || $type === 'posts') {
            $results['posts'] = $this->searchPosts($searchTerm, $perPage, $offset, $sort, $dateFilter);
        }

        if ($type === null || $type === 'hashtags') {
            $results['hashtags'] = $this->searchHashtags($searchTerm, $perPage, $offset, $sort);
        }

        return $results;
    }

    public function getTotalResults(array $results): int
    {
        return count($results['users']) + count($results['posts']) + count($results['hashtags']);
    }

    public function getTotalCount(string $query, string $type, string $dateFilter = ''): int
    {
        if (strlen(trim($query)) < 2) {
            return 0;
        }
        $searchTerm = '%' . trim($query) . '%';
        switch ($type) {
            case 'users': return $this->countUsers($searchTerm);
            case 'posts': return $this->countPosts($searchTerm, $dateFilter);
            case 'hashtags': return $this->countHashtags($searchTerm);
        }
        return 0;
    }

    private function searchUsers(string $searchTerm, int $limit, int $offset, string $sort): array
    {
        $orderBy = 'full_name ASC';
        if ($sort === 'recent') {
            $orderBy = 'registration_date DESC';
        }

        try {
            $rawTerm = trim(str_replace('%', '', $searchTerm));
            $useFulltext = strlen($rawTerm) >= 4;

            if ($useFulltext) {
                $fulltextTerm = '*' . $rawTerm . '*';
                $stmt = $this->pdo->prepare("
                    SELECT user_id, full_name, profile_picture, biography, role, registration_date
                    FROM users
                    WHERE active = 1
                    AND MATCH(full_name, email) AGAINST(? IN BOOLEAN MODE)
                    ORDER BY {$orderBy}
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$fulltextTerm, $limit, $offset]);
            } else {
                // Fallback to LIKE for short terms
                $likeTerm = '%' . $rawTerm . '%';
                $stmt = $this->pdo->prepare("
                    SELECT user_id, full_name, profile_picture, biography, role, registration_date
                    FROM users
                    WHERE active = 1
                    AND (full_name LIKE ? OR email LIKE ?)
                    ORDER BY {$orderBy}
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$likeTerm, $likeTerm, $limit, $offset]);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchUsers error: " . $e->getMessage());
            return [];
        }
    }

    private function countUsers(string $searchTerm): int
    {
        try {
            $rawTerm = trim(str_replace('%', '', $searchTerm));
            $useFulltext = strlen($rawTerm) >= 4;

            if ($useFulltext) {
                $fulltextTerm = '*' . $rawTerm . '*';
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM users
                    WHERE active = 1 AND MATCH(full_name, email) AGAINST(? IN BOOLEAN MODE)
                ");
                $stmt->execute([$fulltextTerm]);
            } else {
                $likeTerm = '%' . $rawTerm . '%';
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM users
                    WHERE active = 1 AND (full_name LIKE ? OR email LIKE ?)
                ");
                $stmt->execute([$likeTerm, $likeTerm]);
            }
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("countUsers error: " . $e->getMessage());
            return 0;
        }
    }

    private function searchPosts(string $searchTerm, int $limit, int $offset, string $sort, string $dateFilter): array
    {
        $orderBy = 'created_at DESC';
        if ($sort === 'popular') {
            $orderBy = 'like_count DESC, created_at DESC';
        } elseif ($sort === 'comments') {
            $orderBy = 'comment_count DESC, created_at DESC';
        } elseif ($sort === 'relevance') {
            // Use FULLTEXT relevance score when available
            $rawTerm = trim(str_replace('%', '', $searchTerm));
            if (strlen($rawTerm) >= 4) {
                $orderBy = 'MATCH(content) AGAINST(? IN BOOLEAN MODE) DESC, created_at DESC';
            }
        }

        $dateWhere = $this->buildDateFilter($dateFilter, 'created_at');

        try {
            $rawTerm = trim(str_replace('%', '', $searchTerm));
            $useFulltext = strlen($rawTerm) >= 4;

            if ($useFulltext) {
                $fulltextTerm = '*' . $rawTerm . '*';
                $stmt = $this->pdo->prepare("
                    SELECT *
                    FROM v_posts_stats
                    WHERE MATCH(content) AGAINST(? IN BOOLEAN MODE)
                    {$dateWhere}
                    ORDER BY {$orderBy}
                    LIMIT ? OFFSET ?
                ");
                $params = [$fulltextTerm, $limit, $offset];
                if ($sort === 'relevance' && strpos($orderBy, 'MATCH') !== false) {
                    // Re-bind the term for the ORDER BY clause
                    $params = [$fulltextTerm, $fulltextTerm, $limit, $offset];
                }
                $stmt->execute($params);
            } else {
                $likeTerm = '%' . $rawTerm . '%';
                $stmt = $this->pdo->prepare("
                    SELECT *
                    FROM v_posts_stats
                    WHERE content LIKE ?
                    {$dateWhere}
                    ORDER BY {$orderBy}
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$likeTerm, $limit, $offset]);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchPosts error: " . $e->getMessage());
            return [];
        }
    }

    private function countPosts(string $searchTerm, string $dateFilter): int
    {
        $dateWhere = $this->buildDateFilter($dateFilter, 'created_at');
        try {
            $rawTerm = trim(str_replace('%', '', $searchTerm));
            $useFulltext = strlen($rawTerm) >= 4;

            if ($useFulltext) {
                $fulltextTerm = '*' . $rawTerm . '*';
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM v_posts_stats
                    WHERE MATCH(content) AGAINST(? IN BOOLEAN MODE) {$dateWhere}
                ");
                $stmt->execute([$fulltextTerm]);
            } else {
                $likeTerm = '%' . $rawTerm . '%';
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM v_posts_stats
                    WHERE content LIKE ? {$dateWhere}
                ");
                $stmt->execute([$likeTerm]);
            }
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("countPosts error: " . $e->getMessage());
            return 0;
        }
    }

    private function searchHashtags(string $searchTerm, int $limit, int $offset, string $sort): array
    {
        $orderBy = 'post_count DESC';
        if ($sort === 'name') {
            $orderBy = 'h.name ASC';
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT h.name, COUNT(DISTINCT ph.post_id) as post_count
                FROM hashtags h
                JOIN post_hashtags ph ON h.hashtag_id = ph.hashtag_id
                JOIN posts p ON ph.post_id = p.post_id AND p.active = 1
                WHERE h.name LIKE :term
                GROUP BY h.hashtag_id, h.name
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':term', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchHashtags error: " . $e->getMessage());
            return [];
        }
    }

    private function countHashtags(string $searchTerm): int
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT h.hashtag_id)
                FROM hashtags h
                JOIN post_hashtags ph ON h.hashtag_id = ph.hashtag_id
                JOIN posts p ON ph.post_id = p.post_id AND p.active = 1
                WHERE h.name LIKE :term
            ");
            $stmt->execute([':term' => $searchTerm]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("countHashtags error: " . $e->getMessage());
            return 0;
        }
    }

    private function buildDateFilter(string $dateFilter, string $column): string
    {
        if (empty($dateFilter)) {
            return '';
        }
        switch ($dateFilter) {
            case 'hour':
                return "AND {$column} >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            case 'today':
                return "AND DATE({$column}) = CURDATE()";
            case 'week':
                return "AND {$column} >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "AND {$column} >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return '';
        }
    }
}
