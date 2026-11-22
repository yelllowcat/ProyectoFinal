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
            $targets = [
                'reported_user_id' => $reportedUserId,
                'post_id' => $postId,
                'comment_id' => $commentId,
                'reply_id' => $replyId,
            ];
            if ($this->hasPendingDuplicateReport((int) $reporterId, $targets)) {
                return true;
            }

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
            $stmt->closeCursor();
            return $result['report_id'] ?? true;
        } catch (PDOException $e) {
            error_log("createReport error: " . $e->getMessage());
            return false;
        }
    }

    private function hasPendingDuplicateReport(int $reporterId, array $targets): bool
    {
        $reportedUserId = $targets['reported_user_id'] ?? null;
        $postId = $targets['post_id'] ?? null;
        $commentId = $targets['comment_id'] ?? null;
        $replyId = $targets['reply_id'] ?? null;

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reports
            WHERE reporter_id = ?
              AND status = 'pending'
              AND (reported_user_id = ? OR (reported_user_id IS NULL AND ? IS NULL))
              AND (post_id = ? OR (post_id IS NULL AND ? IS NULL))
              AND (comment_id = ? OR (comment_id IS NULL AND ? IS NULL))
              AND (reply_id = ? OR (reply_id IS NULL AND ? IS NULL))
        ");
        $stmt->execute([
            $reporterId,
            $reportedUserId,
            $reportedUserId,
            $postId,
            $postId,
            $commentId,
            $commentId,
            $replyId,
            $replyId
        ]);
        return ((int) $stmt->fetchColumn()) > 0;
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
            $stmt = $this->pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                return false;
            }

            if ($action === 'delete' || $action === 'suspend') {
                $this->deleteReportedContent($report);
            }

            if ($action === 'suspend') {
                $userIdToSuspend = $this->resolveUserIdToSuspend($report);
                if ($userIdToSuspend) {
                    $this->pdo->prepare("CALL sp_admin_suspend_user(?)")->execute([$userIdToSuspend]);
                }
            }

            return $this->resolveReport($reportId, 'resolved');
        } catch (PDOException $e) {
            error_log("takeModerationAction error: " . $e->getMessage());
            return false;
        }
    }

    private function deleteReportedContent(array $report): void
    {
        if (!empty($report['post_id'])) {
            $this->pdo->prepare("CALL sp_admin_delete_post(?)")->execute([$report['post_id']]);
        } elseif (!empty($report['comment_id'])) {
            $this->pdo->prepare("CALL sp_admin_delete_comment(?)")->execute([$report['comment_id']]);
        } elseif (!empty($report['reply_id'])) {
            $this->pdo->prepare("CALL sp_admin_delete_reply(?)")->execute([$report['reply_id']]);
        }
    }

    private function resolveUserIdToSuspend(array $report)
    {
        if (!empty($report['reported_user_id'])) {
            return $report['reported_user_id'];
        }

        if (!empty($report['post_id'])) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM posts WHERE post_id = ?");
            $stmt->execute([$report['post_id']]);
            return $stmt->fetchColumn();
        }

        if (!empty($report['comment_id'])) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
            $stmt->execute([$report['comment_id']]);
            return $stmt->fetchColumn();
        }

        if (!empty($report['reply_id'])) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM replies WHERE reply_id = ?");
            $stmt->execute([$report['reply_id']]);
            return $stmt->fetchColumn();
        }

        return null;
    }
}
