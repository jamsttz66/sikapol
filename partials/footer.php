  </div><!-- /content -->

  <footer class="text-center text-muted py-3 no-print" style="font-size:.72rem">
    &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &mdash; <?= e(APP_FULL) ?><br>
    <?= e(APP_INSTANSI) ?>
  </footer>
</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    var sb = document.getElementById('sidebar'),
        bd = document.getElementById('backdrop'),
        tg = document.getElementById('toggleSidebar');

    function open()  { sb.classList.add('show'); bd.classList.add('show'); }
    function close() { sb.classList.remove('show'); bd.classList.remove('show'); }

    if (tg) tg.addEventListener('click', function () {
      sb.classList.contains('show') ? close() : open();
    });
    if (bd) bd.addEventListener('click', close);

    // auto-hilangkan alert setelah 4 detik
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
      setTimeout(function () {
        if (window.bootstrap && bootstrap.Alert) {
          bootstrap.Alert.getOrCreateInstance(el).close();
        }
      }, 4000);
    });
  })();
</script>
</body>
</html>
