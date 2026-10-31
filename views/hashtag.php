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

<body>
    <?php
    $currentPage = 'posts';
    require_once 'assets/sidebar.php';
    ?>

    <div class="main-content">
        <?php require_once 'assets/search_header.php'; ?>

        <div class="content-wrapper">
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
                echo '<div class="no-posts">No hay publicaciones con este hashtag aún. <a href="/addPost">Sé el primero en publicar</a></div>';
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
                <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin: 30px 0; padding: 15px;">
                    <?php if ($page > 1): ?>
                        <a href="/hashtag/<?php echo urlencode($tag); ?>?page=<?php echo $page - 1; ?>" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">&larr; Anterior</a>
                    <?php endif; ?>

                    <span style="color: #666; font-size: 14px;">
                        P&aacute;gina <?php echo $page; ?> de <?php echo $totalPages; ?> (<?php echo $postCount; ?> publicaciones)
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="/hashtag/<?php echo urlencode($tag); ?>?page=<?php echo $page + 1; ?>" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">Siguiente &rarr;</a>
                    <?php endif; ?>
                </div>
                <?php
                endif;
            }
            ?>
        </div>
    </div>

        <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>
