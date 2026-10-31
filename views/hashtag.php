<?php
namespace App\views;

use App\Components\Post;

$currentUserId = getCurrentUserId();

// These maps are pre-fetched by HashtagController in bulk (eliminates N+1 queries)
$likedMap = $likedMap ?? [];
$commentsMap = $commentsMap ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - #<?php echo safe_output($tag); ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body class="posts-page">
    <?php
    $currentPage = 'posts';
    require_once 'assets/sidebar.php';
    ?>

    <div class="main-content">
        <?php require_once 'assets/search_header.php'; ?>

        <div class="content-wrapper posts-content-wrapper">
            <div class="hashtag-page-header">
                <a href="/posts" class="hashtag-back-link">&larr; Volver a publicaciones</a>
                <h1 class="hashtag-page-title">
                    #<?php echo safe_output($tag); ?>
                </h1>
                <span class="hashtag-page-count">
                    <?php echo $postCount; ?> <?php echo $postCount === 1 ? 'publicación' : 'publicaciones'; ?>
                </span>
            </div>

            <?php
            if (empty($postsData)) {
                echo '<div class="posts-empty-state">';
                echo '    <div class="posts-empty-icon">';
                echo '        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
                echo '            <line x1="4" y1="9" x2="20" y2="9"></line>';
                echo '            <line x1="4" y1="15" x2="20" y2="15"></line>';
                echo '            <line x1="10" y1="3" x2="8" y2="21"></line>';
                echo '            <line x1="16" y1="3" x2="14" y2="21"></line>';
                echo '        </svg>';
                echo '    </div>';
                echo '    <h3>No hay publicaciones con este hashtag aún</h3>';
                echo '    <p>Sé el primero en compartir ideas o preguntas sobre #' . safe_output($tag) . '.</p>';
                echo '    <div class="posts-empty-actions">';
                echo '        <a href="/addPost" class="btn btn-primary">Crear publicación</a>';
                echo '        <a href="/posts" class="btn btn-secondary">Volver al muro</a>';
                echo '    </div>';
                echo '</div>';
            } else {
                foreach ($postsData as $postData) {
                    // Use pre-fetched bulk data (eliminates N+1 queries)
                    $hasLiked = $likedMap[$postData['post_id']] ?? false;
                    $likesCount = $postData['likes_count'] ?? 0;
                    $comments = $commentsMap[$postData['post_id']] ?? [];
                    $commentsCount = $postData['comments_count'] ?? 0;
                    $authorPicture = getProfilePicture($postData['author_picture'] ?? '');

                    $postComponent = new Post([
                        'id' => $postData['post_id'],
                        'author' => $postData['author_name'] ?? 'Usuario',
                        'author_role' => $postData['author_role'] ?? 'user',
                        'date' => date('d/m/Y', strtotime($postData['created_at'])),
                        'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                        'image_alt' => 'Imagen del post',
                        'text' => $postData['content'],
                        'likes' => $likesCount,
                        'comments_count' => $commentsCount,
                        'comments' => $comments,
                        'user_id' => $postData['user_id'],
                        'current_user_id' => $currentUserId,
                        'has_liked' => $hasLiked,
                        'user_avatar' => $authorPicture
                    ]);

                    echo $postComponent->render();
                }

                // Pagination controls for hashtag posts
                if ($totalPages > 1):
                ?>
                <nav class="posts-pagination" aria-label="Paginación de hashtag">
                    <?php if ($page > 1): ?>
                        <a href="/hashtag/<?php echo urlencode($tag); ?>?page=<?php echo $page - 1; ?>" class="posts-page-btn posts-page-prev" aria-label="Página anterior">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>Anterior</span>
                        </a>
                    <?php endif; ?>

                    <div class="posts-page-info">
                        <span>Página <strong><?php echo $page; ?></strong> de <strong><?php echo $totalPages; ?></strong></span>
                        <span class="posts-page-count-badge">(<?php echo $postCount; ?> publicaciones)</span>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="/hashtag/<?php echo urlencode($tag); ?>?page=<?php echo $page + 1; ?>" class="posts-page-btn posts-page-next" aria-label="Página siguiente">
                            <span>Siguiente</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
                <?php
                endif;
            }
            ?>
        </div>
    </div>

    <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>
