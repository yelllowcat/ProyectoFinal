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

        <a href="/posts" class="menu-item <?php echo ($currentPage === 'posts') ? 'active' : ''; ?>">
            <span class="menu-icon">📄</span>
            <span>Publicaciones</span>
        </a>
        
        <a href="/friends" class="menu-item <?php echo ($currentPage === 'friends') ? 'active' : ''; ?>">
            <span class="menu-icon">👥</span>
            <span>Amigos</span>
        </a>
        <a href="/profile" class="menu-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>">
            <span class="menu-icon">👤</span>
            <span>Perfil</span>
        </a>
        
        <a href="/logout" class="menu-item <?php echo ($currentPage === 'logout') ? 'active' : ''; ?>">
            <span class="menu-icon">🚪</span>
            <span>Cerrar sesión</span>
        </a>
    </div>