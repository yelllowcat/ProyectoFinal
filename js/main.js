const COMMENTS_PER_LOAD = 3;

function loadMoreComments(button) {
  const commentsSection = button.closest(".comments-section");
  const commentsContainer = commentsSection.querySelector(".comments-container");
  const comments = commentsContainer.querySelectorAll(".comment.hidden");
  const loadMoreBtn = button;

  let loaded = 0;
  for (let i = 0; i < comments.length && loaded < COMMENTS_PER_LOAD; i++) {
    comments[i].classList.remove("hidden");
    loaded++;
  }

  const remainingComments = commentsContainer.querySelectorAll(".comment.hidden");
  if (remainingComments.length === 0) {
    loadMoreBtn.classList.add("hidden");
  }
}

document.addEventListener("click", function () {
  const allMenus = document.querySelectorAll(".post-menu-modal");
  allMenus.forEach((m) => m.classList.remove("active"));
});

const confirmModal = document.getElementById("confirm-delete-modal");
let postToDelete = null;

function openConfirmModal(deleteButton) {
  console.log(deleteButton);
  postToDelete = deleteButton.closest(".post-container");

  if (confirmModal) {
    try {
      const modalName = confirmModal.querySelector(".friend-name");
      let name = null;
      const friendCard = deleteButton.closest(".friend-card");
      if (friendCard) {
        const nameEl = friendCard.querySelector(".friend-name");
        if (nameEl) name = nameEl.textContent.trim();
      }
      if (!name) {
        const postCard = deleteButton.closest(".post-card");
        if (postCard) {
          const author = postCard.querySelector(".post-user-info h3");
          if (author) name = author.textContent.trim();
        }
      }
      if (modalName) modalName.textContent = name || "[Nombre del amigo]";
    } catch (e) { }

    confirmModal.showModal();
  }
}

if (confirmModal) {
  confirmModal.addEventListener("close", function () {
    if (confirmModal.returnValue === "confirm") {
      if (postToDelete) {
        const postId = postToDelete.dataset.postId;
        console.log("Eliminando post:", postId);

        postToDelete.remove();
      }
    }

    postToDelete = null;
  });
}

async function toggleComments(button) {
  const postContainer = button.closest(".post-container");
  const postId = postContainer.dataset.postId;
  const commentsSection = postContainer.querySelector(".comments-section");

  if (commentsSection.classList.contains("hidden")) {
    await loadComments(postId, postContainer);
  }

  commentsSection.classList.toggle("hidden");
}

async function loadComments(postId, postContainer) {
  try {
    const response = await fetch(`/posts/${postId}/comments`);
    const result = await response.json();

    if (result.success) {
      updateCommentsSection(postContainer, result.data.comments, result.data.comment_count);
    } else {
      console.error('Error al cargar comentarios:', result.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

async function handleLike(button) {

  const postContainer = button.closest('.post-container');
  const postId = postContainer.dataset.postId;
  const isCurrentlyLiked = button.classList.contains("liked");

  try {
    const response = await fetch(`/posts/${postId}/like`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      }
    });

    const result = await response.json();

    if (result.success) {
      let action = result.data.action;
      if (action === undefined) {
        action = isCurrentlyLiked ? 'removed' : 'added';
      }

      const img = button.querySelector("img");

      if (action === 'added') {
        button.classList.add("liked");
        img.src = "/assets/images/heartFilled.png";
        img.alt = "Liked";
      } else {
        button.classList.remove("liked");
        img.src = "/assets/images/heartOutline.png";
        img.alt = "Like";
      }

      button.innerHTML = `
        <img src="${img.src}" alt="${img.alt}" width="25" height="25">
        ${result.data.likes} Me gusta
      `;
    }
  } catch (error) {
    console.error('Fetch error:', error);
  }
}

async function addComment(button) {
  const postContainer = button.closest(".post-container");
  const postId = postContainer.dataset.postId;
  const commentInput = postContainer.querySelector(".comment-input");
  const commentText = commentInput.value.trim();

  if (!commentText) {
    alert("Por favor escribe un comentario");
    return;
  }

  try {
    const response = await fetch(`/posts/${postId}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ comment: commentText })
    });

    const result = await response.json();

    if (result.success) {
      commentInput.value = "";

      updateCommentsSection(postContainer, result.data.comments, result.data.comment_count);

      updateCommentCount(postContainer, result.data.comment_count);

    } else {
      alert('Error al agregar comentario: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error de conexión');
  }
}

function updateCommentsSection(postContainer, comments, commentCount) {
  const commentsSection = postContainer.querySelector(".comments-section");

  let commentsContainer = commentsSection.querySelector('.comments-container');
  if (!commentsContainer) {
    commentsContainer = document.createElement('div');
    commentsContainer.className = 'comments-container';

    const commentInputContainer = commentsSection.querySelector('.comment-input-container');
    commentsSection.insertBefore(commentsContainer, commentInputContainer);
  }

  commentsContainer.innerHTML = '';

  const title = document.createElement('h4');
  title.style.marginBottom = '15px';
  title.style.fontSize = '15px';
  title.textContent = `Comentarios (${commentCount})`;
  commentsContainer.appendChild(title);

  comments.forEach((comment, index) => {
    const isHidden = index >= 3 ? ' hidden' : '';
    const date = new Date(comment.created_at);
    const dateString = date.toLocaleDateString("es-MX", {
      day: "numeric",
      month: "long",
      year: "numeric"
    });
    const timeAgo = getTimeAgo(comment.created_at);

    const commentHTML = `
      <div class="comment${isHidden}">
        <div class="comment-header">${comment.full_name}: ${escapeHtml(comment.content)}</div>
        <div class="comment-date">${timeAgo} • ${dateString}</div>
      </div>
    `;

    commentsContainer.insertAdjacentHTML('beforeend', commentHTML);
  });

  if (comments.length > 3) {
    const loadMoreContainer = document.createElement('div');
    loadMoreContainer.className = 'load-more-container';
    loadMoreContainer.innerHTML = `
      <button class="load-more-btn" onclick="loadMoreComments(this)">
        Ver más comentarios
      </button>
    `;
    commentsContainer.appendChild(loadMoreContainer);
  }

  const newComments = commentsContainer.querySelectorAll('.comment');
  if (newComments.length > 0) {
    const lastComment = newComments[newComments.length - 1];
    lastComment.style.opacity = "0";
    lastComment.style.transform = "translateY(-10px)";
    setTimeout(() => {
      lastComment.style.transition = "all 0.3s ease";
      lastComment.style.opacity = "1";
      lastComment.style.transform = "translateY(0)";
    }, 10);
  }
}

function updateCommentCount(postContainer, commentCount) {
  const commentsButton = postContainer.querySelector(".action-btn.comments");
  commentsButton.innerHTML = `
    <img src='/assets/images/comments.png' alt='comments icon' width='25'>
    ${commentCount} Comentarios
  `;
}

function getTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Justo ahora';
  if (diffMins < 60) return `Hace ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
  if (diffHours < 24) return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
  if (diffDays < 7) return `Hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;

  return date.toLocaleDateString('es-MX');
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function handleCommentKeyPress(event, button) {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    addComment(button);
  }
}

function updateCounter() {
  const textarea = document.getElementById("postText");
  const counter = document.getElementById("charCount");
  counter.textContent = textarea.value.length;
}
function fetchUsersWithMostPosts(event) {
  console.log("Fetching users with most posts...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchUsersWithMostFriends(event) {
  console.log("Fetching users with most friends...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchPostsWithMostComments(event) {
  console.log("Fetching posts with most comments...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchPostsWithMostLikes(event) {
  console.log("Fetching posts with most likes...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchFriends(event) {
  console.log("Fetching all friends...");
  const tabs = document.querySelectorAll(".tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchFriendRequests(event) {
  console.log("Fetching friend requests...");
  const tabs = document.querySelectorAll(".tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchSendRequests(event) {
  console.log("Fetching sent friend requests...");
  const tabs = document.querySelectorAll(".tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}
console.log("Main.js loaded");
