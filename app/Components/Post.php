<?php
namespace App\Components;

class Post
{
    private $id;
    private $author;
    private $date;
    private $image;
    private $imageAlt;
    private $text;
    private $likes;
    private $commentsCount;
    private $comments;
    private $userId;
    private $currentUserId;
    private $data;
    private $userAvatar;
    private $authorRole;
    private $singleMode;
    private $expandAllComments;
    private $highlightCommentId;
    private $highlightReplyId;
    private $repliesData;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->author = htmlspecialchars($data['author']);
        $this->date = htmlspecialchars($data['date']);
        $this->image = htmlspecialchars($data['image']);
        $this->imageAlt = htmlspecialchars($data['image_alt']);
        $this->text = htmlspecialchars($data['text']);
        $this->likes = $data['likes'];
        $this->commentsCount = $data['comments_count'];
        $this->comments = $data['comments'];
        $this->userId = $data['user_id'] ?? null;
        $this->currentUserId = $data['current_user_id'] ?? null;
        $this->data = $data;

        $this->userAvatar = $data['user_avatar'] ?? '/assets/imagesProfile/default_avatar.png';

        if ($this->userAvatar && !str_starts_with($this->userAvatar, '/assets/')) {
            $this->userAvatar = '/assets/imagesProfile/' . $this->userAvatar;
        }

        $this->authorRole = $data['author_role'] ?? 'user';

        $this->singleMode = $data['single_mode'] ?? false;
        $this->expandAllComments = $data['expand_all_comments'] ?? false;
        $this->highlightCommentId = $data['highlight_comment_id'] ?? null;
        $this->highlightReplyId = $data['highlight_reply_id'] ?? null;
        $this->repliesData = $data['replies_data'] ?? [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getImageAlt()
    {
        return $this->imageAlt;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getLikes()
    {
        return $this->likes;
    }

    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function render(): string
    {
        $menuId = 'menu' . $this->id;
        $commentsSection = $this->renderCommentsSection();

        $hasLiked = $this->data['has_liked'] ?? false;

        $heartIcon = $hasLiked ? 'heartFilled.png' : 'heartOutline.png';
        $heartAlt = $hasLiked ? 'Liked' : 'Like';
        $likeButtonClass = $hasLiked ? 'action-btn liked' : 'action-btn';

        // Menú del post
        $menuOptions = '';
        if (!$this->singleMode) {
            if ($this->userId == $this->currentUserId) {
                $menuOptions = "
        <div class='feed-post-menu' onclick=\"toggleMenu(event, '{$menuId}')\">
            <img src='/assets/images/more.png' alt='more options' width='25'>
            <div class='post-menu-modal' id='{$menuId}'>
                <div class='menu-option delete' onclick='openConfirmModal(this)'>Eliminar</div>
                <a href='/editPost/{$this->id}' class='menu-option edit'>Editar</a>
                <div class='menu-option'>Cancelar</div>
            </div>
        </div>
        ";
            } else {
                $menuOptions = "
        <div class='feed-post-menu' onclick=\"toggleMenu(event, '{$menuId}')\">
            <img src='/assets/images/more.png' alt='more options' width='25'>
            <div class='post-menu-modal' id='{$menuId}'>
                <div class='menu-option' style='color: #f44336;' onclick=\"openReportModal('post', {$this->id})\">Reportar publicación</div>
                <div class='menu-option'>Cancelar</div>
            </div>
        </div>
        ";
            }
        }

        $roleBadge = "";
        if ($this->authorRole === 'teacher') {
            $roleBadge = "<span class='role-badge role-teacher'>Profesor</span>";
        } elseif ($this->authorRole === 'student') {
            $roleBadge = "<span class='role-badge role-student'>Estudiante</span>";
        }

        $singleModeClass = $this->singleMode ? ' post-single-mode' : '';
        $actionsHtml = '';
        if (!$this->singleMode) {
            $actionsHtml = "
        <div class='feed-post-actions'>
            <button class='{$likeButtonClass}' onclick='handleLike(this)'>
                <img src='/assets/images/{$heartIcon}' alt='{$heartAlt}' width='25'>
                {$this->likes} Me gusta
            </button>
            <button class='action-btn comments' onclick='toggleComments(this)'>
                <img src='/assets/images/comments.png' alt='comments icon' width='25'>
                {$this->commentsCount} Comentarios
            </button>
        </div>";
        } else {
            $actionsHtml = "
        <div class='feed-post-actions' style='opacity: 0.7;'>
            <span class='action-btn' style='cursor: default;'>
                <img src='/assets/images/{$heartIcon}' alt='Like' width='25'>
                {$this->likes} Me gusta
            </span>
            <span class='action-btn' style='cursor: default;'>
                <img src='/assets/images/comments.png' alt='Comments' width='25'>
                {$this->commentsCount} Comentarios
            </span>
        </div>";
        }

        return "
    <div class='feed-post-card post-container{$singleModeClass}' id='post-{$this->id}' data-post-id='{$this->id}' data-current-user-id='{$this->currentUserId}'>
        <div class='feed-post-header'>
            <a href='/profile/{$this->userId}' class='feed-post-user'>
                <div class='feed-post-avatar'>
                    <img src='{$this->userAvatar}' alt='Avatar de {$this->author}' class='post-user-avatar'>
                </div>
                <div class='feed-post-user-info'>
                    <h3 style='display:flex; align-items:center; gap:5px;'>{$this->author} {$roleBadge}</h3>
                    <div class='feed-post-date'>Publicado el: {$this->date}</div>
                </div>
            </a>
            {$menuOptions}
        </div>

        " . ($this->image ? "<div class='feed-post-image'><img src='{$this->image}' 
        class='post-image-clickable' data-image-url='{$this->image}' alt='{$this->imageAlt}' style='cursor: pointer;'></div>" : "") . "
        <p class='feed-post-text'>
            {$this->text}
        </p>

        {$actionsHtml}

        {$commentsSection}
    </div>
    ";
    }
    private function renderCommentsSection(): string
    {
        $totalComments = count($this->comments);
        $commentsHtml = '';

        foreach ($this->comments as $index => $comment) {
            $isHidden = ($index >= 3 && !$this->expandAllComments) ? ' hidden' : '';
            $author = htmlspecialchars($comment['full_name'] ?? $comment['author']);
            $text = htmlspecialchars($comment['content'] ?? $comment['text']);
            $time = htmlspecialchars($comment['time'] ?? '');
            $date = htmlspecialchars($comment['date'] ?? date('d/m/Y', strtotime($comment['created_at'])));

            $commentId = $comment['comment_id'] ?? $comment['id'] ?? $index;
            $commentMenuId = 'comment-menu-' . $this->id . '-' . $commentId;
            $commentUserId = $comment['user_id'] ?? null;

            $highlightClass = ($this->highlightCommentId && $this->highlightCommentId == $commentId) ? ' highlighted-comment' : '';

            // Menú del comentario
            $commentMenu = '';
            if (!$this->singleMode) {
                if ($commentUserId == $this->currentUserId) {
                    $commentMenu = "
                    <div class='comment-menu-wrapper'>
                        <img src='/assets/images/vertical-dots.png' alt='Opciones de comentario' width='20' 
                             class='comment-menu-trigger' data-menu-id='{$commentMenuId}' style='cursor: pointer;'>
                        <div class='comment-menu-modal' id='{$commentMenuId}'>
                            <div class='menu-option delete comment-delete-btn'>Eliminar</div>
                            <div class='menu-option'>Cancelar</div>
                        </div>
                    </div>";
                } else {
                    $commentMenu = "
                    <div class='comment-menu-wrapper'>
                        <img src='/assets/images/vertical-dots.png' alt='Opciones de comentario' width='20' 
                             class='comment-menu-trigger' data-menu-id='{$commentMenuId}' style='cursor: pointer;'>
                        <div class='comment-menu-modal' id='{$commentMenuId}'>
                            <div class='menu-option' style='color: #f44336;' onclick=\"openReportModal('comment', {$commentId})\">Reportar comentario</div>
                            <div class='menu-option'>Cancelar</div>
                        </div>
                    </div>";
                }
            }

            $replyCount = $comment['reply_count'] ?? 0;
            $replyBtnText = $replyCount > 0 ? "Ver respuestas ({$replyCount})" : 'Responder';

            // Replies: server-side if available, otherwise empty container for JS
            $repliesHtml = '';
            $replySectionClass = 'hidden';
            if (isset($this->repliesData[$commentId]) && !empty($this->repliesData[$commentId])) {
                $replySectionClass = '';
                foreach ($this->repliesData[$commentId] as $reply) {
                    $repliesHtml .= $this->renderReply($reply, $commentId);
                }
            }

            $replyToggleHtml = '';
            $replyInputHtml = '';
            if (!$this->singleMode) {
                $replyToggleHtml = "
                <button class='reply-toggle-btn' onclick='toggleReplies(this)'>
                    {$replyBtnText}
                </button>
                <button class='comment-like-btn' onclick='handleCommentLike(this)' data-comment-id='{$commentId}'>
                    <img src='/assets/images/heartOutline.png' alt='Like' width='14'> 0
                </button>";
                $replyInputHtml = "
                <div class='reply-input-container'>
                    <input type='text' class='reply-input' placeholder='Escribir respuesta...' maxlength='500' minlength='1' onkeypress='handleReplyKeyPress(event, this)'>
                    <button class='reply-submit' onclick='addReply(this)'>Publicar</button>
                </div>";
            } else {
                if ($replyCount > 0) {
                    $replyToggleHtml = "<span class='reply-toggle-btn' style='cursor: default;'>{$replyBtnText}</span>";
                }
            }

            $commentsHtml .= "
        <div class='comment{$isHidden}{$highlightClass}' id='comment-{$commentId}' data-comment-id='{$commentId}'>
            <div class='comment-header'>
            <a href='/profile/{$commentUserId}' class='comment-user'>
                <div class='comment-text-content'>
                    <span class='comment-author-name'>{$author}</span> <span class='comment-text-body'>{$text}</span>
                </div>
            </a>

                {$commentMenu}
            </div>
            <div class='comment-date'>
                {$time} • {$date}
            </div>
            <div class='reply-toggle-container' data-comment-id='{$commentId}'>
                {$replyToggleHtml}
            </div>
            <div class='reply-section {$replySectionClass}' data-comment-id='{$commentId}'>
                <div class='replies-container'>{$repliesHtml}</div>
                {$replyInputHtml}
            </div>
        </div>
        ";
        }
        $loadMoreBtn = '';
        if ($totalComments > 3 && !$this->expandAllComments) {
            $loadMoreBtn = "
        <div class='load-more-container'>
            <button class='load-more-btn' onclick='loadMoreComments(this)'>
               Ver más comentarios
            </button>
        </div>
        ";
        }

        $commentInputHtml = '';
        if (!$this->singleMode) {
            $commentInputHtml = "
        <div class='comment-input-container'>
            <input type='text' class='comment-input' placeholder='Comentar' maxlength='500' minlength='1' onkeypress='handleCommentKeyPress(event, this)'>
            <button class='comment-submit' onclick='addComment(this)'>Publicar</button>
        </div>";
        }

        $sectionClass = $this->singleMode ? '' : ' hidden';

        return "
    <div class='comments-section{$sectionClass}'>
        <div class='comments-container'>
            <h4 style='margin-bottom: 15px; font-size: 15px;'>Comentarios ({$this->commentsCount})</h4>
            {$commentsHtml}
            {$loadMoreBtn}
        </div>
        {$commentInputHtml}
    </div>
    ";
    }

    private function renderReply(array $reply, int $commentId): string
    {
        $replyId = $reply['reply_id'] ?? $reply['id'] ?? 0;
        $author = htmlspecialchars($reply['full_name'] ?? $reply['author'] ?? 'Usuario');
        $text = htmlspecialchars($reply['content'] ?? $reply['text'] ?? '');
        $replyUserId = $reply['user_id'] ?? null;
        $date = date('d/m/Y', strtotime($reply['created_at'] ?? 'now'));
        $time = htmlspecialchars($reply['time'] ?? '');

        $highlightClass = ($this->highlightReplyId && $this->highlightReplyId == $replyId) ? ' highlighted-reply' : '';

        return "
        <div class='reply{$highlightClass}' id='reply-{$replyId}' data-reply-id='{$replyId}'>
            <div class='reply-header'>
                <a href='/profile/{$replyUserId}' class='reply-user'>
                    <div class='reply-text-content'>
                        <span class='reply-author-name'>{$author}</span> <span class='reply-text-body'>{$text}</span>
                    </div>
                </a>
            </div>
            <div class='reply-date'>{$time} • {$date}</div>
        </div>
        ";
    }
}
?>