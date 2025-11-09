<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Perfil de Usuario</title>
   <link rel="stylesheet" href="/assets/styles/styles.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <img src="/assets/images/logoUnired.png" alt="UNIRED Logo">
            </div>
        </div>
        
    <div class="menu-item">
            <span class="menu-icon">➕</span>
            <span>Nueva publicación</span>
        </div>
        
        <a href="/posts" class="menu-item">
            <span class="menu-icon">📄</span>
            <span>Publicaciones</span>
        </a>
        
        <a href="/friends" class="menu-item">
            <span class="menu-icon">👥</span>
            <span>Amigos</span>
        </a>
        
        <div class="menu-item active">
            <span class="menu-icon">👤</span>
            <span>Perfil</span>
        </div>
        
        <div class="menu-item">
            <span class="menu-icon">🚪</span>
            <span>Cerrar sesión</span>
        </div>
    </div>

    <div class="main-content">
        <div class="content-wrapper">
            <div class="profile-section">
            <div class="profile-photo"></div>
            <h2 class="profile-name">Manuel Orozco</h2>
            <p class="profile-bio">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.
            </p>
            
            <div class="stats-container">
                <div class="stat">
                    <div class="stat-number">2,100</div>
                    <div class="stat-label">Amigos</div>
                </div>
                <div class="stat">
                    <div class="stat-number">187</div>
                    <div class="stat-label">Me gusta</div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="/editProfile">
                    <button class="btn btn-primary">Editar perfil</button>
                </a>
            </div>
        </div>

        <div class="post-card post-container" data-post-id="post-1">
            <div class="post-header">
                <div class="post-user">
                    <div class="post-avatar"></div>
                    <div class="post-user-info">
                        <h3>Manuel Orozco</h3>
                        <div class="post-date">Publicado el: 12/03/2025</div>
                    </div>
                </div>
                <div class="post-menu" onclick="toggleMenu(event, 'menu1')">
                    <img src="/assets/images/more.png" alt="more options" width="25">
                    <div class="post-menu-modal" id="menu1">
                        <div class="menu-option delete" onclick="openConfirmModal(this)">Eliminar</div>
                        <div class="menu-option">Editar</div>
                        <div class="menu-option">Cancelar</div>
                    </div>
                </div>
            </div>
            
            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&h=300&fit=crop" alt="Paisaje" class="post-image">
            
            <p class="post-text">
                Dento habitasse dictum tristique masuada eius. Ut rutrum erat eget lorem velit. Eget lorem velit rutrum amet non est. Quisque que amet tellus aliquam euismod. Consectetur suscipit suscipit sed tempor bibendum volutpat magna amet arci accumsan. Vulputate pede que amet arci suscipit sed.
            </p>
            
            <div class="post-actions">
                <button class="action-btn" onclick="handleLike(this)">
                   <img src="/assets/images/heartOutline.png" alt="heart icon" width="25">   
                245 Me gusta</button>
                <button class="action-btn comments" onclick="toggleComments(this)">
                    <img src="/assets/images/comments.png" alt="comments icon" width="25">
                    12 comentarios
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

        <div class="post-card post-container" data-post-id="post-2">
            <div class="post-header">
                <div class="post-user">
                    <div class="post-avatar"></div>
                    <div class="post-user-info">
                        <h3>Manuel Orozco</h3>
                        <div class="post-date">Publicado el: 7/2/2025</div>
                    </div>
                </div>
                <div class="post-menu" onclick="toggleMenu(event, 'menu2')">
                    <img src="/assets/images/more.png" alt="more options" width="25">
                    <div class="post-menu-modal" id="menu2">
                        <div class="menu-option delete" onclick="openConfirmModal(this)">Eliminar</div>
                        <div class="menu-option" >Editar</div>
                        <div class="menu-option">Cancelar</div>
                    </div>
                </div>
            </div>
            
            <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&h=300&fit=crop" alt="Motocicleta" class="post-image">
            
            <p class="post-text">
                Dento habitasse dictum tristique masuada eius. Ut rutrum erat eget lorem velit. Eget lorem velit rutrum amet non est. Quisque que amet tellus aliquam euismod. Consectetur suscipit suscipit sed tempor bibendum volutpat magna amet arci accumsan. Vulputate pede que amet arci suscipit sed.
            </p>
            
            <div class="post-actions">
                <button class="action-btn" onclick="handleLike(this)">
                    <img src="/assets/images/heartOutline.png" alt="heart icon" width="25">
                    391 Me gusta
                </button>
                <button class="action-btn comments" onclick="toggleComments(this)">
                    <img src="/assets/images/comments.png" alt="comments icon" width="25">
                    4 Comentarios
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
    <dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar a <span class="friend-name">[Nombre del amigo]</span>?</p>
            </div>

            <div class="confirm-sep"></div>

            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-delete">Eliminar</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>
  <script src="../js/main.js"></script>
</body>
</html>