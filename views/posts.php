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
        <div class="content-wrapper">
            <div class="feed-tabs-container" style="margin-bottom: 24px;">
                <div class="friends-tabs feed-tabs">
                    <a href="/posts?tab=all" class="tab <?php echo $activeTab === 'all' ? 'active' : ''; ?>" style="text-decoration: none;">
                        <span>Comunidad (Todos)</span>
                    </a>
                    <a href="/posts?tab=friends" class="tab <?php echo $activeTab === 'friends' ? 'active' : ''; ?>" style="text-decoration: none;">
                        <span>Mis Amigos</span>
                    </a>
                    <a href="/posts?tab=teachers" class="tab <?php echo $activeTab === 'teachers' ? 'active' : ''; ?>" style="text-decoration: none;">
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
                if ($activeTab === 'friends') {
                    echo '<div class="no-posts">Aún no hay publicaciones de tus amigos. <a href="/friends">Encuentra compañeros</a> o sé el primero en publicar.</div>';
                } elseif ($activeTab === 'teachers') {
                    echo '<div class="no-posts">No hay avisos o publicaciones de profesores por el momento.</div>';
                } else {
                    echo '<div class="no-posts">No hay publicaciones aún. <a href="/addPost">Sé el primero en publicar</a></div>';
                }
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
            ?>
            <?php $tabParam = $activeTab !== 'all' ? '&tab=' . urlencode($activeTab) : ''; ?>
            <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin: 30px 0; padding: 15px;">
                <?php if ($page > 1): ?>
                    <a href="/posts?page=<?php echo $page - 1; ?><?php echo $tabParam; ?>" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">&larr; Anterior</a>
                <?php endif; ?>

                <span style="color: #666; font-size: 14px;">
                    P&aacute;gina <?php echo $page; ?> de <?php echo $totalPages; ?> (<?php echo $totalPosts; ?> publicaciones)
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="/posts?page=<?php echo $page + 1; ?><?php echo $tabParam; ?>" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">Siguiente &rarr;</a>
                <?php endif; ?>
            </div>
            <?php
            endif;
            ?>
        </div>
    </div>

        <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>