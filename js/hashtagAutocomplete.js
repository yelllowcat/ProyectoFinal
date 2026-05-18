document.addEventListener('DOMContentLoaded', function () {
  const textarea = document.getElementById('postText');
  if (!textarea) return;

  // Create suggestion dropdown
  const dropdown = document.createElement('div');
  dropdown.className = 'hashtag-suggestions-dropdown';
  dropdown.style.display = 'none';
  textarea.parentNode.style.position = 'relative';
  textarea.parentNode.appendChild(dropdown);

  let currentQuery = '';
  let selectedIndex = -1;
  let suggestions = [];
  let debounceTimer = null;

  textarea.addEventListener('input', function (e) {
    const cursorPos = textarea.selectionStart;
    const text = textarea.value;
    const beforeCursor = text.substring(0, cursorPos);

    // Find if we're currently typing a hashtag
    const hashMatch = beforeCursor.match(/#(\w*)$/);

    if (!hashMatch) {
      hideDropdown();
      return;
    }

    currentQuery = hashMatch[1];

    if (currentQuery.length < 1) {
      hideDropdown();
      return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      fetchSuggestions(currentQuery);
    }, 150);
  });

  textarea.addEventListener('keydown', function (e) {
    if (dropdown.style.display === 'none') return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedIndex = (selectedIndex + 1) % suggestions.length;
      renderSuggestions();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedIndex = (selectedIndex - 1 + suggestions.length) % suggestions.length;
      renderSuggestions();
    } else if (e.key === 'Enter' || e.key === 'Tab') {
      e.preventDefault();
      if (selectedIndex >= 0 && suggestions[selectedIndex]) {
        insertHashtag(suggestions[selectedIndex].name);
      }
    } else if (e.key === 'Escape') {
      hideDropdown();
    }
  });

  document.addEventListener('click', function (e) {
    if (!dropdown.contains(e.target) && e.target !== textarea) {
      hideDropdown();
    }
  });

  function fetchSuggestions(query) {
    fetch('/api/hashtags/suggest?q=' + encodeURIComponent(query))
      .then(res => res.json())
      .then(data => {
        if (data.success && data.data && data.data.length > 0) {
          suggestions = data.data;
          selectedIndex = 0;
          renderSuggestions();
          showDropdown();
        } else {
          hideDropdown();
        }
      })
      .catch(() => hideDropdown());
  }

  function renderSuggestions() {
    dropdown.innerHTML = '';
    suggestions.forEach((item, index) => {
      const div = document.createElement('div');
      div.className = 'hashtag-suggestion-item' + (index === selectedIndex ? ' selected' : '');
      div.innerHTML = '<span class="hashtag-suggestion-name">#' + escapeHtml(item.name) + '</span>' +
        '<span class="hashtag-suggestion-count">' + item.post_count + ' publicaciones</span>';
      div.addEventListener('click', function () {
        insertHashtag(item.name);
      });
      div.addEventListener('mouseenter', function () {
        selectedIndex = index;
        renderSuggestions();
      });
      dropdown.appendChild(div);
    });
  }

  function insertHashtag(name) {
    const cursorPos = textarea.selectionStart;
    const text = textarea.value;
    const beforeCursor = text.substring(0, cursorPos);
    const afterCursor = text.substring(cursorPos);

    // Replace the partial hashtag with the full one
    const newBefore = beforeCursor.replace(/#\w*$/, '#' + name + ' ');
    textarea.value = newBefore + afterCursor;

    // Set cursor position after the inserted hashtag + space
    const newCursorPos = newBefore.length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();

    // Trigger the counter update
    if (typeof updateCounter === 'function') {
      updateCounter();
    }

    hideDropdown();
  }

  function showDropdown() {
    dropdown.style.display = 'block';
  }

  function hideDropdown() {
    dropdown.style.display = 'none';
    suggestions = [];
    selectedIndex = -1;
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
});
