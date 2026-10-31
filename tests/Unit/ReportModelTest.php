<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ReportModel;
use PDO;
use PHPUnit\Framework\TestCase;

class ReportModelTest extends TestCase
{
    private PDO $db;
    private ReportModel $reportModel;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->db->sqliteCreateFunction('now', fn() => date('Y-m-d H:i:s'));

        $this->db->exec("
            CREATE TABLE reports (
                report_id INTEGER PRIMARY KEY AUTOINCREMENT,
                reporter_id INTEGER NOT NULL,
                reported_user_id INTEGER,
                post_id INTEGER,
                comment_id INTEGER,
                reply_id INTEGER,
                reason TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                created_at TEXT NOT NULL
            );
        ");

        $this->db->exec("
            INSERT INTO reports (
                report_id, reporter_id, reported_user_id, post_id, reason, status, created_at
            ) VALUES
            (1, 2, 4, 10, 'Spam excesivo', 'pending', '2026-01-01 10:00:00'),
            (2, 3, 4, NULL, 'Acoso o bullying', 'resolved', '2026-01-02 11:00:00'),
            (3, 2, NULL, 15, 'Contenido inapropiado', 'pending', '2026-01-03 12:00:00');
        ");

        global $pdo;
        $pdo = $this->db;
        $this->reportModel = new ReportModel();
    }

    public function testFilterReportsByStatus(): void
    {
        $allReports = $this->db->query(
            "SELECT * FROM reports ORDER BY created_at DESC"
        )->fetchAll();
        $this->assertCount(3, $allReports);

        $filterPending = array_values(
            array_filter($allReports, fn($r) => $r['status'] === 'pending')
        );
        $this->assertCount(2, $filterPending);
        $this->assertSame('pending', $filterPending[0]['status']);
        $this->assertSame('pending', $filterPending[1]['status']);

        $filterResolved = array_values(
            array_filter($allReports, fn($r) => $r['status'] === 'resolved')
        );
        $this->assertCount(1, $filterResolved);
        $this->assertSame('resolved', $filterResolved[0]['status']);
        $this->assertSame('Acoso o bullying', $filterResolved[0]['reason']);
    }

    public function testReportValidationRequiresReasonAndAtLeastOneTarget(): void
    {
        $validateReportPayload = function (array $payload): ?string {
            $reason = trim($payload['reason'] ?? '');
            if (empty($reason)) {
                return 'Debes proporcionar una razón para el reporte.';
            }

            $hasTarget = !empty($payload['reported_user_id'])
                || !empty($payload['post_id'])
                || !empty($payload['comment_id'])
                || !empty($payload['reply_id']);

            if (!$hasTarget) {
                return 'Debe especificar un elemento para reportar.';
            }

            return null;
        };

        // Missing reason
        $this->assertSame(
            'Debes proporcionar una razón para el reporte.',
            $validateReportPayload(['reason' => '', 'post_id' => 10])
        );

        // Missing targets
        $this->assertSame(
            'Debe especificar un elemento para reportar.',
            $validateReportPayload(['reason' => 'Spam'])
        );

        // Valid report
        $this->assertNull(
            $validateReportPayload(['reason' => 'Spam', 'post_id' => 42])
        );
    }

    public function testResolveReportUpdatesStatus(): void
    {
        $stmt = $this->db->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
        $stmt->execute(['resolved', 1]);

        $updated = $this->db->query("SELECT status FROM reports WHERE report_id = 1")->fetch();
        $this->assertSame('resolved', $updated['status']);
    }

    public function testDeleteReportRemovesRow(): void
    {
        $stmt = $this->db->prepare("DELETE FROM reports WHERE report_id = ?");
        $stmt->execute([3]);

        $remaining = $this->db->query("SELECT COUNT(*) FROM reports")->fetchColumn();
        $this->assertSame(2, (int) $remaining);
    }
}
