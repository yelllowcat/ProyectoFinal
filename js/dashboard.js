function setActiveTab(clickedTab) {
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  clickedTab.classList.add("active");
}

function showLoading() {
  const tableBody = document.getElementById("statsTableBody");
  tableBody.innerHTML = `
    <tr>
      <td colspan="5" style="text-align: center; padding: 40px;">
        Cargando estadísticas...
      </td>
    </tr>
  `;
}

function showError(message) {
  const tableBody = document.getElementById("statsTableBody");
  tableBody.innerHTML = `
    <tr>
      <td colspan="5" style="text-align: center; padding: 40px; color: #d93025;">
        ${message}
      </td>
    </tr>
  `;
}

function setUserHeaders() {
  const tableHeader = document.getElementById("statsTableHeader");
  tableHeader.innerHTML = `
    <tr>
      <th>Id</th>
      <th>Nombre</th>
      <th>Correo electrónico</th>
      <th>Cantidad</th>
      <th></th>
    </tr>
  `;
}

function setPostHeaders() {
  const tableHeader = document.getElementById("statsTableHeader");
  tableHeader.innerHTML = `
    <tr>
      <th>Id</th>
      <th>Autor</th>
      <th>Contenido</th>
      <th>Cantidad</th>
      <th></th>
    </tr>
  `;
}

function renderUserRows(users, countKey, countLabel) {
  const tableBody = document.getElementById("statsTableBody");

  if (users.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; padding: 40px;">
          No hay datos disponibles
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = users
    .map(
      (user) => `
    <tr>
      <td>${user.user_id}</td>
      <td>${user.full_name}</td>
      <td>${user.email}</td>
      <td>${user[countKey]}</td>
      <td>
        <a href="/profile/${user.user_id}">
          <button class="btn-view-profile-table">Ver perfil</button>
        </a>
      </td>
    </tr>
  `
    )
    .join("");
}

function renderPostRows(posts, countKey) {
  const tableBody = document.getElementById("statsTableBody");

  if (posts.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; padding: 40px;">
          No hay datos disponibles
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = posts
    .map(
      (post) => `
    <tr>
      <td>${post.post_id}</td>
      <td>${post.author_name}</td>
      <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
        ${post.content || "Sin contenido"}
      </td>
      <td>${post[countKey]}</td>
      <td>
        <a href="/profile/${post.user_id}">
          <button class="btn-view-profile-table">Ver perfil</button>
        </a>
      </td>
    </tr>
  `
    )
    .join("");
}

async function fetchUsersWithMostPosts(event) {
  setActiveTab(event.currentTarget);
  setUserHeaders();
  showLoading();

  try {
    const response = await fetch("/admin/stats/users-posts");
    const result = await response.json();

    if (result.success) {
      renderUserRows(result.data, "post_count", "Publicaciones");
    } else {
      showError("Error al cargar las estadísticas");
    }
  } catch (error) {
    console.error("Error fetching users with most posts:", error);
    showError("Error al cargar las estadísticas");
  }
}

async function fetchUsersWithMostFriends(event) {
  setActiveTab(event.currentTarget);
  setUserHeaders();
  showLoading();

  try {
    const response = await fetch("/admin/stats/users-friends");
    const result = await response.json();

    if (result.success) {
      renderUserRows(result.data, "friend_count", "Amigos");
    } else {
      showError("Error al cargar las estadísticas");
    }
  } catch (error) {
    console.error("Error fetching users with most friends:", error);
    showError("Error al cargar las estadísticas");
  }
}

async function fetchPostsWithMostComments(event) {
  setActiveTab(event.currentTarget);
  setPostHeaders();
  showLoading();

  try {
    const response = await fetch("/admin/stats/posts-comments");
    const result = await response.json();

    if (result.success) {
      renderPostRows(result.data, "comment_count");
    } else {
      showError("Error al cargar las estadísticas");
    }
  } catch (error) {
    console.error("Error fetching posts with most comments:", error);
    showError("Error al cargar las estadísticas");
  }
}

async function fetchPostsWithMostLikes(event) {
  setActiveTab(event.currentTarget);
  setPostHeaders();
  showLoading();

  try {
    const response = await fetch("/admin/stats/posts-likes");
    const result = await response.json();

    if (result.success) {
      renderPostRows(result.data, "like_count");
    } else {
      showError("Error al cargar las estadísticas");
    }
  } catch (error) {
    console.error("Error fetching posts with most likes:", error);
    showError("Error al cargar las estadísticas");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const firstTab = document.querySelector(".stat-tab");
  if (firstTab) {
    fetchUsersWithMostPosts({ currentTarget: firstTab });
  }

  const adminLogoutLink = document.getElementById("admin-logout");
  const logoutModal = document.getElementById("confirm-logout-modal");

  if (adminLogoutLink && logoutModal) {
    adminLogoutLink.addEventListener("click", function (event) {
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
