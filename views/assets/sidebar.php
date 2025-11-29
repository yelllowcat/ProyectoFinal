<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle-btn" id="sidebarToggle">
        <img src="../../assets/images/menu.png" alt="Toggle Menu">
    </button>

    <a href="/">
        <div class="logo-container">
            <img class="logo" src="../assets/images/logoUnired.png" alt="UNIRED Logo">
        </div>
    </a>

    <a href="/addPost" class="menu-item">
        <img src="../assets/images/addPost.png" alt="Nueva publicación" class="menu-icon">
        <span>Nueva publicación</span>
    </a>

    <a href="/posts" class="menu-item <?php echo ($currentPage === 'posts') ? 'active' : ''; ?>">
        <img src="../assets/images/posts.png" alt="Publicaciones" class="menu-icon">
        <span>Publicaciones</span>
    </a>

    <a href="/friends" class="menu-item <?php echo ($currentPage === 'friends') ? 'active' : ''; ?>">
        <img src="../assets/images/friends.png" alt="Amigos" class="menu-icon">
        <span>Amigos</span>
    </a>
    <a href="/profile" class="menu-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>">
        <img src="../assets/images/profile.png" alt="Perfil" class="menu-icon">
        <span>Perfil</span>
    </a>

    <a href="/logout" class="menu-item <?php echo ($currentPage === 'logout') ? 'active' : ''; ?>" id="logout">
        <img src="../assets/images/logout.png" alt="Cerrar sesión" class="menu-icon">
        <span>Cerrar sesión</span>
    </a>
    <dialog id="confirm-logout-modal" class="confirm-dialog" aria-labelledby="confirm-logout-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-logout-title">Confirmar cierre de sesión</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas cerrar sesión?</p>
            </div>
            <div class="confirm-sep"></div>
            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-logout">Cerrar sesión</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>