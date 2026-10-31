<?php

use App\Components\Post;
use App\Components\Profile;
use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\UserModel;
use App\Models\FriendModel;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$userId = $segments[1] ?? getCurrentUserId();

$userModel = new UserModel();
$user = $userModel->getUserById($userId);

if (!$user || ($user['role'] ?? '') === 'admin') {
    flash('error', 'Usuario no encontrado');
    redirect('/posts');
}

$postModel = new PostModel();
$likeModel = new LikeModel();
$commentModel = new CommentModel();
$friendModel = new FriendModel();

$currentUserId = getCurrentUserId();

// Pagination for profile posts
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$userPosts = $postModel->getPostsByUserIdPaginated($userId, $page, $perPage);
$postCount = $postModel->countPostsByUserId($userId);
$totalPages = (int) ceil($postCount / $perPage);

// Total likes across ALL user's posts (single query instead of N)
$totalLikes = $likeModel->getTotalLikesForUser($userId);

// Bulk fetch data for displayed posts (eliminates N+1 queries)
$postIds = array_column($userPosts, 'post_id');
$likedMap = $likeModel->bulkHasLiked($postIds, $currentUserId);
$commentsMap = $commentModel->getFirstCommentsForPosts($postIds, 3);

$isOwnProfile = ($userId == $currentUserId);
$friendsCount = $friendModel->countFriends((int) $userId);

if ($isOwnProfile) {
    $state = 'own';
} else {
    $friendshipStatus = $friendModel->getFriendshipStatus($currentUserId, $userId);

    switch ($friendshipStatus) {
        case 'friends':
            $state = 'friend';
            break;
        case 'pending_received':
            $state = 'request';
            break;
        case 'pending_sent':
            $state = 'pending';
            break;
        default:
            $state = 'stranger';
            break;
    }
}

$profilePicture = getProfilePicture($user['profile_picture']);

$safe_full_name = safe_output($user['full_name'] ?? '');
$safe_biography = safe_output($user['biography'] ?? '');
$safe_email = safe_output($user['email'] ?? '');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Perfil de <?php echo $safe_full_name; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body class="profile-page">
    <?php
    $currentPage = 'profile';
    require_once 'assets/sidebar.php';
    ?>
    <div class="main-content">
        <?php require_once 'assets/search_header.php'; ?>
        <?= \App\Components\Alert::render(); ?>

        <div class="content-wrapper profile-view-container">
            <?php
            $profile = new Profile(
                $state,
                $safe_full_name,
                $safe_biography,
                $postCount,
                $totalLikes,
                $friendsCount,
                $userId,
                $profilePicture,
                $user['role'] ?? 'user',
                $user['department'] ?? null,
                $user['career'] ?? null,
                isset($user['semester']) && $user['semester'] !== null ? (int) $user['semester'] : null
            );
            echo $profile->render();
            ?>

            <!-- Profile Posts Feed Section -->
            <div class="profile-feed-section">
                <div class="profile-feed-header">
                    <div class="profile-feed-title-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                        <h2 class="profile-feed-heading">Publicaciones</h2>
                        <span class="profile-feed-badge"><?= $postCount ?></span>
                    </div>

                    <?php if ($isOwnProfile): ?>
                        <a href="/addPost" class="btn-create-post-shortcut">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Nueva publicación</span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($userPosts)): ?>
                    <div class="profile-empty-card">
                        <div class="profile-empty-icon">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <h3 class="profile-empty-title">Aún no hay publicaciones</h3>
                        <p class="profile-empty-desc">
                            <?= $isOwnProfile
                                ? 'Comparte tus experiencias académicas, dudas o proyectos con la comunidad de UNIRED.'
                                : 'Este usuario aún no ha compartido ninguna publicación en su perfil.' ?>
                        </p>
                        <?php if ($isOwnProfile): ?>
                            <a href="/addPost" class="btn btn-primary" style="margin-top: 14px;">
                                Crear mi primera publicación
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="profile-posts-list">
                        <?php foreach ($userPosts as $postData):
                            $hasLiked = $likedMap[$postData['post_id']] ?? false;
                            $likesCount = $postData['likes_count'] ?? 0;
                            $comments = $commentsMap[$postData['post_id']] ?? [];
                            $commentsCount = $postData['comments_count'] ?? 0;

                            $authorName = $postData['author_name'] ?? $safe_full_name;
                            $authorPicture = $profilePicture;
                            $safe_post_content = $postData['content'] ?? '';

                            $postComponent = new Post([
                                'id' => $postData['post_id'],
                                'author' => $authorName,
                                'author_role' => $user['role'] ?? 'user',
                                'date' => date('d/m/Y', strtotime($postData['created_at'])),
                                'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                                'image_alt' => 'Imagen del post',
                                'text' => $safe_post_content,
                                'likes' => $likesCount,
                                'comments_count' => $commentsCount,
                                'comments' => $comments,
                                'user_id' => $postData['user_id'],
                                'current_user_id' => $currentUserId,
                                'has_liked' => $hasLiked,
                                'user_avatar' => $authorPicture
                            ]);

                            echo $postComponent->render();
                        endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="profile-pagination" aria-label="Navegación de páginas">
                            <?php if ($page > 1): ?>
                                <a href="/profile/<?= $userId ?>?page=<?= $page - 1 ?>" class="pagination-btn pagination-nav-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    <span>Anterior</span>
                                </a>
                            <?php else: ?>
                                <span class="pagination-btn pagination-disabled">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    <span>Anterior</span>
                                </span>
                            <?php endif; ?>

                            <div class="pagination-status">
                                Página <strong class="pagination-current"><?= $page ?></strong> de <strong><?= $totalPages ?></strong>
                                <span class="pagination-total-posts">(<?= $postCount ?> publicaciones)</span>
                            </div>

                            <?php if ($page < $totalPages): ?>
                                <a href="/profile/<?= $userId ?>?page=<?= $page + 1 ?>" class="pagination-btn pagination-nav-btn">
                                    <span>Siguiente</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="pagination-btn pagination-disabled">
                                    <span>Siguiente</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>
