function toggleMenu(event, menuId) {
    event.stopPropagation(); 
    
    const menu = document.getElementById(menuId);
    const allMenus = document.querySelectorAll('.post-menu-modal');
    
    const wasActive = menu.classList.contains('active');

    allMenus.forEach(m => {
        m.classList.remove('active');
    });
    
    if (!wasActive) {
        menu.classList.toggle('active');
    }
}

document.addEventListener('click', function() {
    const allMenus = document.querySelectorAll('.post-menu-modal');
    allMenus.forEach(m => m.classList.remove('active'));
});



const confirmModal = document.getElementById('confirm-delete-modal');
let postParaEliminar = null;

function abrirModalConfirmacion(botonEliminar) {
    postParaEliminar = botonEliminar.closest('.post-card');
    
    if (confirmModal) {
        confirmModal.showModal(); 
    }
}

if (confirmModal) {
    confirmModal.addEventListener('close', function() {
        if (confirmModal.returnValue === 'confirm') {
            if (postParaEliminar) {
                const postId = postParaEliminar.dataset.postId;
                console.log('Eliminando post:', postId);
                
                postParaEliminar.remove();
                
            }
        }
        
        postParaEliminar = null;
    });
}