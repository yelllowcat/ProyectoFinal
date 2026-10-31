/**
 * UNIRED - Search Module (js/search.js)
 * Manages global search input, recent search history dropdown (localStorage),
 * keyboard shortcuts (Ctrl+K), search filters, infinite scroll pagination,
 * and friend request actions within search results.
 */

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

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  function renderHistory() {
    if (!historyList) return;
    const history = getHistory();
    if (history.length === 0) {
      historyList.innerHTML =
        '<div class="search-history-item" style="color:#999; cursor:default; justify-content:center; padding:16px;">Sin búsquedas recientes</div>';
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

    historyList.querySelectorAll(".search-history-item-delete").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        removeFromHistory(this.dataset.query);
      });
    });
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

    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && searchInput.value.length > 0) {
        e.preventDefault();
        searchInput.value = "";
        toggleClearButton();
        searchInput.focus();
      }
    });

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

  // Ctrl+K / Cmd+K keyboard shortcut to focus global search
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
  // Infinite Scroll (tab-specific results)
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

  document.querySelectorAll(".search-user-card").forEach((card) => {
    attachFriendActionHandlers(card);
  });
});
