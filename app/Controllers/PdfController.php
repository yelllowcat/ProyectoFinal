<?php

namespace App\Controllers;

use App\Models\AdminModel;

class PdfController
{
    private $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function downloadStatsPdf()
    {
        requireAdmin();

        $stats = [
            'users_most_posts' => $this->adminModel->getUsersWithMostPosts(),
            'users_most_friends' => $this->adminModel->getUsersWithMostFriends(),
            'posts_most_comments' => $this->adminModel->getPostsWithMostComments(),
            'posts_most_likes' => $this->adminModel->getPostsWithMostLikes(),
            'generated_date' => date('d/m/Y H:i:s')
        ];

        $this->generatePdfWithTcpdf($stats);
        exit;
    }

    private function generatePdfWithTcpdf($stats)
    {
        require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema UNIRED');
        $pdf->SetTitle('Reporte de Estadísticas');
        $pdf->SetSubject('Estadísticas de la Plataforma');
        $pdf->SetKeywords('UNIRED, estadísticas, reporte, PDF');

        $pdf->SetMargins(15, 25, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);

        $pdf->SetAutoPageBreak(TRUE, 25);

        $pdf->AddPage();

        $this->addHeader($pdf);

        $this->addExecutiveSummary($pdf, $stats);

        $this->addUsersMostPosts($pdf, $stats['users_most_posts']);

        $pdf->AddPage();

        $this->addUsersMostFriends($pdf, $stats['users_most_friends']);

        $this->addPostsMostComments($pdf, $stats['posts_most_comments']);

        $pdf->AddPage();

        $this->addPostsMostLikes($pdf, $stats['posts_most_likes']);

        $pdf->Output('estadisticas_unired_' . date('Y-m-d') . '.pdf', 'D');
    }

    private function addHeader($pdf)
    {
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 10, 'UNIRED', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Reporte de Estadísticas de la Plataforma', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Dashboard Administrativo', 0, 1, 'C');

        $pdf->Ln(10);
    }

    private function addExecutiveSummary($pdf, $stats)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetLineWidth(0.5);
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 10);

        $summary = "Este reporte presenta un análisis completo de la plataforma UNIRED. ";

        $pdf->MultiCell(0, 6, $summary, 0, 'J');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Métricas Principales:', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $metrics = [
            'Usuarios analizados' => count($stats['users_most_posts']),
            'Publicaciones destacadas' => count($stats['posts_most_likes']),
            'Fecha de generación' => $stats['generated_date']
        ];

        foreach ($metrics as $label => $value) {
            $pdf->Cell(60, 6, $label . ':', 0, 0, 'L');
            $pdf->SetFont('helvetica', 'B');
            $pdf->Cell(0, 6, $value, 0, 1, 'L');
            $pdf->SetFont('helvetica', '');
        }

        $pdf->Ln(10);
    }

    private function addUsersMostPosts($pdf, $users)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '1. Usuarios con más Publicaciones', 0, 1, 'L');
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        if (empty($users)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'No hay datos disponibles.', 0, 1, 'C');
            return;
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(10, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Nombre del Usuario', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Correo Electrónico', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Publicaciones', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $fill = false;
        foreach ($users as $index => $user) {
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            $pdf->Cell(10, 7, $index + 1, 1, 0, 'C', $fill);
            $pdf->Cell(70, 7, $this->truncateText($user['full_name'], 35), 1, 0, 'L', $fill);
            $pdf->Cell(70, 7, $this->truncateText($user['email'], 35), 1, 0, 'L', $fill);
            $pdf->Cell(25, 7, $user['post_count'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(10);
    }

    private function addUsersMostFriends($pdf, $users)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '2. Usuarios con más Amigos', 0, 1, 'L');
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        if (empty($users)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'No hay datos disponibles.', 0, 1, 'C');
            return;
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(10, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Nombre del Usuario', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Correo Electrónico', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Amigos', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $fill = false;
        foreach ($users as $index => $user) {
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            $pdf->Cell(10, 7, $index + 1, 1, 0, 'C', $fill);
            $pdf->Cell(70, 7, $this->truncateText($user['full_name'], 35), 1, 0, 'L', $fill);
            $pdf->Cell(70, 7, $this->truncateText($user['email'], 35), 1, 0, 'L', $fill);
            $pdf->Cell(25, 7, $user['friend_count'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(10);
    }

    private function addPostsMostComments($pdf, $posts)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '3. Publicaciones con más Comentarios', 0, 1, 'L');
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        if (empty($posts)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'No hay datos disponibles.', 0, 1, 'C');
            return;
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(10, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Autor', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Contenido', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Comentarios', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Fecha', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        $fill = false;
        foreach ($posts as $index => $post) {
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            $pdf->Cell(10, 7, $index + 1, 1, 0, 'C', $fill);
            $pdf->Cell(50, 7, $this->truncateText($post['author_name'], 25), 1, 0, 'L', $fill);
            $pdf->Cell(70, 7, $this->truncateText($post['content'], 40), 1, 0, 'L', $fill);
            $pdf->Cell(25, 7, $post['comment_count'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, date('d/m/Y', strtotime($post['created_at'])), 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(10);
    }

    private function addPostsMostLikes($pdf, $posts)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '4. Publicaciones con más "Me Gusta"', 0, 1, 'L');
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        if (empty($posts)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'No hay datos disponibles.', 0, 1, 'C');
            return;
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(10, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Autor', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Contenido', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Me Gusta', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Fecha', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        $fill = false;
        foreach ($posts as $index => $post) {
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            $pdf->Cell(10, 7, $index + 1, 1, 0, 'C', $fill);
            $pdf->Cell(50, 7, $this->truncateText($post['author_name'], 25), 1, 0, 'L', $fill);
            $pdf->Cell(70, 7, $this->truncateText($post['content'], 40), 1, 0, 'L', $fill);
            $pdf->Cell(25, 7, $post['like_count'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, date('d/m/Y', strtotime($post['created_at'])), 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(5);
    }

    private function truncateText($text, $length)
    {
        if (strlen($text) > $length) {
            return substr($text, 0, $length - 3) . '...';
        }
        return $text;
    }
}