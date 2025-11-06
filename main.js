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


function toggleComments(button) {
    const postCard = button.closest('.post-container');
    const commentsSection = postCard.querySelector('.comments-section');
    
    commentsSection.classList.toggle('hidden');
}

function handleLike(button) {
    const isLiked = button.classList.contains('liked');
    const img = button.querySelector('img');
    const currentCount = parseInt(button.textContent.match(/\d+/)[0]);
    
    if (isLiked) {
        button.classList.remove('liked');
        img.src = '../assets/images/heartOutline.png';
        img.alt = 'Like';
        button.innerHTML = `
            <img src="../assets/images/heartOutline.png" alt="Like" width="25" height="25">
            ${currentCount - 1} Me gusta
        `;
    } else {
        button.classList.add('liked');
        img.src = '../assets/images/heartFilled.png';
        img.alt = 'Liked';
        button.innerHTML = `
            <img src="../assets/images/heartFilled.png" alt="Liked" width="25" height="25">
            ${currentCount + 1} Me gusta
        `;
    }
}

function addComment(button) {
    const postCard = button.closest('.post-container');
    const commentInput = postCard.querySelector('.comment-input');
    const commentText = commentInput.value.trim();
    
    if (!commentText) {
        alert('Por favor escribe un comentario');
        return;
    }
    
    const now = new Date();
    const dateString = now.toLocaleDateString('es-MX', { 
        day: 'numeric', 
        month: 'long' 
    });
    const timeAgo = 'Justo ahora';
    
    const commentHTML = `
        <div class="comment">
            <div class="comment-header">Manuel Orozco: ${escapeHtml(commentText)}</div>
            <div class="comment-date">${timeAgo} • ${dateString}</div>
        </div>
    `;
    
    const commentInputContainer = postCard.querySelector('.comment-input-container');
    commentInputContainer.insertAdjacentHTML('beforebegin', commentHTML);
    
    commentInput.value = '';
    
    const newComment = commentInputContainer.previousElementSibling;
    newComment.style.opacity = '0';
    newComment.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        newComment.style.transition = 'all 0.3s ease';
        newComment.style.opacity = '1';
        newComment.style.transform = 'translateY(0)';
    }, 10);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function handleCommentKeyPress(event, button) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        addComment(button);
    }
}