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

        // Menú solo para propietario
        $menuOptions = '';
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
        }

        return "
        <div class='feed-post-card post-container' data-post-id='{$this->id}'>
            <div class='feed-post-header'>
                <a href='/profile/{$this->userId}' class='feed-post-user'>
                    <div class='feed-post-avatar'></div>
                    <div class='feed-post-user-info'>
                        <h3>{$this->author}</h3>
                        <div class='feed-post-date'>Publicado el: {$this->date}</div>
                    </div>
                </a>
                {$menuOptions}
            </div>

            " . ($this->image ? "<div class='feed-post-image'><img src='{$this->image}' alt='{$this->imageAlt}'></div>" : "") . "

            <p class='feed-post-text'>
                {$this->text}
            </p>

            <div class='feed-post-actions'>
                <button class='{$likeButtonClass}' onclick='handleLike(this)'>
                    <img src='/assets/images/{$heartIcon}' alt='{$heartAlt}' width='25'>
                    {$this->likes} Me gusta
                </button>
                <button class='action-btn comments' onclick='toggleComments(this)'>
                    <img src='/assets/images/comments.png' alt='comments icon' width='25'>
                    {$this->commentsCount} Comentarios
                </button>
            </div>

            {$commentsSection}
        </div>
        ";
    }

    private function renderCommentsSection(): string
    {
        $totalComments = count($this->comments);
        $commentsHtml = '';

        foreach ($this->comments as $index => $comment) {
            $isHidden = $index >= 3 ? ' hidden' : '';
            $author = htmlspecialchars($comment['full_name'] ?? $comment['author']);
            $text = htmlspecialchars($comment['content'] ?? $comment['text']);
            $time = htmlspecialchars($comment['time'] ?? '');
            $date = htmlspecialchars($comment['date'] ?? date('d/m/Y', strtotime($comment['created_at'])));

            $commentsHtml .= "
        <div class='comment{$isHidden}'>
            <div class='comment-header'>
                {$author}: {$text}
            </div>
            <div class='comment-date'>
                {$time} • {$date}
            </div>
        </div>
        ";
        }

        $loadMoreBtn = '';
        if ($totalComments > 3) {
            $loadMoreBtn = "
        <div class='load-more-container'>
            <button class='load-more-btn' onclick='loadMoreComments(this)'>
               Ver más comentarios
            </button>
        </div>
        ";
        }

        return "
    <div class='comments-section hidden'>
        <div class='comments-container'>
            <h4 style='margin-bottom: 15px; font-size: 15px;'>Comentarios ({$this->commentsCount})</h4>
            {$commentsHtml}
            {$loadMoreBtn}
        </div>
        <div class='comment-input-container'>
            <input type='text' class='comment-input' placeholder='Comentar' onkeypress='handleCommentKeyPress(event, this)'>
            <button class='comment-submit' onclick='addComment(this)'>Publicar</button>
        </div>
    </div>
    ";
    }
}
?>