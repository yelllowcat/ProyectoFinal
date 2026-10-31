<?php
namespace App\views;

use App\Components\Post;
use App\Components\Alert;

$postAuthorName = htmlspecialchars($postData['author_name'] ?? 'Usuario');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Publicación de <?= $postAuthorName ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body class="posts-page single-post-page">
    <?php
    $currentPage = 'posts';
    require_once 'assets/sidebar.php';
    ?>

    <div class="main-content">
        <?php require_once 'assets/search_header.php'; ?>
        <?= Alert::render(); ?>

        <div class="content-wrapper posts-content-wrapper single-post-content-wrapper">
            <div class="single-post-navigation">
                <a href="/posts" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Volver a publicaciones</span>
                </a>
            </div>

            <?php
            $authorPicture = getProfilePicture($postData['author_picture'] ?? '');
            $postComponent = new Post([
                'id' => $postData['post_id'],
                'author' => $postData['author_name'] ?? 'Usuario',
                'author_role' => $postData['author_role'] ?? 'user',
                'date' => date('d/m/Y', strtotime($postData['created_at'])),
                'created_at' => $postData['created_at'],
                'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                'image_alt' => 'Imagen del post',
                'text' => $postData['content'],
                'likes' => $postData['likes_count'] ?? 0,
                'comments_count' => $postData['comments_count'] ?? 0,
                'comments' => $comments,
                'replies_data' => $repliesData,
                'user_id' => $postData['user_id'],
                'current_user_id' => $currentUserId,
                'has_liked' => $hasLiked,
                'user_avatar' => $authorPicture,
                'single_mode' => true,
                'interactive' => true,
                'expand_all_comments' => true
            ]);

            echo $postComponent->render();
            ?>
        </div>
    </div>

    <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>
