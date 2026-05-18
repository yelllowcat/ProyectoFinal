<?php
namespace App\views;

use App\Components\Post;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\UserModel;

$likeModel = new LikeModel();
$commentModel = new CommentModel();
$userModel = new UserModel();
$currentUserId = getCurrentUserId();

// Determine active tab
$activeTab = $type ?: 'all';

// Count results per category
$userCount = count($results['users'] ?? []);
$postCount = count($results['posts'] ?? []);
$hashtagCount = count($results['hashtags'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Buscar: <?php echo safe_output($query); ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body>
    <?php
    $currentPage = 'search';
    require_once 'assets/sidebar.php';
    ?>

    <div class="main-content">
        <?php require_once 'assets/search_header.php'; ?>

        <div class="content-wrapper">
            <div class="search-results-container">
                <?php if (!empty($query)): ?>
                <div class="search-results-header">
                    <h1 class="search-results-title">
                        Resultados para "<?php echo safe_output($query); ?>"
                    </h1>
                    <span class="search-results-count">
                        <?php echo $totalResults; ?> resultado<?php echo $totalResults !== 1 ? 's' : ''; ?> encontrado<?php echo $totalResults !== 1 ? 's' : ''; ?>
                    </span>
                </div>
                <?php endif; ?>

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

                <div class="search-tabs">
                    <a href="/search?q=<?php echo urlencode($query); ?>" 
                       class="search-tab <?php echo $activeTab === 'all' ? 'active' : ''; ?>"
                       data-type="all">
                        Todos
                        <?php if ($totalResults > 0 && $activeTab === 'all'): ?>
                            <span class="tab-count"><?php echo $totalResults; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/search?q=<?php echo urlencode($query); ?>&type=users" 
                       class="search-tab <?php echo $activeTab === 'users' ? 'active' : ''; ?>"
                       data-type="users">
                        Usuarios
                        <?php if ($userCount > 0): ?>
                            <span class="tab-count"><?php echo $userCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/search?q=<?php echo urlencode($query); ?>&type=posts" 
                       class="search-tab <?php echo $activeTab === 'posts' ? 'active' : ''; ?>"
                       data-type="posts">
                        Publicaciones
                        <?php if ($postCount > 0): ?>
                            <span class="tab-count"><?php echo $postCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/search?q=<?php echo urlencode($query); ?>&type=hashtags" 
                       class="search-tab <?php echo $activeTab === 'hashtags' ? 'active' : ''; ?>"
                       data-type="hashtags">
                        Hashtags
                        <?php if ($hashtagCount > 0): ?>
                            <span class="tab-count"><?php echo $hashtagCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <?php if ($totalResults === 0): ?>
                    <div class="no-results">
                        <div class="no-results-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <h3>No se encontraron resultados</h3>
                        <p>Intenta con otros términos de búsqueda o verifica la ortografía.</p>
                        <a href="/posts" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Ver publicaciones</a>
                    </div>
                <?php else: ?>
                    <div class="search-results-list">
                        <?php
                        // Show mixed results for "Todos" tab
                        if ($activeTab === 'all'):
                            $shownUsers = array_slice($results['users'], 0, 5);
                            $shownPosts = array_slice($results['posts'], 0, 10);
                            $shownHashtags = array_slice($results['hashtags'], 0, 5);

                            // Users section
                            if (!empty($shownUsers)):
                        ?>
                            <div class="search-section">
                                <h3 class="search-section-title">
                                    Usuarios
                                    <?php if (count($results['users']) > 5): ?>
                                        <a href="/search?q=<?php echo urlencode($query); ?>&type=users" class="search-section-link">Ver todos</a>
                                    <?php endif; ?>
                                </h3>
                                <div class="search-users-grid">
                                    <?php foreach ($shownUsers as $user): ?>
                                        <a href="/profile/<?php echo $user['user_id']; ?>" class="search-user-card">
                                            <img src="<?php echo getProfilePicture($user['profile_picture']); ?>" alt="<?php echo safe_output($user['full_name']); ?>" class="search-user-avatar">
                                            <div class="search-user-info">
                                                <span class="search-user-name"><?php echo safe_output($user['full_name']); ?></span>
                                                <?php if (!empty($user['role']) && in_array($user['role'], ['teacher', 'student'])): ?>
                                                    <span class="search-user-role role-badge role-<?php echo $user['role']; ?>">
                                                        <?php echo $user['role'] === 'teacher' ? 'Profesor' : 'Estudiante'; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php
                            endif;

                            // Posts section
                            if (!empty($shownPosts)):
                        ?>
                            <div class="search-section">
                                <h3 class="search-section-title">
                                    Publicaciones
                                    <?php if (count($results['posts']) > 10): ?>
                                        <a href="/search?q=<?php echo urlencode($query); ?>&type=posts" class="search-section-link">Ver todas</a>
                                    <?php endif; ?>
                                </h3>
                                <div class="search-posts-list">
                                    <?php foreach ($shownPosts as $postData):
                                        $postUser = $userModel->getUserById($postData['user_id']);
                                        $hasLiked = $likeModel->hasLiked($postData['post_id'], $currentUserId);
                                        $likesCount = $likeModel->getLikeCount($postData['post_id']);
                                        $commentsCount = $commentModel->getCommentCount($postData['post_id']);
                                        $authorPicture = getProfilePicture($postUser['profile_picture'] ?? '');
                                    ?>
                                        <?php
                                        $postComponent = new Post([
                                            'id' => $postData['post_id'],
                                            'author' => $postUser['full_name'] ?? 'Usuario',
                                            'author_role' => $postUser['role'] ?? 'user',
                                            'date' => date('d/m/Y', strtotime($postData['created_at'])),
                                            'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                                            'image_alt' => 'Imagen del post',
                                            'text' => $postData['content'],
                                            'likes' => $likesCount,
                                            'comments_count' => $commentsCount,
                                            'comments' => [],
                                            'user_id' => $postData['user_id'],
                                            'current_user_id' => $currentUserId,
                                            'has_liked' => $hasLiked,
                                            'user_avatar' => $authorPicture
                                        ]);
                                        echo $postComponent->render();
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php
                            endif;

                            // Hashtags section
                            if (!empty($shownHashtags)):
                        ?>
                            <div class="search-section">
                                <h3 class="search-section-title">
                                    Hashtags
                                    <?php if (count($results['hashtags']) > 5): ?>
                                        <a href="/search?q=<?php echo urlencode($query); ?>&type=hashtags" class="search-section-link">Ver todos</a>
                                    <?php endif; ?>
                                </h3>
                                <div class="search-hashtags-grid">
                                    <?php foreach ($shownHashtags as $hashtag): ?>
                                        <a href="/hashtag/<?php echo urlencode(ltrim($hashtag['name'], '#')); ?>" class="search-hashtag-card">
                                            <span class="search-hashtag-name"><?php echo safe_output($hashtag['name']); ?></span>
                                            <span class="search-hashtag-count"><?php echo $hashtag['post_count']; ?> publicacion<?php echo $hashtag['post_count'] != 1 ? 'es' : ''; ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php
                            endif;

                        // Users tab
                        elseif ($activeTab === 'users'):
                            if (!empty($results['users'])):
                        ?>
                            <div class="search-users-grid full">
                                <?php foreach ($results['users'] as $user): ?>
                                    <a href="/profile/<?php echo $user['user_id']; ?>" class="search-user-card large">
                                        <img src="<?php echo getProfilePicture($user['profile_picture']); ?>" alt="<?php echo safe_output($user['full_name']); ?>" class="search-user-avatar">
                                        <div class="search-user-info">
                                            <span class="search-user-name"><?php echo safe_output($user['full_name']); ?></span>
                                            <?php if (!empty($user['biography'])): ?>
                                                <span class="search-user-bio"><?php echo safe_output($user['biography']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($user['role']) && in_array($user['role'], ['teacher', 'student'])): ?>
                                                <span class="search-user-role role-badge role-<?php echo $user['role']; ?>">
                                                    <?php echo $user['role'] === 'teacher' ? 'Profesor' : 'Estudiante'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php
                            else:
                                echo '<div class="no-results"><p>No se encontraron usuarios.</p></div>';
                            endif;

                        // Posts tab
                        elseif ($activeTab === 'posts'):
                            if (!empty($results['posts'])):
                                foreach ($results['posts'] as $postData):
                                    $postUser = $userModel->getUserById($postData['user_id']);
                                    $hasLiked = $likeModel->hasLiked($postData['post_id'], $currentUserId);
                                    $likesCount = $likeModel->getLikeCount($postData['post_id']);
                                    $commentsCount = $commentModel->getCommentCount($postData['post_id']);
                                    $authorPicture = getProfilePicture($postUser['profile_picture'] ?? '');
                        ?>
                            <?php
                            $postComponent = new Post([
                                'id' => $postData['post_id'],
                                'author' => $postUser['full_name'] ?? 'Usuario',
                                'author_role' => $postUser['role'] ?? 'user',
                                'date' => date('d/m/Y', strtotime($postData['created_at'])),
                                'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                                'image_alt' => 'Imagen del post',
                                'text' => $postData['content'],
                                'likes' => $likesCount,
                                'comments_count' => $commentsCount,
                                'comments' => [],
                                'user_id' => $postData['user_id'],
                                'current_user_id' => $currentUserId,
                                'has_liked' => $hasLiked,
                                'user_avatar' => $authorPicture
                            ]);
                            echo $postComponent->render();
                            ?>
                        <?php
                                endforeach;
                            else:
                                echo '<div class="no-results"><p>No se encontraron publicaciones.</p></div>';
                            endif;

                        // Hashtags tab
                        elseif ($activeTab === 'hashtags'):
                            if (!empty($results['hashtags'])):
                        ?>
                            <div class="search-hashtags-grid full">
                                <?php foreach ($results['hashtags'] as $hashtag): ?>
                                    <a href="/hashtag/<?php echo urlencode(ltrim($hashtag['name'], '#')); ?>" class="search-hashtag-card large">
                                        <span class="search-hashtag-name"><?php echo safe_output($hashtag['name']); ?></span>
                                        <span class="search-hashtag-count"><?php echo $hashtag['post_count']; ?> publicacion<?php echo $hashtag['post_count'] != 1 ? 'es' : ''; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php
                            else:
                                echo '<div class="no-results"><p>No se encontraron hashtags.</p></div>';
                            endif;
                        endif;
                        ?>
                    </div>
                <?php endif; ?>
            </div>
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
