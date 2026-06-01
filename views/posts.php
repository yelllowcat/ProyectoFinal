<?php
namespace App\views;
use App\Components\Post;
use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\HashtagModel;

$postModel = new PostModel();
$likeModel = new LikeModel();
$commentModel = new CommentModel();
$hashtagModel = new HashtagModel();

$currentUserId = $_SESSION['user_id'];

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$postsData = $postModel->getAllPostsPaginated($page, $perPage);
$totalPosts = $postModel->getTotalPostsCount();
$totalPages = (int) ceil($totalPosts / $perPage);

// Bulk fetch data for all posts in one go (eliminates N+1 queries)
$postIds = array_column($postsData, 'post_id');
$likedMap = $likeModel->bulkHasLiked($postIds, $currentUserId);
$commentsMap = $commentModel->getFirstCommentsForPosts($postIds, 3);
$trendingHashtags = $hashtagModel->getTrendingHashtags(8);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Publicaciones</title>
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
        <div class="content-wrapper">
            <?php if (!empty($trendingHashtags)): ?>
            <div class="trending-hashtags-widget">
                <span class="trending-label">Tendencias</span>
                <div class="trending-hashtags-scroll">
                    <?php foreach ($trendingHashtags as $tag): ?>
                        <a href="/hashtag/<?php echo urlencode($tag['name']); ?>" class="trending-hashtag-pill">
                            #<?php echo safe_output($tag['name']); ?>
                            <span class="trending-hashtag-count"><?php echo $tag['post_count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            if (empty($postsData)) {
                echo '<div class="no-posts">No hay publicaciones aún. <a href="/addPost">Sé el primero en publicar</a></div>';
            } else {
                foreach ($postsData as $postData) {
                    // Use pre-fetched bulk data instead of N+1 queries
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
            }

            // Pagination controls
            if ($totalPages > 1):
            ?>
            <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin: 30px 0; padding: 15px;">
                <?php if ($page > 1): ?>
                    <a href="/posts?page=<?php echo $page - 1; ?>" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">&larr; Anterior</a>
                <?php endif; ?>

                <span style="color: #666; font-size: 14px;">
                    P&aacute;gina <?php echo $page; ?> de <?php echo $totalPages; ?> (<?php echo $totalPosts; ?> publicaciones)
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="/posts?page=<?php echo $page + 1; ?>" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">Siguiente &rarr;</a>
                <?php endif; ?>
            </div>
            <?php
            endif;
            ?>
        </div>
    </div>

    <dialog id="image-modal" class="image-modal">
        <div class="image-modal-content">
            <button class="image-modal-close" id="closeImageModal">
                <img src="../assets/images/x.png" alt="Cerrar">
            </button>
            <img id="modalImage" src="" alt="Post image">
        </div>
    </dialog>

    <dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar esta publicación?</p>
            </div>
            <div class="confirm-sep"></div>
            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-delete">Eliminar</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>
    <dialog id="confirm-delete-comment-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar este comentario?</p>
            </div>
            <div class="confirm-sep"></div>
            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-delete">Eliminar</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>

    <!-- Report Modal -->
    <dialog id="report-modal" class="confirm-dialog" aria-labelledby="report-modal-title">
        <div class="confirm-box" style="width: 400px; max-width: 90%; text-align: left;">
            <div class="confirm-head">
                <h3 id="report-modal-title" style="margin-bottom: 5px;">Reportar Contenido</h3>
                <p class="confirm-subtitle" style="margin: 0; font-size: 14px; color: #666;">Selecciona una razón para reportar este contenido:</p>
            </div>
            <div class="confirm-sep"></div>
            <form id="report-form" class="report-form" style="padding: 15px; display: flex; flex-direction: column; gap: 10px;">
                <input type="hidden" id="report-entity-type" name="entity_type">
                <input type="hidden" id="report-entity-id" name="entity_id">
                
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Spam" required> Spam
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Contenido inapropiado"> Contenido inapropiado
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Acoso o bullying"> Acoso o bullying
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Discurso de odio"> Discurso de odio
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Otro"> Otro
                </label>

                <textarea id="report-reason-details" name="reason_details" placeholder="Detalles opcionales..." style="margin-top: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit; resize: vertical; min-height: 60px;"></textarea>

                <div class="confirm-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('report-modal').close()" class="confirm-cancel" style="padding: 8px 16px;">Cancelar</button>
                    <button type="submit" class="confirm-delete" style="background-color: #f44336; border: none; color: white; padding: 8px 16px; cursor: pointer; border-radius: 4px; font-weight: bold;">Enviar Reporte</button>
                </div>
            </form>
        </div>
    </dialog>
</body>

</html>