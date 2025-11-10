<?php

$posts = [
    [
        'id' => 1,
        'author' => 'Manuel Orozco',
        'date' => '18/03/2025',
        'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop',
        'image_alt' => 'Paisaje montañoso',
        'text' => 'Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.',
        'likes' => 200,
        'comments_count' => 20,
        'comments' => [
            [
                'author' => 'Gabriel Hernández',
                'text' => 'Eres publicista o dentista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Manuel Orozco',
                'text' => 'Soy publicista o tecnicista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ]
        ]
    ],
    [
        'id' => 2,
        'author' => 'Juanito Alimaña',
        'date' => '10/03/2025',
        'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop',
        'image_alt' => 'Gran Cañón',
        'text' => 'Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.',
        'likes' => 391,
        'comments_count' => 12,
        'comments' => [
            [
                'author' => 'Gabriel Hernández',
                'text' => 'Eres publicista o dentista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Manuel Orozco',
                'text' => 'Soy publicista o tecnicista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Gabriel Hernández',
                'text' => 'Eres publicista o dentista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Manuel Orozco',
                'text' => 'Soy publicista o tecnicista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Gabriel Hernández',
                'text' => 'Eres publicista o dentista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Manuel Orozco',
                'text' => 'Soy publicista o tecnicista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ]
        ]
    ],
    [
        'id' => 3,
        'author' => 'María González',
        'date' => '05/03/2025',
        'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=350&fit=crop',
        'image_alt' => 'Naturaleza',
        'text' => 'Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.',
        'likes' => 95,
        'comments_count' => 12,
        'comments' => [
            [
                'author' => 'Gabriel Hernández',
                'text' => 'Eres publicista o dentista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ],
            [
                'author' => 'Manuel Orozco',
                'text' => 'Soy publicista o tecnicista',
                'time' => 'Hace 2 hrs',
                'date' => '5 de diciembre'
            ]
        ]
    ]
];

function renderPost($post) {
    $menuId = 'menu' . $post['id'];
    ?>
    <div class="feed-post-card post-container" data-post-id="<?php echo $post['id']; ?>">
        <div class="feed-post-header">
            <div class="feed-post-user">
                <div class="feed-post-avatar"></div>
                <div class="feed-post-user-info">
                    <h3><?php echo htmlspecialchars($post['author']); ?></h3>
                    <div class="feed-post-date">Publicado el: <?php echo $post['date']; ?></div>
                </div>
            </div>
            <div class="feed-post-menu" onclick="toggleMenu(event, '<?php echo $menuId; ?>')">
                <img src="/assets/images/more.png" alt="more options" width="25">
                <div class="post-menu-modal" id="<?php echo $menuId; ?>">
                    <div class="menu-option delete" onclick="openConfirmModal(this)">Eliminar</div>
                    <a href="/editPost/<?php echo $post['id']; ?>" class="menu-option">Editar</a>
                    <div class="menu-option">Cancelar</div>
                </div>
            </div>
        </div>

        <div class="feed-post-image">
            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['image_alt']); ?>">
        </div>

        <p class="feed-post-text">
            <?php echo htmlspecialchars($post['text']); ?>
        </p>

        <div class="feed-post-actions">
            <button class="action-btn" onclick="handleLike(this)">
                <img src="/assets/images/heartOutline.png" alt="heart icon" width="25">
                <?php echo $post['likes']; ?> Me gusta
            </button>
            <button class="action-btn comments" onclick="toggleComments(this)">
                <img src="/assets/images/comments.png" alt="comments icon" width="25">
                <?php echo $post['comments_count']; ?> Comentarios
            </button>
        </div>

        <div class="comments-section hidden">
            <h4 style="margin-bottom: 15px; font-size: 15px;">Comentarios</h4>
            
            <?php 
            $totalComments = count($post['comments']);
            foreach ($post['comments'] as $index => $comment): 
                $isHidden = $index >= 3 ? ' hidden' : '';
            ?>
            <div class="comment<?php echo $isHidden; ?>">
                <div class="comment-header">
                    <?php echo htmlspecialchars($comment['author']); ?>: 
                    <?php echo htmlspecialchars($comment['text']); ?>
                </div>
                <div class="comment-date">
                    <?php echo $comment['time']; ?> • <?php echo $comment['date']; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if ($totalComments > 3): ?>
            <div class="load-more-container">
                <button class="load-more-btn" id="loadMoreBtn<?php echo $post['id']; ?>" onclick="loadMoreComments(this)">
                   Ver más comentarios
                </button>
            </div>
            <?php endif; ?>
            
            <div class="comment-input-container">
                <input type="text" class="comment-input" placeholder="Comentar" onkeypress="handleCommentKeyPress(event, this.nextElementSibling)">
                <button class="comment-submit" onclick="addComment(this)">Publicar</button>
            </div>
        </div>
    </div>
    <?php
}


?>