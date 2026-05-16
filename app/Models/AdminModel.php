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

    public function getSummaryStats()
    {
        try {
            $stats = [];
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users WHERE active = 1 AND role != 'admin'");
            $stats['total_users'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM posts WHERE active = 1");
            $stats['total_posts'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM comments WHERE active = 1");
            $stmt2 = $this->pdo->query("SELECT COUNT(*) as total FROM replies WHERE active = 1");
            $stats['total_comments'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'] + (int)$stmt2->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM likes");
            $stats['total_likes'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM friends");
            $stats['total_friendships'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("getSummaryStats error: " . $e->getMessage());
            return [];
        }
    }

    public function getActivityTimeline()
    {
        try {
            // Posts per day (last 30 days)
            $stmtPosts = $this->pdo->query("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM posts WHERE active = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at) ORDER BY date
            ");
            $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

            // Comments per day (last 30 days)
            $stmtComments = $this->pdo->query("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM comments WHERE active = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at) ORDER BY date
            ");
            $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

            // Likes per day (last 30 days)
            $stmtLikes = $this->pdo->query("
                SELECT DATE(liked_at) as date, COUNT(*) as count
                FROM likes WHERE liked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(liked_at) ORDER BY date
            ");
            $likes = $stmtLikes->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data for chart
            $allDates = [];
            foreach ($posts as $row) { $allDates[$row['date']] = true; }
            foreach ($comments as $row) { $allDates[$row['date']] = true; }
            foreach ($likes as $row) { $allDates[$row['date']] = true; }
            
            $labels = array_keys($allDates);
            sort($labels);
            
            $postsData = [];
            $commentsData = [];
            $likesData = [];
            
            foreach ($labels as $date) {
                $postCount = 0;
                foreach ($posts as $row) { if ($row['date'] === $date) { $postCount = (int)$row['count']; break; } }
                $postsData[] = $postCount;
                
                $commentCount = 0;
                foreach ($comments as $row) { if ($row['date'] === $date) { $commentCount = (int)$row['count']; break; } }
                $commentsData[] = $commentCount;
                
                $likeCount = 0;
                foreach ($likes as $row) { if ($row['date'] === $date) { $likeCount = (int)$row['count']; break; } }
                $likesData[] = $likeCount;
            }

            return [
                'labels' => $labels,
                'posts' => $postsData,
                'comments' => $commentsData,
                'likes' => $likesData
            ];
        } catch (PDOException $e) {
            error_log("getActivityTimeline error: " . $e->getMessage());
            return ['labels' => [], 'posts' => [], 'comments' => [], 'likes' => []];
        }
    }

    public function getEngagementBreakdown()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                  (SELECT COUNT(*) FROM likes) as post_likes,
                  (SELECT COUNT(*) FROM comments WHERE active = 1) as comments,
                  (SELECT COUNT(*) FROM replies WHERE active = 1) as replies,
                  (SELECT COUNT(*) FROM comment_likes) as comment_likes,
                  (SELECT COUNT(*) FROM reply_likes) as reply_likes
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return array_map('intval', $row);
            }
            return [];
        } catch (PDOException $e) {
            error_log("getEngagementBreakdown error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserGrowth()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                  YEARWEEK(registration_date, 1) as week,
                  MIN(DATE(registration_date)) as week_start,
                  COUNT(*) as count
                FROM users 
                WHERE active = 1 AND role != 'admin'
                  AND registration_date >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
                GROUP BY YEARWEEK(registration_date, 1)
                ORDER BY week
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $labels = [];
            $counts = [];
            foreach ($rows as $row) {
                $dateObj = new \DateTime($row['week_start']);
                $labels[] = 'Sem ' . $dateObj->format('d/m');
                $counts[] = (int)$row['count'];
            }
            
            return [
                'labels' => $labels,
                'counts' => $counts
            ];
        } catch (PDOException $e) {
            error_log("getUserGrowth error: " . $e->getMessage());
            return ['labels' => [], 'counts' => []];
        }
    }

    public function getUserActivitySplit()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                  (SELECT COUNT(*) FROM users WHERE active = 1 AND role != 'admin' 
                   AND user_id IN (SELECT DISTINCT user_id FROM posts WHERE active = 1)) as active,
                  (SELECT COUNT(*) FROM users WHERE active = 1 AND role != 'admin' 
                   AND user_id NOT IN (SELECT DISTINCT user_id FROM posts WHERE active = 1)) as inactive
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? array_map('intval', $row) : ['active' => 0, 'inactive' => 0];
        } catch (PDOException $e) {
            error_log("getUserActivitySplit error: " . $e->getMessage());
            return ['active' => 0, 'inactive' => 0];
        }
    }

    public function getPostsByDayOfWeek()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DAYOFWEEK(created_at) as dow, COUNT(*) as count
                FROM posts WHERE active = 1
                GROUP BY DAYOFWEEK(created_at)
                ORDER BY dow
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $data = [];
            foreach ($rows as $row) {
                $data[(int)$row['dow']] = (int)$row['count'];
            }

            $labels = [];
            $counts = [];
            for ($i = 2; $i <= 7; $i++) {
                $labels[] = $dayNames[$i - 1];
                $counts[] = $data[$i] ?? 0;
            }
            $labels[] = $dayNames[0];
            $counts[] = $data[1] ?? 0;

            return ['labels' => $labels, 'counts' => $counts];
        } catch (PDOException $e) {
            error_log("getPostsByDayOfWeek error: " . $e->getMessage());
            return ['labels' => ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'], 'counts' => [0,0,0,0,0,0,0]];
        }
    }

    public function getPostImageRatio()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                  (SELECT COUNT(*) FROM posts WHERE active = 1 AND image IS NOT NULL AND image != '') as with_image,
                  (SELECT COUNT(*) FROM posts WHERE active = 1 AND (image IS NULL OR image = '')) as text_only
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? array_map('intval', $row) : ['with_image' => 0, 'text_only' => 0];
        } catch (PDOException $e) {
            error_log("getPostImageRatio error: " . $e->getMessage());
            return ['with_image' => 0, 'text_only' => 0];
        }
    }

    public function getPeakUsageHeatmap($range = '30')
    {
        try {
            $sql = "
                SELECT DAYOFWEEK(ts) AS dow, HOUR(ts) AS hr, COUNT(*) AS cnt
                FROM (
                    SELECT created_at AS ts FROM posts WHERE active = 1
                    UNION ALL SELECT created_at FROM comments WHERE active = 1
                    UNION ALL SELECT created_at FROM replies WHERE active = 1
                    UNION ALL SELECT liked_at FROM likes
                    UNION ALL SELECT liked_at FROM comment_likes
                    UNION ALL SELECT liked_at FROM reply_likes
                    UNION ALL SELECT friendship_date FROM friends
                    UNION ALL SELECT registration_date FROM users WHERE active = 1
                ) AS all_activity
            ";

            if ($range !== 'all') {
                $sql .= " WHERE ts >= DATE_SUB(NOW(), INTERVAL ? DAY)";
                $days = (int)$range;
            }

            $sql .= " GROUP BY dow, hr";

            $stmt = $this->pdo->prepare($sql);
            if ($range !== 'all') {
                $stmt->execute([$days]);
            } else {
                $stmt->execute();
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            for ($r = 0; $r < 7; $r++) {
                $data[$r] = array_fill(0, 24, 0);
            }

            $max = 0;
            foreach ($rows as $row) {
                $dow = (int)$row['dow'];
                $hr = (int)$row['hr'];
                $cnt = (int)$row['cnt'];
                $rowIdx = ($dow + 5) % 7;
                $data[$rowIdx][$hr] = $cnt;
                if ($cnt > $max) {
                    $max = $cnt;
                }
            }

            $labelDays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            $labelHours = [];
            for ($h = 0; $h < 24; $h++) {
                $labelHours[] = sprintf('%02d:00', $h);
            }

            return [
                'max' => $max,
                'days' => $labelDays,
                'hours' => $labelHours,
                'data' => $data
            ];
        } catch (PDOException $e) {
            error_log("getPeakUsageHeatmap error: " . $e->getMessage());
            return ['max' => 0, 'days' => [], 'hours' => [], 'data' => []];
        }
    }

    public function getTopEngagedUsers($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                  u.user_id,
                  u.full_name,
                  u.email,
                  u.profile_picture,
                  (COUNT(DISTINCT p.post_id) + COUNT(DISTINCT c.comment_id) + COUNT(DISTINCT r.reply_id) + COUNT(DISTINCT l.like_id)) as total_engagement
                FROM users u
                LEFT JOIN posts p ON u.user_id = p.user_id AND p.active = 1
                LEFT JOIN comments c ON u.user_id = c.user_id AND c.active = 1
                LEFT JOIN replies r ON u.user_id = r.user_id AND r.active = 1
                LEFT JOIN likes l ON u.user_id = l.user_id
                WHERE u.active = 1 AND u.role != 'admin'
                GROUP BY u.user_id, u.full_name, u.email, u.profile_picture
                ORDER BY total_engagement DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getTopEngagedUsers error: " . $e->getMessage());
            return [];
        }
    }
}
