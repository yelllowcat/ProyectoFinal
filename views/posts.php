<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Publicaciones</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <img src="../assets/images/logoUnired.png" alt="UNIRED Logo">
            </div>
        </div>

        <a href="/addPost" class="menu-item">
            <span class="menu-icon">➕</span>
            <span>Nueva publicación</span>
        </a>

        <a href="/posts" class="menu-item active">
            <span class="menu-icon">📄</span>
            <span>Publicaciones</span>
        </a>
        
        <a href="/friends" class="menu-item">
            <span class="menu-icon">👥</span>
            <span>Amigos</span>
        </a>
        <a href="/profile" class="menu-item">
            <span class="menu-icon">👤</span>
            <span>Perfil</span>
        </a>
        
        <a href="/logout" class="menu-item">
            <span class="menu-icon">🚪</span>
            <span>Cerrar sesión</span>
        </a>
    </div>

    <div class="main-content">
        <div class="feed-container">
            <div class="feed-post-card post-container">
                <div class="feed-post-header">
                    <div class="feed-post-user">
                        <div class="feed-post-avatar"></div>
                        <div class="feed-post-user-info">
                            <h3>Manuel Orozco</h3>
                            <div class="feed-post-date">Publicado el: 18/03/2025</div>
                        </div>
                    </div>
                    <div class="feed-post-menu" onclick="toggleMenu(event, 'menu1')">
                        <img src="/assets/images/more.png" alt="more options" width="25">
                        <div class="post-menu-modal" id="menu1">
                            <div class="menu-option delete" onclick="abrirModalConfirmacion(this)">Eliminar</div>
                            <div class="menu-option">Editar</div>
                            <div class="menu-option">Cancelar</div>
                        </div>
                    </div>
                </div>

                <div class="feed-post-image">
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop" alt="Paisaje montañoso">
                </div>

                <p class="feed-post-text">
                    Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.
                </p>

                <div class="feed-post-actions">
                    <button class="action-btn" onclick="handleLike(this)">
                        <img src="/assets/images/heartOutline.png" alt="heart icon" width="25">
                        200 Me gusta
                    </button>
                    <button class="action-btn comments" onclick="toggleComments(this)">
                        <img src="/assets/images/comments.png" alt="comments icon" width="25">
                        20 Comentarios
                    </button>
                </div>
                <div class="comments-section hidden">
                <h4 style="margin-bottom: 15px; font-size: 15px;">Comentarios</h4>
                
                <div class="comment">
                    <div class="comment-header">Gabriel Hernández: Eres publicista o dentista</div>
                    <div class="comment-date">Hace 2 hrs • 5 de diciembre</div>
                </div>
                
                <div class="comment">
                    <div class="comment-header">Manuel Orozco: Soy publicista o tecnicista</div>
                    <div class="comment-date">Hace 2 hrs • 5 de diciembre</div>
                </div>
                
                <div class="comment-input-container">
                    <input type="text" class="comment-input" placeholder="Comentar" onkeypress="handleCommentKeyPress(event, this.nextElementSibling)">
                    <button class="comment-submit" onclick="addComment(this)">Publicar</button>
                </div>
            </div>                
            </div>
            <div class="feed-post-card post-container">
                <div class="feed-post-header">
                    <div class="feed-post-user">
                        <div class="feed-post-avatar"></div>
                        <div class="feed-post-user-info">
                            <h3>Juanito Alimaña</h3>
                            <div class="feed-post-date">Publicado el: 10/03/2025</div>
                        </div>
                    </div>
                    <div class="feed-post-menu" onclick="toggleMenu(event, 'menu2')">
                        <img src="/assets/images/more.png" alt="more options" width="25">
                        <div class="post-menu-modal" id="menu2">
                            <div class="menu-option delete" onclick="abrirModalConfirmacion(this)">Eliminar</div>
                            <div class="menu-option">Editar</div>
                            <div class="menu-option">Cancelar</div>
                        </div>
                    </div>
                </div>

                <div class="feed-post-image">
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop" alt="Gran Cañón">
                </div>

                <p class="feed-post-text">
                    Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.
                </p>

                <div class="feed-post-actions">
                    <button class="action-btn" onclick="handleLike(this)">
                        <img src="/assets/images/heartOutline.png" alt="heart icon" width="25">
                        391 Me gusta
                    </button>
                    <button class="action-btn comments" onclick="toggleComments(this)">
                        <img src="/assets/images/comments.png" alt="comments icon" width="25">
                        12 Comentarios
                    </button>
                </div>
                 <div class="comments-section hidden">
                <h4 style="margin-bottom: 15px; font-size: 15px;">Comentarios</h4>
                
                <div class="comment">
                    <div class="comment-header">Gabriel Hernández: Eres publicista o dentista</div>
                    <div class="comment-date">Hace 2 hrs • 5 de diciembre</div>
                </div>
                
                <div class="comment">
                    <div class="comment-header">Manuel Orozco: Soy publicista o tecnicista</div>
                    <div class="comment-date">Hace 2 hrs • 5 de diciembre</div>
                </div>
                
                <div class="comment-input-container">
                    <input type="text" class="comment-input" placeholder="Comentar" onkeypress="handleCommentKeyPress(event, this.nextElementSibling)">
                    <button class="comment-submit" onclick="addComment(this)">Publicar</button>
                </div>
            </div>
            </div>

            <div class="feed-post-card post-container">
                <div class="feed-post-header">
                    <div class="feed-post-user">
                        <div class="feed-post-avatar"></div>
                        <div class="feed-post-user-info">
                            <h3>María González</h3>
                            <div class="feed-post-date">Publicado el: 05/03/2025</div>
                        </div>
                    </div>
                    <div class="feed-post-menu" onclick="toggleMenu(event, 'menu3')">
                        <img src="/assets/images/more.png" alt="more options" width="25">
                        <div class="post-menu-modal" id="menu3">
                            <div class="menu-option delete" onclick="abrirModalConfirmacion(this)">Eliminar</div>
                            <div class="menu-option">Editar</div>
                            <div class="menu-option">Cancelar</div>
                        </div>
                    </div>
                </div>

                <div class="feed-post-image">
                    <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=350&fit=crop" alt="Naturaleza">
                </div>

                <p class="feed-post-text">
                    Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.
                </p>

                <div class="feed-post-actions">
                    <button class="action-btn" onclick="handleLike(this)">
                        <img src="/assets/images/heartOutline.png" alt="heart icon" width="25">
                        95 Me gusta
                    </button>
                    <button class="action-btn comments" onclick="toggleComments(this)">
                        <img src="/assets/images/comments.png" alt="comments icon" width="25">
                        12 Comentarios
                    </button>
                </div>
                <div class="comments-section hidden">
                <h4 style="margin-bottom: 15px; font-size: 15px;">Comentarios</h4>
                
                <div class="comment">
                    <div class="comment-header">Gabriel Hernández: Eres publicista o dentista</div>
                    <div class="comment-date">Hace 2 hrs • 5 de diciembre</div>
                </div>
                
                <div class="comment">
                    <div class="comment-header">Manuel Orozco: Soy publicista o tecnicista</div>
                    <div class="comment-date">Hace 2 hrs • 5 de diciembre</div>
                </div>
                
                <div class="comment-input-container">
                    <input type="text" class="comment-input" placeholder="Comentar" onkeypress="handleCommentKeyPress(event, this.nextElementSibling)">
                    <button class="comment-submit" onclick="addComment(this)">Publicar</button>
                </div>
            </div>
            </div>
        </div>
    </div>
    <script src="/main.js"></script>
</body>
</html>