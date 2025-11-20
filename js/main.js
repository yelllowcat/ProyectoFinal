const COMMENTS_PER_LOAD = 3;

function loadMoreComments(button) {
  const commentsSection = button.closest(".comments-section");
  const comments = commentsSection.querySelectorAll(".comment.hidden");
  const loadMoreBtn = button;

  let loaded = 0;
  for (let i = 0; i < comments.length && loaded < COMMENTS_PER_LOAD; i++) {
    comments[i].classList.remove("hidden");
    loaded++;
  }

  const remainingComments = commentsSection.querySelectorAll(".comment.hidden");
  if (remainingComments.length === 0) {
    loadMoreBtn.classList.add("hidden");
  }
}
function toggleMenu(event, menuId) {
  event.stopPropagation();

  const menu = document.getElementById(menuId);
  const allMenus = document.querySelectorAll(".post-menu-modal");

  const wasActive = menu.classList.contains("active");

  allMenus.forEach((m) => {
    m.classList.remove("active");
  });

  if (!wasActive) {
    menu.classList.toggle("active");
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

function toggleComments(button) {
  const postCard = button.closest(".post-container");
  const commentsSection = postCard.querySelector(".comments-section");

  commentsSection.classList.toggle("hidden");
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

function addComment(button) {
  const postCard = button.closest(".post-container");
  const commentInput = postCard.querySelector(".comment-input");
  const commentText = commentInput.value.trim();

  if (!commentText) {
    alert("Por favor escribe un comentario");
    return;
  }

  const now = new Date();
  const dateString = now.toLocaleDateString("es-MX", {
    day: "numeric",
    month: "long",
  });
  const timeAgo = "Justo ahora";

  const commentHTML = `
        <div class="comment">
            <div class="comment-header">Manuel Orozco: ${escapeHtml(
    commentText
  )}</div>
            <div class="comment-date">${timeAgo} • ${dateString}</div>
        </div>
    `;

  const commentInputContainer = postCard.querySelector(
    ".comment-input-container"
  );
  commentInputContainer.insertAdjacentHTML("beforebegin", commentHTML);

  commentInput.value = "";

  const newComment = commentInputContainer.previousElementSibling;
  newComment.style.opacity = "0";
  newComment.style.transform = "translateY(-10px)";
  setTimeout(() => {
    newComment.style.transition = "all 0.3s ease";
    newComment.style.opacity = "1";
    newComment.style.transform = "translateY(0)";
  }, 10);

  const commentsButton = postCard.querySelector(".action-btn.comments");
  const currentCount = parseInt(commentsButton.textContent.match(/\d+/)[0]);
  const newCount = currentCount + 1;
  commentsButton.innerHTML = `
    <img src='/assets/images/comments.png' alt='comments icon' width='25'>
    ${newCount} Comentarios
  `;
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
