/**
 * Live movie search for the nav search bar.
 * Debounces keystrokes, fetches search-movies.php, and renders a dropdown
 * of matching movies. Clicking a result (or pressing Enter) opens its
 * movie-details page. Does not touch any other site functionality.
 */
(function () {
  const input = document.getElementById('nav-search-input');
  const dropdown = document.getElementById('nav-search-dropdown');
  if (!input || !dropdown) return;

  const endpoint = (window.CINEPLEX_BASE || '') + 'search-movies.php';
  let debounceTimer = null;
  let activeIndex = -1;
  let currentResults = [];

  function closeDropdown() {
    dropdown.classList.remove('open');
    dropdown.innerHTML = '';
    activeIndex = -1;
    currentResults = [];
  }

  function renderResults(results) {
    currentResults = results;
    activeIndex = -1;

    if (results.length === 0) {
      dropdown.innerHTML = '<div class="nav-search-empty">No movies found.</div>';
      dropdown.classList.add('open');
      return;
    }

    dropdown.innerHTML = results.map((movie, i) => `
      <a href="${movie.url}" class="nav-search-item" data-index="${i}">
        <div class="nav-search-thumb">
          ${movie.poster_url ? `<img src="${movie.poster_url}" alt="" loading="lazy">` : ''}
        </div>
        <div class="nav-search-meta">
          <span class="nav-search-title">${escapeHtml(movie.title)}</span>
          <span class="nav-search-sub">${escapeHtml(movie.genre || '')}${movie.year ? ' · ' + movie.year : ''}</span>
        </div>
      </a>
    `).join('');

    dropdown.classList.add('open');
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function runSearch(query) {
    fetch(endpoint + '?q=' + encodeURIComponent(query))
      .then((res) => res.json())
      .then((data) => renderResults(data.results || []))
      .catch(() => closeDropdown());
  }

  input.addEventListener('input', function () {
    const query = input.value.trim();
    clearTimeout(debounceTimer);

    if (query === '') {
      closeDropdown();
      return;
    }

    debounceTimer = setTimeout(() => runSearch(query), 200);
  });

  // Keyboard navigation through the dropdown
  input.addEventListener('keydown', function (e) {
    const items = dropdown.querySelectorAll('.nav-search-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, items.length - 1);
      updateActiveItem(items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, 0);
      updateActiveItem(items);
    } else if (e.key === 'Enter' && activeIndex >= 0) {
      e.preventDefault();
      window.location.href = items[activeIndex].getAttribute('href');
    } else if (e.key === 'Escape') {
      closeDropdown();
      input.blur();
    }
  });

  function updateActiveItem(items) {
    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
    if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.nav-search')) closeDropdown();
  });
})();
