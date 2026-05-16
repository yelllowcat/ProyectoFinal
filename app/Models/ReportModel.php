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
}
