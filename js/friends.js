let currentFilter = "friend";
let currentSearchTerm = "";

function searchFriends(searchTerm) {
  currentSearchTerm = searchTerm.toLowerCase().trim();
  const clearBtn = document.getElementById("clearSearchBtn");
  const searchResults = document.getElementById("searchResults");

  if (currentSearchTerm.length > 0) {
    clearBtn.style.display = "flex";
  } else {
    clearBtn.style.display = "none";
    searchResults.style.display = "none";
  }

  filterAndSearch();
}

function clearSearch() {
  const searchInput = document.getElementById("friendSearchInput");
  searchInput.value = "";
  currentSearchTerm = "";
  document.getElementById("clearSearchBtn").style.display = "none";
  document.getElementById("searchResults").style.display = "none";
  filterAndSearch();
}

function filterFriends(event, filter) {
  const tabs = document.querySelectorAll(".tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  const clickedTab = event.target.closest(".tab") || event.currentTarget;
  if (clickedTab) {
    clickedTab.classList.add("active");
  }

  currentFilter = filter;
  filterAndSearch();
}

function updateTabBadges() {
  const counts = {
    friend: 0,
    request: 0,
    pending: 0,
    suggestion: 0
  };

  document.querySelectorAll(".friend-card").forEach((card) => {
    const st = card.dataset.status || "friend";
    if (counts[st] !== undefined) {
      counts[st]++;
    }
  });

  Object.keys(counts).forEach((key) => {
    const badge = document.getElementById("badge-" + key);
    if (badge) {
      badge.textContent = counts[key];
    }
  });
}

function filterAndSearch() {
  const friendCards = document.querySelectorAll(".friend-card");
  const noResultsMessage = document.getElementById("noResultsMessage");
  const noResultsTitle = document.getElementById("noResultsTitle");
  const noResultsSubtitle = document.getElementById("noResultsSubtitle");
  const searchResults = document.getElementById("searchResults");
  let visibleCount = 0;

  updateTabBadges();

  friendCards.forEach((card) => {
    const friendName = card
      .querySelector(".friend-name")
      .textContent.toLowerCase();
    const cardStatus = card.dataset.status || "friend";

    const matchesFilter = cardStatus === currentFilter;

    const matchesSearch =
      currentSearchTerm === "" || friendName.includes(currentSearchTerm);

    if (matchesFilter && matchesSearch) {
      card.style.display = "flex";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  if (visibleCount === 0) {
    noResultsMessage.style.display = "block";
    if (noResultsTitle && noResultsSubtitle) {
      if (currentSearchTerm.length > 0) {
        noResultsTitle.textContent = "Sin coincidencias";
        noResultsSubtitle.textContent = `No encontramos a nadie con "${currentSearchTerm}" en esta pestaña.`;
      } else if (currentFilter === "friend") {
        noResultsTitle.textContent = "Aún no tienes amigos agregados";
        noResultsSubtitle.textContent = "Explora la pestaña de Sugerencias para conectar con compañeros.";
      } else if (currentFilter === "request") {
        noResultsTitle.textContent = "No tienes solicitudes pendientes";
        noResultsSubtitle.textContent = "Cuando alguien te envíe una solicitud de amistad aparecerá aquí.";
      } else if (currentFilter === "pending") {
        noResultsTitle.textContent = "No has enviado solicitudes";
        noResultsSubtitle.textContent = "Aquí verás las solicitudes de amistad que están esperando respuesta.";
      } else {
        noResultsTitle.textContent = "No hay más sugerencias por ahora";
        noResultsSubtitle.textContent = "Ya conoces a todos los usuarios disponibles.";
      }
    }
  } else {
    noResultsMessage.style.display = "none";
  }

  if (currentSearchTerm.length > 0) {
    searchResults.style.display = "block";
    searchResults.textContent = `Se encontraron ${visibleCount} resultado${
      visibleCount !== 1 ? "s" : ""
    } para "${currentSearchTerm}"`;
  } else {
    searchResults.style.display = "none";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const friendCards = document.querySelectorAll(".friend-card");
  friendCards.forEach((card) => {
    if (card.querySelector(".btn-accept")) {
      card.dataset.status = "request";
    } else if (card.querySelector(".btn-cancel")) {
      card.dataset.status = "pending";
    } else if (card.querySelector(".btn-add")) {
      card.dataset.status = "suggestion";
    } else {
      card.dataset.status = "friend";
    }
  });

  filterAndSearch();

  document.addEventListener("click", function (event) {
    const button = event.target.closest(".btn-action");
    if (!button) return;

    const action = button.dataset.action;
    if (action === "view") return;

    const card = button.closest(".friend-card");

    // Local dismiss for suggestion ("Ocultar")
    if (action === "deny" && card && card.dataset.status === "suggestion") {
      card.style.transition = "opacity 0.25s ease, transform 0.25s ease";
      card.style.opacity = "0";
      card.style.transform = "scale(0.95)";
      setTimeout(() => {
        card.remove();
        filterAndSearch();
      }, 250);
      return;
    }

    const id =
      button.dataset.requestId ||
      button.dataset.suggestionId ||
      button.dataset.userId;
    if (!id) return;

    if (action === "accept") {
      handleFriendAction(id, button, "/friend/accept/");
    } else if (action === "deny") {
      handleFriendAction(id, button, "/friend/reject/");
    } else if (action === "add") {
      handleFriendAction(id, button, "/friend/request/");
    } else if (action === "cancel") {
      handleFriendAction(id, button, "/friend/cancelUser/");
    }
  });
});

function handleFriendAction(id, button, endpoint) {
  button.disabled = true;
  const originalText = button.textContent;
  button.textContent = "Procesando...";
  const action = button.dataset.action;

  fetch(`${endpoint}${id}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({})
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const card = button.closest(".friend-card");
        if (card) {
          updateCardAfterAction(card, action);
        }
      } else {
        if (typeof showDialogMessage === "function") {
          showDialogMessage("Atención", data.message || "No se pudo completar la acción.");
        }
        button.disabled = false;
        button.textContent = originalText;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      if (typeof showDialogMessage === "function") {
        showDialogMessage("Error de conexión", "Ocurrió un error al procesar la solicitud.");
      }
      button.disabled = false;
      button.textContent = originalText;
    });
}

function updateCardAfterAction(card, action) {
  const actionsContainer = card.querySelector(".friend-actions");
  const userId = card.dataset.userId;

  card.style.transition = "opacity 0.25s ease";
  card.style.opacity = "0";

  setTimeout(() => {
    switch (action) {
      case "accept":
        card.dataset.status = "friend";
        actionsContainer.innerHTML = `<a href='/profile/${userId}' class='friend-view-link'><button data-user-id='${userId}' data-action='view' class='btn btn-view-profile btn-action'>Ver perfil</button></a>`;
        break;

      case "deny":
        card.remove();
        filterAndSearch();
        return;

      case "add":
        card.dataset.status = "pending";
        actionsContainer.innerHTML = `<button data-user-id='${userId}' data-action='cancel' class='btn btn-deny btn-cancel btn-action'>Cancelar solicitud</button>`;
        break;

      case "cancel":
        card.dataset.status = "suggestion";
        actionsContainer.innerHTML = `
          <button data-suggestion-id='${userId}' data-action='add' class='btn btn-primary btn-add btn-action'>Agregar amigo</button>
          <button data-suggestion-id='${userId}' data-action='deny' class='btn btn-ghost-dismiss btn-action'>Ocultar</button>
        `;
        break;
    }

    card.style.opacity = "1";
    filterAndSearch();
  }, 250);
}
