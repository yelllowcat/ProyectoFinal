<?php

namespace App\Controllers;

use App\Models\ReportModel;

class ReportController
{
    private $reportModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
    }

    public function store()
    {
        requireUser();
        requireAuth();

        $reporterId = $_SESSION['user']['id'];
        $reportedUserId = $_POST['reported_user_id'] ?? null;
        $postId = $_POST['post_id'] ?? null;
        $commentId = $_POST['comment_id'] ?? null;
        $replyId = $_POST['reply_id'] ?? null;
        $reason = $_POST['reason'] ?? '';

        if (empty($reason)) {
            jsonError('Debes proporcionar una razón para el reporte.');
            return;
        }

        if (!$reportedUserId && !$postId && !$commentId && !$replyId) {
            jsonError('Debe especificar un elemento para reportar.');
            return;
        }

        $success = $this->reportModel->createReport(
            $reporterId,
            $reportedUserId ?: null,
            $postId ?: null,
            $commentId ?: null,
            $replyId ?: null,
            $reason
        );

        if ($success) {
            jsonSuccess(['message' => 'Reporte enviado correctamente. Nuestro equipo lo revisará pronto.']);
        } else {
            jsonError('Hubo un error al enviar tu reporte. Inténtalo más tarde.');
        }
    }

    public function index()
    {
        requireAdmin();
        $reports = $this->reportModel->getAllReports();
        jsonSuccess($reports);
    }

    public function resolve($id)
    {
        requireAdmin();
        $status = $_POST['status'] ?? 'resolved';
        
        $success = $this->reportModel->resolveReport($id, $status);
        if ($success) {
            jsonSuccess(['message' => 'Estado del reporte actualizado.']);
        } else {
            jsonError('Error al actualizar el reporte.');
        }
    }

    public function destroy($id)
    {
        requireAdmin();
        $success = $this->reportModel->deleteReport($id);
        if ($success) {
            jsonSuccess(['message' => 'Reporte eliminado.']);
        } else {
            jsonError('Error al eliminar el reporte.');
        }
    }
}
