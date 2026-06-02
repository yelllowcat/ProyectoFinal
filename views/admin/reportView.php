<?php
namespace App\views\admin;

use App\Components\Post;
use App\Components\Profile;
use App\Models\ReportModel;
use App\Models\PostModel;
use App\Models\CommentModel;
use App\Models\ReplyModel;
use App\Models\LikeModel;
use App\Models\UserModel;
use App\Models\FriendModel;

$reportId = $_GET['report_id'] ?? null;

if (!$reportId) {
    flash('error', 'Reporte no encontrado');
    redirect('/dashboard');
}

$reportModel = new ReportModel();
$report = $reportModel->getReportById($reportId);

if (!$report) {
    flash('error', 'Reporte no encontrado');
    redirect('/dashboard');
}

$postModel = new PostModel();
$commentModel = new CommentModel();
$replyModel = new ReplyModel();
$likeModel = new LikeModel();
$userModel = new UserModel();
$friendModel = new FriendModel();

$currentUserId = $_SESSION['user_id'] ?? null;

$contentHtml = '';
$reportTypeLabel = '';
$reportedItemLabel = '';

if ($report['reported_user_id']) {
    // User report
    $reportTypeLabel = 'Usuario';
    $user = $userModel->getUserById($report['reported_user_id']);
    $userPosts = $postModel->getPostsByUserId($report['reported_user_id']);
    $friendsCount = count($friendModel->getFriends($report['reported_user_id']));
    $totalLikes = 0;
    foreach ($userPosts as $post) {
        $totalLikes += $likeModel->getLikeCount($post['post_id']);
    }

    $profilePicture = getProfilePicture($user['profile_picture'] ?? null);

    $profileComponent = new Profile(
        'stranger',
        $user['full_name'] ?? 'Usuario',
        $user['biography'] ?? '',
        count($userPosts),
        $totalLikes,
        $friendsCount,
        $report['reported_user_id'],
        $profilePicture,
        $user['role'] ?? 'user'
    );

    $contentHtml = "<div class='report-banner'>
        <span class='report-banner-icon'>!</span>
        <span class='report-banner-text'>Usuario Reportado — {$report['reason']}</span>
    </div>";
    $contentHtml .= $profileComponent->render();

    // Show user's recent posts
    if (!empty($userPosts)) {
        $contentHtml .= "<h3 style='margin: 30px 0 15px; color: #333;'>Publicaciones recientes</h3>";
        foreach (array_slice($userPosts, 0, 5) as $postData) {
            $author = $userModel->getUserById($postData['user_id']);
            $likesCount = $likeModel->getLikeCount($postData['post_id']);
            $comments = $commentModel->getCommentsByPost($postData['post_id']);
            $commentsCount = $commentModel->getCommentCount($postData['post_id']);
            $authorPicture = getProfilePicture($author['profile_picture']);

            $postComponent = new Post([
                'id' => $postData['post_id'],
                'author' => $author['full_name'],
                'author_role' => $author['role'] ?? 'user',
                'date' => date('d/m/Y', strtotime($postData['created_at'])),
                'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                'image_alt' => 'Imagen del post',
                'text' => $postData['content'],
                'likes' => $likesCount,
                'comments_count' => $commentsCount,
                'comments' => $comments,
                'user_id' => $postData['user_id'],
                'current_user_id' => $currentUserId,
                'has_liked' => false,
                'user_avatar' => $authorPicture,
                'single_mode' => true,
                'expand_all_comments' => false
            ]);
            $contentHtml .= $postComponent->render();
        }
    }

    $reportedItemLabel = 'Usuario: ' . ($user['full_name'] ?? 'Desconocido');
} elseif ($report['post_id']) {
    // Post report
    $reportTypeLabel = 'Publicación';
    $postData = $postModel->getPostById($report['post_id']);

    if (!$postData) {
        $contentHtml = '<p>Esta publicación ya no existe o fue eliminada.</p>';
    } else {
        $author = $userModel->getUserById($postData['user_id']);
        $likesCount = $likeModel->getLikeCount($postData['post_id']);
        $comments = $commentModel->getCommentsByPost($postData['post_id']);
        $commentsCount = $commentModel->getCommentCount($postData['post_id']);
        $authorPicture = getProfilePicture($author['profile_picture']);

        // Fetch all replies for all comments
        $repliesData = [];
        foreach ($comments as $comment) {
            $commentId = $comment['comment_id'] ?? $comment['id'];
            $replies = $replyModel->getRepliesByComment($commentId);
            if (!empty($replies)) {
                $repliesData[$commentId] = $replies;
            }
        }

        $postComponent = new Post([
            'id' => $postData['post_id'],
            'author' => $author['full_name'],
            'author_role' => $author['role'] ?? 'user',
            'date' => date('d/m/Y', strtotime($postData['created_at'])),
            'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
            'image_alt' => 'Imagen del post',
            'text' => $postData['content'],
            'likes' => $likesCount,
            'comments_count' => $commentsCount,
            'comments' => $comments,
            'user_id' => $postData['user_id'],
            'current_user_id' => $currentUserId,
            'has_liked' => false,
            'user_avatar' => $authorPicture,
            'single_mode' => true,
            'expand_all_comments' => true,
            'replies_data' => $repliesData
        ]);
        $contentHtml = $postComponent->render();
    }

    $reportedItemLabel = 'Publicación #' . $report['post_id'];
} elseif ($report['comment_id']) {
    // Comment report
    $reportTypeLabel = 'Comentario';
    $comment = $commentModel->getCommentById($report['comment_id']);

    if (!$comment) {
        $contentHtml = '<p>Este comentario ya no existe o fue eliminado.</p>';
    } else {
        $postData = $postModel->getPostById($comment['post_id']);
        if (!$postData) {
            $contentHtml = '<p>La publicación padre ya no existe.</p>';
        } else {
            $author = $userModel->getUserById($postData['user_id']);
            $likesCount = $likeModel->getLikeCount($postData['post_id']);
            $comments = $commentModel->getCommentsByPost($postData['post_id']);
            $commentsCount = $commentModel->getCommentCount($postData['post_id']);
            $authorPicture = getProfilePicture($author['profile_picture']);

            // Fetch all replies for all comments
            $repliesData = [];
            foreach ($comments as $c) {
                $cId = $c['comment_id'] ?? $c['id'];
                $replies = $replyModel->getRepliesByComment($cId);
                if (!empty($replies)) {
                    $repliesData[$cId] = $replies;
                }
            }

            $postComponent = new Post([
                'id' => $postData['post_id'],
                'author' => $author['full_name'],
                'author_role' => $author['role'] ?? 'user',
                'date' => date('d/m/Y', strtotime($postData['created_at'])),
                'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                'image_alt' => 'Imagen del post',
                'text' => $postData['content'],
                'likes' => $likesCount,
                'comments_count' => $commentsCount,
                'comments' => $comments,
                'user_id' => $postData['user_id'],
                'current_user_id' => $currentUserId,
                'has_liked' => false,
                'user_avatar' => $authorPicture,
                'single_mode' => true,
                'expand_all_comments' => true,
                'highlight_comment_id' => $report['comment_id'],
                'replies_data' => $repliesData
            ]);
            $contentHtml = $postComponent->render();
        }
    }

    $reportedItemLabel = 'Comentario #' . $report['comment_id'];
} elseif ($report['reply_id']) {
    // Reply report
    $reportTypeLabel = 'Respuesta';
    $reply = $replyModel->getReplyById($report['reply_id']);

    if (!$reply) {
        $contentHtml = '<p>Esta respuesta ya no existe o fue eliminada.</p>';
    } else {
        $comment = $commentModel->getCommentById($reply['comment_id']);
        if (!$comment) {
            $contentHtml = '<p>El comentario padre ya no existe.</p>';
        } else {
            $postData = $postModel->getPostById($comment['post_id']);
            if (!$postData) {
                $contentHtml = '<p>La publicación padre ya no existe.</p>';
            } else {
                $author = $userModel->getUserById($postData['user_id']);
                $likesCount = $likeModel->getLikeCount($postData['post_id']);
                $comments = $commentModel->getCommentsByPost($postData['post_id']);
                $commentsCount = $commentModel->getCommentCount($postData['post_id']);
                $authorPicture = getProfilePicture($author['profile_picture']);

                // Fetch all replies for all comments
                $repliesData = [];
                foreach ($comments as $c) {
                    $cId = $c['comment_id'] ?? $c['id'];
                    $replies = $replyModel->getRepliesByComment($cId);
                    if (!empty($replies)) {
                        $repliesData[$cId] = $replies;
                    }
                }

                $postComponent = new Post([
                    'id' => $postData['post_id'],
                    'author' => $author['full_name'],
                    'author_role' => $author['role'] ?? 'user',
                    'date' => date('d/m/Y', strtotime($postData['created_at'])),
                    'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                    'image_alt' => 'Imagen del post',
                    'text' => $postData['content'],
                    'likes' => $likesCount,
                    'comments_count' => $commentsCount,
                    'comments' => $comments,
                    'user_id' => $postData['user_id'],
                    'current_user_id' => $currentUserId,
                    'has_liked' => false,
                    'user_avatar' => $authorPicture,
                    'single_mode' => true,
                    'expand_all_comments' => true,
                    'highlight_reply_id' => $report['reply_id'],
                    'replies_data' => $repliesData
                ]);
                $contentHtml = $postComponent->render();
            }
        }
    }

    $reportedItemLabel = 'Respuesta #' . $report['reply_id'];
}

$statusBadge = $report['status'] === 'pending'
    ? '<span class="report-status-badge pending">Pendiente</span>'
    : '<span class="report-status-badge resolved">Resuelto</span>';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Reporte #<?php echo $reportId; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/styles/dashboard.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    <script>
        window.isUserReport = <?php echo $report['reported_user_id'] ? 'true' : 'false'; ?>;
    </script>
    <script src="<?php echo asset('js/reportView.js'); ?>"></script>
    <style>
        /* Override styles.css body flex for report view */
        body {
            display: block !important;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <a href="/dashboard" class="admin-logo-link">
            <img class="admin-logo" src="../../assets/images/logoUnired.png" alt="UNIRED Logo">
        </a>
        <button class="btn-logout-header" id="admin-logout">Cerrar sesión</button>
    </header>

    <div class="report-view-container">
        <div class="report-content">
            <a href="/dashboard" class="back-to-dashboard">Volver al Dashboard</a>

            <!-- Report Header Card -->
            <div class="report-header-card fade-in-up">
                <div class="report-header-icon type-<?php echo strtolower($reportTypeLabel); ?>">
                    <span class="report-type-letter"><?php
                    $typeLetter = 'P';
                    if ($report['comment_id']) { $typeLetter = 'C'; }
                    elseif ($report['reply_id']) { $typeLetter = 'R'; }
                    elseif ($report['reported_user_id']) { $typeLetter = 'U'; }
                    echo $typeLetter;
                    ?></span>
                </div>
                <div class="report-header-info">
                    <h1 class="report-title">Reporte #<?php echo $reportId; ?></h1>
                    <span class="report-subtitle"><?php echo $reportTypeLabel; ?></span>
                </div>
                <div class="report-header-status">
                    <?php echo $statusBadge; ?>
                </div>
            </div>

            <!-- Context Card -->
            <div class="report-context-card fade-in-up" style="animation-delay: 0.1s;">
                <h2 class="card-section-title">Detalles del Reporte</h2>
                <div class="report-context-grid">
                    <div class="context-item">
                        <span class="context-label">Reportado por</span>
                        <span class="context-value"><?php echo safe_output($report['reporter_name'] ?? 'Desconocido'); ?></span>
                    </div>
                    <div class="context-item">
                        <span class="context-label">Fecha</span>
                        <span class="context-value"><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></span>
                    </div>
                    <div class="context-item">
                        <span class="context-label">Razon</span>
                        <span class="context-value reason-badge"><?php echo safe_output($report['reason']); ?></span>
                    </div>
                    <div class="context-item">
                        <span class="context-label">Elemento</span>
                        <span class="context-value"><?php echo $reportedItemLabel; ?></span>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="report-content-card fade-in-up" style="animation-delay: 0.2s;">
                <h2 class="card-section-title">Contenido Reportado</h2>
                <?php echo $contentHtml; ?>
            </div>
        </div>

        <div class="moderation-panel fade-in-up" style="animation-delay: 0.3s;">
            <div class="moderation-panel-header">
                <svg class="moderation-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <h3>Panel de Moderacion</h3>
            </div>

            <div class="report-info-box">
                <div class="info-row">
                    <span class="info-label">Reporte</span>
                    <span class="info-value">#<?php echo $reportId; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tipo</span>
                    <span class="info-value"><?php echo $reportTypeLabel; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value"><?php echo $statusBadge; ?></span>
                </div>
            </div>

            <div class="moderation-divider"></div>

            <?php if ($report['status'] === 'pending'): ?>
                <div class="moderation-actions">
                    <span class="actions-label">Acciones disponibles</span>
                    <button class="moderation-btn dismiss" data-action="dismiss" data-report-id="<?php echo $reportId; ?>">
                        <span class="btn-icon icon-ok"></span>
                        <span class="btn-text">Desestimar</span>
                        <span class="btn-subtext">Falsa alarma</span>
                    </button>
                    <?php if (!$report['reported_user_id']): ?>
                        <button class="moderation-btn delete" data-action="delete" data-report-id="<?php echo $reportId; ?>">
                            <span class="btn-icon icon-trash"></span>
                            <span class="btn-text">Eliminar</span>
                            <span class="btn-subtext">Solo contenido</span>
                        </button>
                    <?php endif; ?>
                    <button class="moderation-btn suspend" data-action="suspend" data-report-id="<?php echo $reportId; ?>">
                        <span class="btn-icon icon-ban"></span>
                        <span class="btn-text">Suspender</span>
                        <span class="btn-subtext"><?php echo $report['reported_user_id'] ? 'Usuario' : 'Usuario + contenido'; ?></span>
                    </button>
                </div>
            <?php else: ?>
                <div class="resolved-state">
                    <div class="resolved-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <p class="resolved-text">Este reporte ya ha sido resuelto</p>
                    <p class="resolved-date"><?php echo $report['resolved_at'] ? date('d/m/Y H:i', strtotime($report['resolved_at'])) : ''; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <dialog id="confirm-action-modal" class="confirm-dialog">
        <div class="confirm-box" style="max-width: 400px; padding: 20px;">
            <div class="confirm-head">
                <h3 id="confirm-action-title">Confirmar acción</h3>
                <p class="confirm-subtitle" id="confirm-action-subtitle">¿Estás seguro?</p>
            </div>
            <div class="confirm-sep"></div>
            <div class="confirm-actions" style="display: flex; gap: 10px;">
                <button value="confirm" class="confirm-delete" id="confirm-action-btn">Confirmar</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </div>
        </div>
    </dialog>
</body>
</html>
