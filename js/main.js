const COMMENTS_PER_LOAD = 3;

async function toggleMenu(event, menuId) {
  event.stopPropagation();

  const menu = document.getElementById(menuId);
  const allMenus = document.querySelectorAll(".post-menu-modal");

  const wasActive = menu.classList.contains("active");

  allMenus.forEach((m) => {
    m.classList.remove("active");
  });

  if (!wasActive) {
    menu.classList.add("active");
  }
}

function loadMoreComments(button) {
  const commentsSection = button.closest(".comments-section");
  const commentsContainer = commentsSection.querySelector(
    ".comments-container"
  );
  const comments = commentsContainer.querySelectorAll(".comment.hidden");
  const loadMoreBtn = button;

  let loaded = 0;
  for (let i = 0; i < comments.length && loaded < COMMENTS_PER_LOAD; i++) {
    comments[i].classList.remove("hidden");
    loaded++;
  }

  const remainingComments =
    commentsContainer.querySelectorAll(".comment.hidden");
  if (remainingComments.length === 0) {
    loadMoreBtn.classList.add("hidden");
  }
}

function toggleCommentMenu(event, menuId) {
  event.stopPropagation();

  const menu = document.getElementById(menuId);
  const allCommentMenus = document.querySelectorAll(".comment-menu-modal");

  const wasActive = menu.classList.contains("active");

  allCommentMenus.forEach((m) => {
    m.classList.remove("active");
  });

  if (!wasActive) {
    menu.classList.add("active");
  }
}

function toggleReplyMenu(event, menuId) {
  event.stopPropagation();

  const menu = document.getElementById(menuId);
  const allReplyMenus = document.querySelectorAll(".reply-menu-modal");

  const wasActive = menu.classList.contains("active");

  allReplyMenus.forEach((m) => {
    m.classList.remove("active");
  });

  if (!wasActive) {
    menu.classList.add("active");
  }
}

function openDeleteCommentModal(deleteButton) {
  commentToDelete = deleteButton.closest(".comment");
  postToDelete = null;
  replyToDelete = null;

  if (confirmModal) {
    try {
      const modalSubtitle = confirmModal.querySelector(".confirm-subtitle");
      if (modalSubtitle) {
        modalSubtitle.textContent = "¿Estás seguro/a de que deseas eliminar este comentario?";
      }
    } catch (e) { }

    confirmModal.showModal();
  }
}

function openDeleteReplyModal(deleteButton) {
  replyToDelete = deleteButton.closest(".reply");
  postToDelete = null;
  commentToDelete = null;

  if (confirmModal) {
    try {
      const modalSubtitle = confirmModal.querySelector(".confirm-subtitle");
      if (modalSubtitle) {
        modalSubtitle.textContent = "¿Estás seguro/a de que deseas eliminar esta respuesta?";
      }
    } catch (e) { }

    confirmModal.showModal();
  }
}

document.addEventListener("click", function () {
  const allMenus = document.querySelectorAll(".post-menu-modal");
  allMenus.forEach((m) => m.classList.remove("active"));

  const allCommentMenus = document.querySelectorAll(".comment-menu-modal");
  allCommentMenus.forEach((m) => m.classList.remove("active"));

  const allReplyMenus = document.querySelectorAll(".reply-menu-modal");
  allReplyMenus.forEach((m) => m.classList.remove("active"));
});

let confirmModal = null;
let postToDelete = null;
let commentToDelete = null;
let replyToDelete = null;

document.addEventListener("DOMContentLoaded", function () {
  confirmModal = document.getElementById("confirm-delete-modal");

  if (confirmModal) {
    confirmModal.addEventListener("close", async function () {
      if (confirmModal.returnValue === "confirm") {
        if (postToDelete) {
          const postId = postToDelete.dataset.postId;
          console.log("Eliminando post:", postId);

          try {
            const response = await fetch(`/posts/${postId}`, {
              method: "DELETE",
              headers: { "Content-Type": "application/json" },
            });

            const result = await response.json();

            if (result.success) {
              postToDelete.remove();
            } else {
              alert("Error al eliminar la publicación: " + result.message);
            }
          } catch (error) {
            console.error("Error:", error);
            alert("Error de conexión al eliminar la publicación");
          }
        }

        if (commentToDelete) {
          const commentId = commentToDelete.dataset.commentId;
          const postContainer = commentToDelete.closest(".post-container");

          try {
            const response = await fetch(`/comments/${commentId}`, {
              method: "DELETE",
              headers: { "Content-Type": "application/json" },
            });

            const result = await response.json();

            if (result.success) {
              commentToDelete.remove();

              if (postContainer && result.data?.comment_count !== undefined) {
                const commentsButton = postContainer.querySelector(".action-btn.comments");
                if (commentsButton) {
                  commentsButton.innerHTML = `
                    <img src='/assets/images/comments.png' alt='comments icon' width='25'>
                    ${result.data.comment_count} Comentarios
                  `;
                }
              }
            } else {
              alert("Error al eliminar el comentario: " + result.message);
            }
          } catch (error) {
            console.error("Error:", error);
            alert("Error de conexión al eliminar el comentario");
          }
        }

        if (replyToDelete) {
          const replyId = replyToDelete.dataset.replyId;
          const commentEl = replyToDelete.closest(".comment");

          try {
            const response = await fetch(`/replies/${replyId}`, {
              method: "DELETE",
              headers: { "Content-Type": "application/json" },
            });

            const result = await response.json();

            if (result.success) {
              replyToDelete.remove();

              if (commentEl && result.data?.reply_count !== undefined) {
                const toggleBtn = commentEl.querySelector(".reply-toggle-btn");
                if (toggleBtn) {
                  const count = result.data.reply_count;
                  toggleBtn.textContent = count > 0 ? `Ver respuestas (${count})` : 'Responder';
                }
              }
            } else {
              alert("Error al eliminar la respuesta: " + result.message);
            }
          } catch (error) {
            console.error("Error:", error);
            alert("Error de conexión al eliminar la respuesta");
          }
        }
      }

      postToDelete = null;
      commentToDelete = null;
      replyToDelete = null;
    });
  }
});

function openConfirmModal(deleteButton) {
  console.log(deleteButton);
  postToDelete = deleteButton.closest(".post-container");
  commentToDelete = null;

  if (confirmModal) {
    try {
      const modalSubtitle = confirmModal.querySelector(".confirm-subtitle");
      if (modalSubtitle) {
        modalSubtitle.textContent = "¿Estás seguro/a de que deseas eliminar esta publicación?";
      }

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
      updateCommentsSection(
        postContainer,
        result.data.comments,
        result.data.comment_count
      );
    } else {
      console.error("Error al cargar comentarios:", result.message);
    }
  } catch (error) {
    console.error("Error:", error);
  }
}

async function handleLike(button) {
  const postContainer = button.closest(".post-container");
  const postId = postContainer.dataset.postId;
  const isCurrentlyLiked = button.classList.contains("liked");

  try {
    const response = await fetch(`/posts/${postId}/like`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
    });

    const result = await response.json();

    if (result.success) {
      let action = result.data.action;
      if (action === undefined) {
        action = isCurrentlyLiked ? "removed" : "added";
      }

      const img = button.querySelector("img");

      if (action === "added") {
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
    console.error("Fetch error:", error);
  }
}

async function addComment(button) {
  const postContainer = button.closest(".post-container");
  const postId = postContainer.dataset.postId;
  const commentInput = postContainer.querySelector(".comment-input");
  const commentText = commentInput.value.trim();

  if (!commentText) {
    return;
  }

  try {
    const response = await fetch(`/posts/${postId}/comments`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ comment: commentText }),
    });

    const result = await response.json();

    if (result.success) {
      commentInput.value = "";

      updateCommentsSection(
        postContainer,
        result.data.comments,
        result.data.comment_count,
        true
      );

      updateCommentCount(postContainer, result.data.comment_count);
    } else {
      alert("Error al agregar comentario: " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Error de conexión");
  }
}

function updateCommentsSection(postContainer, comments, commentCount, isAddingComment = false) {
  const commentsSection = postContainer.querySelector(".comments-section");

  let commentsContainer = commentsSection.querySelector(".comments-container");
  if (!commentsContainer) {
    commentsContainer = document.createElement("div");
    commentsContainer.className = "comments-container";

    const commentInputContainer = commentsSection.querySelector(
      ".comment-input-container"
    );
    commentsSection.insertBefore(commentsContainer, commentInputContainer);
  }

  commentsContainer.innerHTML = "";

  const title = document.createElement("h4");
  title.style.marginBottom = "15px";
  title.style.fontSize = "15px";
  title.textContent = `Comentarios (${commentCount})`;
  commentsContainer.appendChild(title);

  comments.forEach((comment, index) => {

    const isHidden = !isAddingComment && index >= 3 ? " hidden" : "";
    const date = new Date(comment.created_at);
    const dateString = date.toLocaleDateString("es-MX", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
    const timeAgo = getTimeAgo(comment.created_at);
    const postId = postContainer.dataset.postId;
    const commentId = comment.comment_id || comment.id;
    const commentMenuId = `comment-menu-${postId}-${commentId}`;
    const currentUserId = postContainer.dataset.currentUserId;
    const commentUserId = comment.user_id;

    let commentMenuHTML = '';
    if (currentUserId && commentUserId && currentUserId == commentUserId) {
      commentMenuHTML = `
        <div class="comment-menu-wrapper">
          <img src="/assets/images/vertical-dots.png" alt="Opciones de comentario" width="20" 
               class="comment-menu-trigger" data-menu-id="${commentMenuId}" style="cursor: pointer;">
          <div class="comment-menu-modal" id="${commentMenuId}">
            <div class="menu-option delete comment-delete-btn">Eliminar</div>
            <div class="menu-option">Cancelar</div>
          </div>
        </div>
      `;
    }

    const replyCount = comment.reply_count || 0;
    const replyBtnText = replyCount > 0 ? `Ver respuestas (${replyCount})` : 'Responder';

    const commentHTML = `
      <div class="comment${isHidden}" data-comment-id="${commentId}">
        <div class="comment-header">
          <a href='/profile/${commentUserId}' class='comment-user'>
            <div class="comment-text-content">
              <span class="comment-author-name">${escapeHtml(comment.full_name)}</span> <span class="comment-text-body">${escapeHtml(comment.content)}</span>
            </div>
          </a>
          ${commentMenuHTML}
        </div>
        <div class="comment-date">${timeAgo} • ${dateString}</div>
        <div class="reply-toggle-container" data-comment-id="${commentId}">
          <button class="reply-toggle-btn" onclick="toggleReplies(this)">
            ${replyBtnText}
          </button>
          <button class="comment-like-btn" onclick="handleCommentLike(this)" data-comment-id="${commentId}">
            <img src="/assets/images/heartOutline.png" alt="Like" width="14"> 0
          </button>
        </div>
        <div class="reply-section hidden" data-comment-id="${commentId}">
          <div class="replies-container"></div>
          <div class="reply-input-container">
            <input type="text" class="reply-input" placeholder="Escribir respuesta..." maxlength="500" minlength="1" onkeypress="handleReplyKeyPress(event, this)">
            <button class="reply-submit" onclick="addReply(this)">Publicar</button>
          </div>
        </div>
      </div>
    `;

    commentsContainer.insertAdjacentHTML("beforeend", commentHTML);
  });

  if (!isAddingComment && comments.length > 3) {
    const loadMoreContainer = document.createElement("div");
    loadMoreContainer.className = "load-more-container";
    loadMoreContainer.innerHTML = `
      <button class="load-more-btn" onclick="loadMoreComments(this)">
        Ver más comentarios
      </button>
    `;
    commentsContainer.appendChild(loadMoreContainer);
  }

  const newComments = commentsContainer.querySelectorAll(".comment");
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

  if (diffMins < 1) return "Justo ahora";
  if (diffMins < 60) return `Hace ${diffMins} minuto${diffMins > 1 ? "s" : ""}`;
  if (diffHours < 24)
    return `Hace ${diffHours} hora${diffHours > 1 ? "s" : ""}`;
  if (diffDays < 7) return `Hace ${diffDays} día${diffDays > 1 ? "s" : ""}`;

  return date.toLocaleDateString("es-MX");
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

function handleReplyKeyPress(event, input) {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    addReply(input);
  }
}

async function handleCommentLike(button) {
  const commentId = button.dataset.commentId;
  const isCurrentlyLiked = button.classList.contains("liked");

  try {
    const response = await fetch(`/comments/${commentId}/like`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
    });

    const result = await response.json();

    if (result.success) {
      let action = result.data.action;
      if (action === undefined) {
        action = isCurrentlyLiked ? "removed" : "added";
      }

      const img = button.querySelector("img");

      if (action === "added") {
        button.classList.add("liked");
        img.src = "/assets/images/heartFilled.png";
        img.alt = "Liked";
      } else {
        button.classList.remove("liked");
        img.src = "/assets/images/heartOutline.png";
        img.alt = "Like";
      }

      button.innerHTML = `
        <img src="${img.src}" alt="${img.alt}" width="14">
        ${result.data.likes}
      `;
    }
  } catch (error) {
    console.error("Error en like de comentario:", error);
  }
}

async function handleReplyLike(button) {
  const replyId = button.dataset.replyId;
  const isCurrentlyLiked = button.classList.contains("liked");

  try {
    const response = await fetch(`/replies/${replyId}/like`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
    });

    const result = await response.json();

    if (result.success) {
      let action = result.data.action;
      if (action === undefined) {
        action = isCurrentlyLiked ? "removed" : "added";
      }

      const img = button.querySelector("img");

      if (action === "added") {
        button.classList.add("liked");
        img.src = "/assets/images/heartFilled.png";
        img.alt = "Liked";
      } else {
        button.classList.remove("liked");
        img.src = "/assets/images/heartOutline.png";
        img.alt = "Like";
      }

      button.innerHTML = `
        <img src="${img.src}" alt="${img.alt}" width="12">
        ${result.data.likes}
      `;
    }
  } catch (error) {
    console.error("Error en like de respuesta:", error);
  }
}

async function toggleReplies(button) {
  const commentEl = button.closest(".comment");
  const commentId = commentEl.dataset.commentId;
  const replySection = commentEl.querySelector(".reply-section");

  if (replySection.classList.contains("hidden")) {
    try {
      const response = await fetch(`/comments/${commentId}/replies`);
      const result = await response.json();

      if (result.success) {
        const replies = result.data.replies;
        const replyCount = result.data.reply_count;

        renderRepliesSection(commentEl, replies, replyCount);

        if (replyCount > 0) {
          button.textContent = `Ver respuestas (${replyCount})`;
        } else {
          button.textContent = 'Responder';
        }
      }
    } catch (error) {
      console.error("Error al cargar respuestas:", error);
    }
  }

  replySection.classList.toggle("hidden");
}

function renderRepliesSection(commentEl, replies, replyCount) {
  const repliesContainer = commentEl.querySelector(".replies-container");
  const postContainer = commentEl.closest(".post-container");
  const currentUserId = postContainer ? postContainer.dataset.currentUserId : null;

  repliesContainer.innerHTML = "";

  replies.forEach(reply => {
    const replyId = reply.reply_id || reply.id;
    const replyUserId = reply.user_id;
    const date = new Date(reply.created_at);
    const dateString = date.toLocaleDateString("es-MX", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
    const timeAgo = getTimeAgo(reply.created_at);

    let replyMenuHTML = '';
    if (currentUserId && replyUserId && currentUserId == replyUserId) {
      const replyMenuId = `reply-menu-${replyId}`;
      replyMenuHTML = `
        <div class="reply-menu-wrapper">
          <img src="/assets/images/vertical-dots.png" alt="Opciones de respuesta" width="16"
               class="reply-menu-trigger" data-menu-id="${replyMenuId}" style="cursor: pointer;">
          <div class="reply-menu-modal" id="${replyMenuId}">
            <div class="menu-option delete reply-delete-btn">Eliminar</div>
            <div class="menu-option">Cancelar</div>
          </div>
        </div>
      `;
    }

    const replyHTML = `
      <div class="reply" data-reply-id="${replyId}">
        <div class="reply-header">
          <a href="/profile/${replyUserId}" class="reply-user">
            <div class="reply-text-content">
              <span class="reply-author-name">${escapeHtml(reply.full_name)}</span> <span class="reply-text-body">${escapeHtml(reply.content)}</span>
            </div>
          </a>
          ${replyMenuHTML}
        </div>
        <div class="reply-date">${timeAgo} • ${dateString}</div>
        <div class="reply-like-container">
          <button class="reply-like-btn" onclick="handleReplyLike(this)" data-reply-id="${replyId}">
            <img src="/assets/images/heartOutline.png" alt="Like" width="12"> 0
          </button>
        </div>
      </div>
    `;

    repliesContainer.insertAdjacentHTML("beforeend", replyHTML);
  });
}

async function addReply(button) {
  const commentEl = button.closest(".comment");
  const commentId = commentEl.dataset.commentId;
  const replyInput = commentEl.querySelector(".reply-input");
  const replyText = replyInput.value.trim();

  if (!replyText) {
    return;
  }

  try {
    const response = await fetch(`/comments/${commentId}/replies`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ reply: replyText }),
    });

    const result = await response.json();

    if (result.success) {
      replyInput.value = "";

      renderRepliesSection(commentEl, result.data.replies, result.data.reply_count);

      const toggleBtn = commentEl.querySelector(".reply-toggle-btn");
      if (toggleBtn && result.data.reply_count > 0) {
        toggleBtn.textContent = `Ver respuestas (${result.data.reply_count})`;
      }
    } else {
      alert("Error al agregar respuesta: " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Error de conexión");
  }
}

function updateCounter() {
  const textarea = document.getElementById("postText");
  const counter = document.getElementById("charCount");
  counter.textContent = textarea.value.length;
}

// imagen

function handleImageSelect(event) {
  const file = event.target.files[0];
  const preview = document.getElementById("imagePreview");
  const previewImage = document.getElementById("previewImage");
  const addImageSection = document.querySelector(".add-post-image-section");

  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert("La imagen no puede ser mayor a 5MB");
      event.target.value = "";
      return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/jpg"];
    if (!allowedTypes.includes(file.type)) {
      alert("Solo se permiten imágenes JPEG, PNG y GIF");
      event.target.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      previewImage.src = e.target.result;
      preview.style.display = "block";
      addImageSection.style.display = "none";
    };
    reader.readAsDataURL(file);
  }
}

function removeImage() {
  const fileInput = document.getElementById("post_image");
  const preview = document.getElementById("imagePreview");
  const addImageSection = document.querySelector(".add-post-image-section");

  fileInput.value = "";
  preview.style.display = "none";
  addImageSection.style.display = "flex";
}

function handleProfileImageSelect(event) {
  const file = event.target.files[0];
  const preview = document.getElementById("profilePreview");
  const previewImage = document.getElementById("previewProfileImage");
  const currentImage = document.getElementById("currentProfileImage");

  console.log(file);
  console.log(preview);
  console.log(previewImage);
  console.log(currentImage);

  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert("La imagen no puede ser mayor a 5MB");
      event.target.value = "";
      return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
    if (!allowedTypes.includes(file.type)) {
      alert("Solo se permiten imágenes JPEG y PNG");
      event.target.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      previewImage.src = e.target.result;
      previewImage.style.display = "block";
      preview.style.display = "block";
      currentImage.style.display = "none";
    };
    reader.readAsDataURL(file);
  }
}


//update post

async function deletePost(postId) {
  if (!confirm("¿Estás seguro de que deseas eliminar esta publicación?"))
    return;

  try {
    const response = await fetch("/posts/" + postId, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
    });

    const result = await response.json();

    if (result.success) {
      alert("Publicación eliminada correctamente");
      window.location.href = "/posts";
    } else {
      alert("Error: " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Error de conexión");
  }
}

//sidebar
document.addEventListener("DOMContentLoaded", function () {
  const btnSidebar = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");
  const overlay = document.getElementById("sidebarOverlay");

  function initSidebar() {
    if (window.innerWidth <= 768) {
      if (sidebar) sidebar.classList.add("sidebar-hidden");
      if (mainContent) mainContent.classList.add("sidebar-hidden");
      if (overlay) overlay.classList.remove("active");
      document.body.style.overflow = "";
    }
  }

  initSidebar();

  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth > 768) {
        if (sidebar) sidebar.classList.remove("sidebar-hidden");
        if (mainContent) mainContent.classList.remove("sidebar-hidden");
        if (overlay) overlay.classList.remove("active");
        document.body.style.overflow = "";
      } else {
        initSidebar();
      }
    }, 250);
  });

  if (btnSidebar && sidebar && mainContent) {
    btnSidebar.addEventListener("click", function () {
      const isHidden = sidebar.classList.toggle("sidebar-hidden");
      mainContent.classList.toggle("sidebar-hidden");

      if (overlay) {
        overlay.classList.toggle("active");

        if (window.innerWidth <= 768) {
          if (!isHidden) {
            document.body.style.overflow = "hidden";
          } else {
            document.body.style.overflow = "";
          }
        }
      }
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      if (sidebar && mainContent) {
        sidebar.classList.add("sidebar-hidden");
        mainContent.classList.add("sidebar-hidden");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  setupDragAndDrop();
});

function setupDragAndDrop() {
  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("post_image");

  if (!dropZone || !fileInput) return;

  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  ["dragenter", "dragover"].forEach((eventName) => {
    dropZone.addEventListener(eventName, highlight, false);
  });

  ["dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, unhighlight, false);
  });

  function highlight(e) {
    dropZone.classList.add("dragover");
  }

  function unhighlight(e) {
    dropZone.classList.remove("dragover");
  }

  dropZone.addEventListener("drop", handleDrop, false);

  function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    if (files.length > 0) {
      fileInput.files = files;
      const event = new Event("change", { bubbles: true });
      fileInput.dispatchEvent(event);
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', function (event) {
    if (event.target && event.target.classList.contains('profile-action-btn')) {
      const button = event.target;
      const action = button.dataset.action;
      const userId = button.dataset.userId;

      if (!userId || !action) return;

      handleProfileFriendAction(action, userId, button);
    }
  });
});

async function handleProfileFriendAction(action, userId, button) {
  const endpoints = {
    'add': `/friend/request/${userId}`,
    'accept': `/friend/acceptUser/${userId}`,
    'reject': `/friend/cancelUser/${userId}`,
    'remove': `/friend/remove/${userId}`
  };

  const messages = {
    'add': 'Enviando solicitud...',
    'accept': 'Aceptando solicitud...',
    'reject': 'Cancelando solicitud...',
    'remove': 'Eliminando amistad...'
  };

  const endpoint = endpoints[action];
  if (!endpoint) return;

  button.disabled = true;
  const originalText = button.textContent;
  button.textContent = messages[action];

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    });

    const data = await response.json();

    if (data.success) {
      window.location.reload();
    } else {
      alert('Error: ' + (data.message || 'Acción fallida'));
      button.disabled = false;
      button.textContent = originalText;
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Ocurrió un error al procesar la solicitud');
    button.disabled = false;
    button.textContent = originalText;
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const logoutLink = document.getElementById("logout");
  const logoutModal = document.getElementById("confirm-logout-modal");

  if (logoutLink && logoutModal) {
    logoutLink.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      logoutModal.showModal();
    });

    logoutModal.addEventListener("close", function () {
      if (logoutModal.returnValue === "confirm") {
        window.location.href = "/logout";
      }
    });
  }
});

document.addEventListener("click", function (event) {
  if (event.target.classList.contains("comment-menu-trigger")) {
    const menuId = event.target.dataset.menuId;
    toggleCommentMenu(event, menuId);
  }

  if (event.target.classList.contains("comment-delete-btn")) {
    openDeleteCommentModal(event.target);
  }

  if (event.target.classList.contains("reply-menu-trigger")) {
    const menuId = event.target.dataset.menuId;
    toggleReplyMenu(event, menuId);
  }

  if (event.target.classList.contains("reply-delete-btn")) {
    openDeleteReplyModal(event.target);
  }
});

document.addEventListener("click", function (event) {
  if (event.target.classList.contains("post-image-clickable")) {
    const imageUrl = event.target.dataset.imageUrl;
    openImageModal(imageUrl);
  }
});

function openImageModal(imageUrl) {
  const modal = document.getElementById("image-modal");
  const modalImage = document.getElementById("modalImage");

  if (modal && modalImage) {
    modalImage.src = imageUrl;
    modal.showModal();
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const imageModal = document.getElementById("image-modal");
  const closeImageModalBtn = document.getElementById("closeImageModal");

  if (imageModal && closeImageModalBtn) {
    closeImageModalBtn.addEventListener("click", function () {
      imageModal.close();
    });

    imageModal.addEventListener("click", function (event) {
      if (event.target === imageModal) {
        imageModal.close();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && imageModal.open) {
        imageModal.close();
      }
    });
  }
});

function showDeleteConfirmation() {
  const modal = document.getElementById('confirm-delete-account-modal');
  if (modal) {
    modal.showModal();
  }
}

async function deleteAccount() {
  const modal = document.getElementById('confirm-delete-account-modal');

  try {

    const response = await fetch('/profile/delete', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      }
    });

    const textResponse = await response.text();
    console.log('Response text:', textResponse);

    let data = {};
    if (textResponse) {
      try {
        data = JSON.parse(textResponse);
        console.log('Parsed data:', data);
      } catch (parseError) {
        console.error('Error parseando JSON:', parseError);
        console.log('Texto recibido:', textResponse);
      }
    } else {
      console.log('Response vacío');
    }

    if (response.ok) {
      window.location.href = '/logout';
    } else {
      alert(data.message || 'Error al eliminar la cuenta');
      if (modal) modal.close();
    }
  } catch (error) {
    console.error('Error completo:', error);
    console.error('Error stack:', error.stack);
    alert('Error al eliminar la cuenta');
    if (modal) modal.close();
  }
}

// ---------------------------------------------------
// Reportes
// ---------------------------------------------------

function openReportModal(entityType, entityId) {
  const modal = document.getElementById("report-modal");
  if (!modal) return;
  
  document.getElementById("report-entity-type").value = entityType;
  document.getElementById("report-entity-id").value = entityId;
  
  document.getElementById("report-form").reset();
  
  modal.showModal();
}

document.addEventListener("DOMContentLoaded", () => {
  const reportForm = document.getElementById("report-form");
  if (reportForm) {
    reportForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      
      const entityType = document.getElementById("report-entity-type").value;
      const entityId = document.getElementById("report-entity-id").value;
      
      const reasonRadio = document.querySelector('input[name="reason"]:checked');
      if (!reasonRadio) {
        showDialogMessage("Atención", "Por favor selecciona una razón.");
        return;
      }
      const reason = reasonRadio.value;
      const details = document.getElementById("report-reason-details").value;
      
      const fullReason = details ? `${reason}: ${details}` : reason;
      
      const formData = new FormData();
      formData.append("reason", fullReason);
      
      if (entityType === "user") formData.append("reported_user_id", entityId);
      if (entityType === "post") formData.append("post_id", entityId);
      if (entityType === "comment") formData.append("comment_id", entityId);
      if (entityType === "reply") formData.append("reply_id", entityId);
      
      try {
        const response = await fetch("/report", {
          method: "POST",
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          document.getElementById("report-modal").close();
          showDialogMessage("Éxito", result.data.message || "Reporte enviado correctamente.");
        } else {
          showDialogMessage("Error", result.message);
        }
      } catch (error) {
        console.error("Error al enviar reporte:", error);
        showDialogMessage("Error", "Ocurrió un error al enviar el reporte.");
      }
    });
  }
});

function showDialogMessage(title, message) {
    const dialog = document.createElement('dialog');
    dialog.className = 'confirm-dialog';
    dialog.innerHTML = `
        <div class="confirm-box" style="max-width: 350px;">
            <div class="confirm-head">
                <h3>${title}</h3>
                <p class="confirm-subtitle">${message}</p>
            </div>
            <div class="confirm-sep"></div>
            <div class="confirm-actions" style="display:flex; justify-content:center;">
                <button class="confirm-cancel" style="width:100%;" onclick="this.closest('dialog').close()">Aceptar</button>
            </div>
        </div>
    `;
    document.body.appendChild(dialog);
    dialog.showModal();
    dialog.addEventListener('close', () => {
        dialog.remove();
    });
}

// ---------------------------------------------------
// Enhanced Search Bar + History + Infinite Scroll + Friend Actions
// ---------------------------------------------------

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("globalSearchInput");
  const searchClear = document.getElementById("globalSearchClear");
  const searchForm = document.getElementById("globalSearchForm");
  const historyDropdown = document.getElementById("searchHistoryDropdown");
  const historyList = document.getElementById("searchHistoryList");
  const clearHistoryBtn = document.getElementById("clearSearchHistory");
  const HISTORY_KEY = "unired_search_history";
  const MAX_HISTORY = 10;

  function getHistory() {
    try {
      return JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
    } catch (e) {
      return [];
    }
  }

  function saveHistory(items) {
    localStorage.setItem(HISTORY_KEY, JSON.stringify(items.slice(0, MAX_HISTORY)));
  }

  function addToHistory(query) {
    if (!query || query.length < 2) return;
    let history = getHistory();
    history = history.filter((item) => item !== query);
    history.unshift(query);
    saveHistory(history);
  }

  function removeFromHistory(query) {
    let history = getHistory().filter((item) => item !== query);
    saveHistory(history);
    renderHistory();
  }

  function renderHistory() {
    if (!historyList) return;
    const history = getHistory();
    if (history.length === 0) {
      historyList.innerHTML = '<div class="search-history-item" style="color:#999; cursor:default; justify-content:center; padding:16px;">Sin búsquedas recientes</div>';
      return;
    }
    historyList.innerHTML = history
      .map(
        (item) => `
        <a href="/search?q=${encodeURIComponent(item)}" class="search-history-item" data-query="${escapeHtml(item)}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <span class="search-history-item-text">${escapeHtml(item)}</span>
          <button type="button" class="search-history-item-delete" data-query="${escapeHtml(item)}" aria-label="Eliminar búsqueda">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </a>
      `
      )
      .join("");

    // Attach delete handlers
    historyList.querySelectorAll(".search-history-item-delete").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        removeFromHistory(this.dataset.query);
      });
    });
  }

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  function showHistory() {
    if (!historyDropdown) return;
    renderHistory();
    historyDropdown.style.display = "block";
    searchInput.setAttribute("aria-expanded", "true");
  }

  function hideHistory() {
    if (!historyDropdown) return;
    setTimeout(() => {
      if (!historyDropdown.matches(":hover")) {
        historyDropdown.style.display = "none";
        searchInput.setAttribute("aria-expanded", "false");
      }
    }, 150);
  }

  if (searchInput && searchClear) {
    function toggleClearButton() {
      searchClear.style.display = searchInput.value.length > 0 ? "flex" : "none";
    }

    toggleClearButton();

    searchInput.addEventListener("input", toggleClearButton);

    searchClear.addEventListener("click", function () {
      searchInput.value = "";
      toggleClearButton();
      searchInput.focus();
    });

    // Allow Escape to clear
    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && searchInput.value.length > 0) {
        e.preventDefault();
        searchInput.value = "";
        toggleClearButton();
        searchInput.focus();
      }
    });

    // Show history on focus
    searchInput.addEventListener("focus", showHistory);
    searchInput.addEventListener("blur", hideHistory);
  }

  if (clearHistoryBtn) {
    clearHistoryBtn.addEventListener("click", function (e) {
      e.preventDefault();
      localStorage.removeItem(HISTORY_KEY);
      renderHistory();
    });
  }

  if (searchForm) {
    searchForm.addEventListener("submit", function () {
      if (searchInput && searchInput.value.length >= 2) {
        addToHistory(searchInput.value.trim());
      }
    });
  }

  // Ctrl+K / Cmd+K keyboard shortcut to focus search
  document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault();
      if (searchInput) {
        searchInput.focus();
        searchInput.select();
      }
    }
  });

  // ---------------------------------------------------
  // Search Filters
  // ---------------------------------------------------
  window.applySearchFilter = function () {
    const sort = document.getElementById("search-sort");
    const date = document.getElementById("search-date");
    if (!sort) return;

    const params = new URLSearchParams(window.location.search);
    params.set("sort", sort.value);
    if (date) {
      params.set("date", date.value);
    }
    window.location.href = window.location.pathname + "?" + params.toString();
  };

  // ---------------------------------------------------
  // Infinite Scroll (only for specific tabs)
  // ---------------------------------------------------
  const sentinel = document.getElementById("searchSentinel");
  const resultsList = document.getElementById("searchResultsList");

  if (sentinel && resultsList && typeof window.searchConfig !== "undefined" && window.searchConfig.isTabSpecific) {
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    const config = window.searchConfig;

    const spinner = document.getElementById("searchSpinner");
    const noMore = document.getElementById("searchNoMore");

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !isLoading && hasMore) {
            loadMore();
          }
        });
      },
      { rootMargin: "200px" }
    );

    observer.observe(sentinel);

    async function loadMore() {
      isLoading = true;
      if (spinner) spinner.style.display = "flex";
      currentPage++;

      try {
        const params = new URLSearchParams();
        params.set("q", config.query);
        params.set("type", config.type);
        params.set("page", currentPage);
        if (config.sort) params.set("sort", config.sort);
        if (config.date) params.set("date", config.date);

        const res = await fetch("/api/search?" + params.toString());
        const data = await res.json();

        if (!data.results || data.results.length === 0) {
          hasMore = false;
          if (spinner) spinner.style.display = "none";
          if (noMore) noMore.style.display = "block";
          observer.disconnect();
          return;
        }

        if (config.type === "posts") {
          if (data.results.posts_html && data.results.posts_html.length > 0) {
            data.results.posts_html.forEach((html) => {
              const wrapper = document.createElement("div");
              wrapper.innerHTML = html;
              const postCard = wrapper.firstElementChild;
              if (postCard) {
                postCard.style.opacity = "0";
                postCard.style.transform = "translateY(12px)";
                postCard.style.transition = "opacity 0.4s ease, transform 0.4s ease";
                resultsList.appendChild(postCard);
                requestAnimationFrame(() => {
                  postCard.style.opacity = "1";
                  postCard.style.transform = "translateY(0)";
                });
              }
            });
            // Re-initialize any post-specific JS if needed
            if (typeof window.initPostInteractions === "function") {
              window.initPostInteractions();
            }
          } else {
            hasMore = false;
          }
        } else if (config.type === "users") {
          const usersGrid = document.getElementById("searchUsersGrid");
          if (usersGrid && data.results.users && data.results.users.length > 0) {
            data.results.users.forEach((user) => {
              const card = createUserCard(user, config.query);
              card.style.opacity = "0";
              card.style.transform = "translateY(12px)";
              card.style.transition = "opacity 0.4s ease, transform 0.4s ease";
              usersGrid.appendChild(card);
              requestAnimationFrame(() => {
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
              });
            });
          } else {
            hasMore = false;
          }
        } else if (config.type === "hashtags") {
          const hashtagsGrid = document.getElementById("searchHashtagsGrid");
          if (hashtagsGrid && data.results.hashtags && data.results.hashtags.length > 0) {
            data.results.hashtags.forEach((tag) => {
              const card = createHashtagCard(tag, config.query);
              card.style.opacity = "0";
              card.style.transform = "translateY(12px)";
              card.style.transition = "opacity 0.4s ease, transform 0.4s ease";
              hashtagsGrid.appendChild(card);
              requestAnimationFrame(() => {
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
              });
            });
          } else {
            hasMore = false;
          }
        }

        hasMore = data.has_more;
        if (!hasMore) {
          if (spinner) spinner.style.display = "none";
          if (noMore) noMore.style.display = "block";
          observer.disconnect();
        }
      } catch (err) {
        console.error("Infinite scroll error:", err);
      } finally {
        isLoading = false;
        if (hasMore && spinner) spinner.style.display = "none";
      }
    }

    function createUserCard(user, query) {
      const div = document.createElement("div");
      div.className = "search-user-card large";
      div.id = "user-card-" + user.user_id;

      const status = user.friendship_status || "none";
      let actionsHtml = "";
      if (user.user_id == config.currentUserId) {
        actionsHtml = '<span class="search-user-status-badge self">T&uacute;</span>';
      } else if (status === "friends") {
        actionsHtml = `<span class="search-user-status-badge friend">Amigos</span><button class="search-user-action-btn btn-remove" data-user-id="${user.user_id}" data-action="remove">Eliminar</button>`;
      } else if (status === "pending_sent") {
        actionsHtml = `<span class="search-user-status-badge pending">Solicitud enviada</span><button class="search-user-action-btn btn-cancel" data-user-id="${user.user_id}" data-action="cancel">Cancelar</button>`;
      } else if (status === "pending_received") {
        actionsHtml = `<button class="search-user-action-btn btn-primary" data-user-id="${user.user_id}" data-action="accept">Aceptar</button><button class="search-user-action-btn btn-deny" data-user-id="${user.user_id}" data-action="reject">Rechazar</button>`;
      } else {
        actionsHtml = `<button class="search-user-action-btn btn-primary" data-user-id="${user.user_id}" data-action="add">Agregar amigo</button>`;
      }

      const roleBadge =
        user.role === "teacher"
          ? '<span class="search-user-role role-badge role-teacher">Profesor</span>'
          : user.role === "student"
          ? '<span class="search-user-role role-badge role-student">Estudiante</span>'
          : "";

      const bioHtml = user.biography ? `<span class="search-user-bio">${escapeHtml(user.biography)}</span>` : "";

      div.innerHTML = `
        <a href="/profile/${user.user_id}" class="search-user-card-link">
          <img src="${escapeHtml(user.profile_picture || '/assets/imagesProfile/default_avatar.png')}" alt="${escapeHtml(user.full_name)}" class="search-user-avatar">
          <div class="search-user-info">
            <span class="search-user-name">${escapeHtml(user.full_name)}</span>
            ${bioHtml}
            ${roleBadge}
          </div>
        </a>
        <div class="search-user-actions">${actionsHtml}</div>
      `;

      attachFriendActionHandlers(div);
      return div;
    }

    function createHashtagCard(tag, query) {
      const a = document.createElement("a");
      a.href = "/hashtag/" + encodeURIComponent(tag.name.replace(/^#/, ""));
      a.className = "search-hashtag-card large";
      const countText = tag.post_count === 1 ? "1 publicaci&oacute;n" : tag.post_count + " publicaciones";
      a.innerHTML = `
        <span class="search-hashtag-name">${escapeHtml(tag.name)}</span>
        <span class="search-hashtag-count">${countText}</span>
      `;
      return a;
    }
  }

  // ---------------------------------------------------
  // Friend Actions in Search Results
  // ---------------------------------------------------
  function attachFriendActionHandlers(container) {
    container.querySelectorAll(".search-user-action-btn").forEach((btn) => {
      btn.addEventListener("click", async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const userId = this.dataset.userId;
        const action = this.dataset.action;
        const card = this.closest(".search-user-card");
        if (!userId || !action || !card) return;

        try {
          let url = "";
          let method = "POST";
          if (action === "add") url = "/friend/request/" + userId;
          else if (action === "accept") url = "/friend/acceptUser/" + userId;
          else if (action === "reject") url = "/friend/reject/" + userId;
          else if (action === "cancel") url = "/friend/cancelUser/" + userId;
          else if (action === "remove") url = "/friend/remove/" + userId;

          if (!url) return;

          const res = await fetch(url, { method, headers: { "X-Requested-With": "XMLHttpRequest" } });
          if (res.ok || res.redirected) {
            // Update card UI based on action
            const actionsContainer = card.querySelector(".search-user-actions");
            if (!actionsContainer) return;

            if (action === "add") {
              actionsContainer.innerHTML = `<span class="search-user-status-badge pending">Solicitud enviada</span><button class="search-user-action-btn btn-cancel" data-user-id="${userId}" data-action="cancel">Cancelar</button>`;
            } else if (action === "accept") {
              actionsContainer.innerHTML = `<span class="search-user-status-badge friend">Amigos</span><button class="search-user-action-btn btn-remove" data-user-id="${userId}" data-action="remove">Eliminar</button>`;
            } else if (action === "reject" || action === "cancel" || action === "remove") {
              actionsContainer.innerHTML = `<button class="search-user-action-btn btn-primary" data-user-id="${userId}" data-action="add">Agregar amigo</button>`;
            }
            attachFriendActionHandlers(card);
          }
        } catch (err) {
          console.error("Friend action error:", err);
        }
      });
    });
  }

  // Attach to existing cards on page load
  document.querySelectorAll(".search-user-card").forEach((card) => {
    attachFriendActionHandlers(card);
  });
});