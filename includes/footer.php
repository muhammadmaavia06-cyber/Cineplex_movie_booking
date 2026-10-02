  </main>
  <footer>
    <span>© <?php echo date('Y'); ?> CinePlex — Ocean Mint Theme</span>
    <span>Kids concession available (ages 3–12) · <a href="<?php echo $base ?? ''; ?>admin/login.php">Admin dashboard →</a></span>
  </footer>

  <div class="trailer-modal-overlay hidden" id="trailerModalOverlay">
    <div class="trailer-modal">
      <div class="trailer-modal-head">
        <h3 id="trailerModalTitle">Trailer</h3>
        <button type="button" class="trailer-modal-close" id="trailerModalClose" aria-label="Close trailer">&times;</button>
      </div>
      <div class="trailer-modal-body" id="trailerModalBody"></div>
    </div>
  </div>
  <script>
    (function () {
      var overlay = document.getElementById('trailerModalOverlay');
      var body = document.getElementById('trailerModalBody');
      var titleEl = document.getElementById('trailerModalTitle');
      var closeBtn = document.getElementById('trailerModalClose');

      function toEmbedUrl(url) {
        if (!url) return '';
        if (url.indexOf('/embed/') !== -1) return url;
        var m = url.match(/(?:youtu\.be\/|v=)([\w-]{6,})/);
        return m ? 'https://www.youtube.com/embed/' + m[1] : url;
      }

      function openTrailer(url, title) {
        titleEl.textContent = title ? title + ' — Trailer' : 'Trailer';
        var src = toEmbedUrl(url) + (toEmbedUrl(url).indexOf('?') !== -1 ? '&' : '?') + 'autoplay=1&rel=0';
        body.innerHTML = '<iframe src="' + src + '" title="' + (title || 'Trailer').replace(/"/g, '') + '" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }

      function closeTrailer() {
        overlay.classList.add('hidden');
        body.innerHTML = '';
        document.body.style.overflow = '';
      }

      document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-trailer]');
        if (trigger) {
          e.preventDefault();
          openTrailer(trigger.getAttribute('data-trailer'), trigger.getAttribute('data-title'));
        }
      });

      closeBtn.addEventListener('click', closeTrailer);
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeTrailer();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.classList.contains('hidden')) closeTrailer();
      });
    })();
  </script>
</body>
</html>
