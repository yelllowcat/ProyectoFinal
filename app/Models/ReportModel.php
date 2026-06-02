<?php

namespace App\Models;

use PDO;
use PDOException;
require_once __DIR__ . '/../../config/database.php';

class ReportModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createReport($reporterId, $reportedUserId, $postId, $commentId, $replyId, $reason)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_create_report(?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $reporterId,
                $reportedUserId,
                $postId,
                $commentId,
                $replyId,
                $reason
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['report_id'] ?? true;
        } catch (PDOException $e) {
            error_log("createReport error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllReports()
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_get_reports()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getAllReports error: " . $e->getMessage());
            return [];
        }
    }

    public function getReportById($reportId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT r.*, 
                       u.full_name AS reporter_name, 
                       ru.full_name AS reported_user_name,
                       p.content AS post_content,
                       p.user_id AS post_user_id,
                       c.content AS comment_content,
                       c.post_id AS comment_post_id,
                       re.content AS reply_content,
                       rc.post_id AS reply_post_id
                FROM reports r
                JOIN users u ON r.reporter_id = u.user_id
                LEFT JOIN users ru ON r.reported_user_id = ru.user_id
                LEFT JOIN posts p ON r.post_id = p.post_id
                LEFT JOIN comments c ON r.comment_id = c.comment_id
                LEFT JOIN replies re ON r.reply_id = re.reply_id
                LEFT JOIN comments rc ON re.comment_id = rc.comment_id
                WHERE r.report_id = ?
            ");
            $stmt->execute([$reportId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getReportById error: " . $e->getMessage());
            return false;
        }
    }

    public function resolveReport($reportId, $status)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_resolve_report(?, ?)");
            $stmt->execute([$reportId, $status]);
            return true;
        } catch (PDOException $e) {
            error_log("resolveReport error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteReport($reportId)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_delete_report(?)");
            $stmt->execute([$reportId]);
            return true;
        } catch (PDOException $e) {
            error_log("deleteReport error: " . $e->getMessage());
            return false;
        }
    }

    public function takeModerationAction($reportId, $action)
    {
        try {
            // First, fetch the report to know what to act on
            $stmt = $this->pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                return false;
            }

            // Execute action based on type
            if ($action === 'delete') {
                if ($report['post_id']) {
                    $this->pdo->prepare("CALL sp_admin_delete_post(?)")->execute([$report['post_id']]);
                } elseif ($report['comment_id']) {
                    $this->pdo->prepare("CALL sp_admin_delete_comment(?)")->execute([$report['comment_id']]);
                } elseif ($report['reply_id']) {
                    $this->pdo->prepare("CALL sp_admin_delete_reply(?)")->execute([$report['reply_id']]);
                }
            } elseif ($action === 'suspend') {
                // First delete the content as well
                if ($report['post_id']) {
                    $this->pdo->prepare("CALL sp_admin_delete_post(?)")->execute([$report['post_id']]);
                } elseif ($report['comment_id']) {
                    $this->pdo->prepare("CALL sp_admin_delete_comment(?)")->execute([$report['comment_id']]);
                } elseif ($report['reply_id']) {
                    $this->pdo->prepare("CALL sp_admin_delete_reply(?)")->execute([$report['reply_id']]);
                }

                // Then suspend the user (either the specifically reported user or the content author)
                $userIdToSuspend = $report['reported_user_id'];
                
                // If it wasn't a direct user report, we need to find the author's ID
                if (!$userIdToSuspend) {
                    if ($report['post_id']) {
                        $stmt = $this->pdo->prepare("SELECT user_id FROM posts WHERE post_id = ?");
                        $stmt->execute([$report['post_id']]);
                        $userIdToSuspend = $stmt->fetchColumn();
                    } elseif ($report['comment_id']) {
                        $stmt = $this->pdo->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
                        $stmt->execute([$report['comment_id']]);
                        $userIdToSuspend = $stmt->fetchColumn();
                    } elseif ($report['reply_id']) {
                        $stmt = $this->pdo->prepare("SELECT user_id FROM replies WHERE reply_id = ?");
                        $stmt->execute([$report['reply_id']]);
                        $userIdToSuspend = $stmt->fetchColumn();
                    }
                }

                if ($userIdToSuspend) {
                    $this->pdo->prepare("CALL sp_admin_suspend_user(?)")->execute([$userIdToSuspend]);
                }
            }

            // Always resolve the report at the end
            return $this->resolveReport($reportId, 'resolved');

        } catch (PDOException $e) {
            error_log("takeModerationAction error: " . $e->getMessage());
            return false;
        }
    }
}
