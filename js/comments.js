/**
 * UNIRED - Comments & Replies Module (js/comments.js)
 * Manages comment rendering, inline editing, likes, replies, and moderation modals.
 */

function toggleCommentMenu(event, menuId) {
  event.stopPropagation();
  event.preventDefault();

  const allMenus = document.querySelectorAll(".comment-menu-modal, .reply-menu-modal");
  allMenus.forEach((menu) => {
    if (menu.id !== menuId) {
      menu.classList.remove("active");
    }
  });

  const menu = document.getElementById(menuId);
  if (menu) {
    menu.classList.toggle("active");
  }
}

function toggleReplyMenu(event, menuId) {
  event.stopPropagation();
  event.preventDefault();

  const allMenus = document.querySelectorAll(".comment-menu-modal, .reply-menu-modal");
  allMenus.forEach((menu) => {
    if (menu.id !== menuId) {
      menu.classList.remove("active");
    }
  });

  const menu = document.getElementById(menuId);
  if (menu) {
    menu.classList.toggle("active");
  }
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
    const btn = input.closest(".reply-input-container")?.querySelector(".btn-submit-reply");
    if (btn) addReply(btn);
  }
}

function updateCommentCount(postContainer, commentCount) {
  const commentCountBtn = postContainer.querySelector(".comment-btn");
  if (commentCountBtn) {
    commentCountBtn.innerHTML = `
      <img src="/assets/images/comment.png" alt="Comentarios" width="16" height="16">
      ${commentCount}
    `;
  }
}
