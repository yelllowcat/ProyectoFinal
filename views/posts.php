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

$currentUserId = (int) $_SESSION['user_id'];

// Tab filter
$activeTab = clean_input($_GET['tab'] ?? 'all');
if (!in_array($activeTab, ['all', 'friends', 'teachers'], true)) {
    $activeTab = 'all';
}

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$postsData = $postModel->getAllPostsPaginated($page, $perPage, $activeTab, $currentUserId);
$totalPosts = $postModel->getTotalPostsCount($activeTab, $currentUserId);
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
        <?= \App\Components\Alert::render(); ?>

        <div class="content-wrapper posts-content-wrapper">
            <div class="feed-tabs-container">
                <div class="friends-tabs feed-tabs" role="tablist">
                    <a href="/posts?tab=all" class="tab <?php echo $activeTab === 'all' ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'all' ? 'true' : 'false'; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        <span>Comunidad</span>
                    </a>
                    <a href="/posts?tab=friends" class="tab <?php echo $activeTab === 'friends' ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'friends' ? 'true' : 'false'; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Mis Amigos</span>
                    </a>
                    <a href="/posts?tab=teachers" class="tab <?php echo $activeTab === 'teachers' ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'teachers' ? 'true' : 'false'; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                        <span>Profesores / Avisos</span>
                    </a>
                </div>
            </div>

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
                echo '<div class="posts-empty-state">';
                echo '    <div class="posts-empty-icon">';
                echo '        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
                echo '            <rect x="3" y="3" width="18" height="18" rx="5" ry="5"></rect>';
                echo '            <line x1="8" y1="12" x2="16" y2="12"></line>';
                echo '        </svg>';
                echo '    </div>';

                if ($activeTab === 'friends') {
                    echo '    <h3>Aún no hay publicaciones de tus amigos</h3>';
                    echo '    <p>Conecta con compañeros de tu facultad o carrera para estar al día con lo que comparten.</p>';
                    echo '    <div class="posts-empty-actions">';
                    echo '        <a href="/friends" class="btn btn-primary">Encontrar compañeros</a>';
                    echo '        <a href="/addPost" class="btn btn-secondary">Crear publicación</a>';
                    echo '    </div>';
                } elseif ($activeTab === 'teachers') {
                    echo '    <h3>No hay avisos de profesores por el momento</h3>';
                    echo '    <p>Los avisos o publicaciones académicas de los docentes aparecerán en esta sección.</p>';
                    echo '    <div class="posts-empty-actions">';
                    echo '        <a href="/posts?tab=all" class="btn btn-secondary">Ver toda la comunidad</a>';
                    echo '    </div>';
                } else {
                    echo '    <h3>Aún no hay publicaciones</h3>';
                    echo '    <p>Sé el primero en compartir ideas, dudas o novedades con toda la comunidad universitaria.</p>';
                    echo '    <div class="posts-empty-actions">';
                    echo '        <a href="/addPost" class="btn btn-primary">Crear primera publicación</a>';
                    echo '    </div>';
                }
                echo '</div>';
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
                        'created_at' => $postData['created_at'],
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
                $tabParam = $activeTab !== 'all' ? '&tab=' . urlencode($activeTab) : '';
            ?>
            <nav class="posts-pagination" aria-label="Paginación de publicaciones">
                <?php if ($page > 1): ?>
                    <a href="/posts?page=<?php echo $page - 1; ?><?php echo $tabParam; ?>" class="posts-page-btn posts-page-prev" aria-label="Página anterior">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Anterior</span>
                    </a>
                <?php endif; ?>

                <div class="posts-page-info">
                    <span>Página <strong><?php echo $page; ?></strong> de <strong><?php echo $totalPages; ?></strong></span>
                    <span class="posts-page-count-badge">(<?php echo $totalPosts; ?> publicaciones)</span>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="/posts?page=<?php echo $page + 1; ?><?php echo $tabParam; ?>" class="posts-page-btn posts-page-next" aria-label="Página siguiente">
                        <span>Siguiente</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                <?php endif; ?>
            </nav>
            <?php
            endif;
            ?>
        </div>
    </div>

    <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>
