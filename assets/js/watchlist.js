/**
 * Client-side "Add to Watchlist" toggle for the movie details hero.
 * The current database schema has no watchlist table, so this stores
 * the list in the browser's localStorage per device. Swap this for a
 * real endpoint later if a `watchlist` table is added.
 */
(function () {
  const STORAGE_KEY = 'cineplex_watchlist';
  const btn = document.getElementById('watchlist-btn');
  if (!btn) return;

  const movieId = btn.getAttribute('data-movie-id');

  function getList() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch (e) {
      return [];
    }
  }

  function saveList(list) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
  }

  function refreshButton() {
    const inList = getList().includes(movieId);
    btn.classList.toggle('in-list', inList);
    btn.textContent = inList ? '✓ In Watchlist' : '+ Add to Watchlist';
  }

  btn.addEventListener('click', function () {
    let list = getList();
    if (list.includes(movieId)) {
      list = list.filter((id) => id !== movieId);
    } else {
      list.push(movieId);
    }
    saveList(list);
    refreshButton();
  });

  refreshButton();
})();
