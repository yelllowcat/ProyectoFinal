<div class="search-header">
    <form action="/search" method="GET" class="search-header-form" id="globalSearchForm">
        <div class="search-header-wrapper">
            <span class="search-header-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" name="q" class="search-header-input" id="globalSearchInput"
                   placeholder="Buscar publicaciones, usuarios o hashtags..."
                   value="<?php echo safe_output($_GET['q'] ?? ''); ?>"
                   required minlength="2" maxlength="100" autocomplete="off"
                   aria-label="Buscar"
                   aria-autocomplete="list"
                   aria-controls="searchHistoryDropdown"
                   aria-expanded="false">
            <button type="button" class="search-header-clear" id="globalSearchClear" aria-label="Limpiar búsqueda" style="display: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <button type="submit" class="search-header-btn" aria-label="Buscar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </div>
        <div class="search-header-hint">
            <kbd>Ctrl</kbd> + <kbd>K</kbd> para buscar
        </div>
        <div class="search-history-dropdown" id="searchHistoryDropdown" role="listbox" style="display: none;">
            <div class="search-history-header">
                <span class="search-history-title">Búsquedas recientes</span>
                <button type="button" class="search-history-clear" id="clearSearchHistory">Borrar todo</button>
            </div>
            <div class="search-history-list" id="searchHistoryList" role="list">
                <!-- Items will be populated by JS -->
            </div>
        </div>
    </form>
</div>
