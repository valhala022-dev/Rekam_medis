    </main>
    <footer class="px-8 py-4 text-xs text-slate-400 text-center no-print">
        &copy; <?= date('Y') ?> Sistem Informasi Rekam Medis &middot; PHP Native
    </footer>
</div>

<script>
(function () {
    // --- Sidebar offcanvas (tablet/mobile) ---
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('overlay');
    function openMenu()  { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
    function closeMenu() { sidebar.classList.add('-translate-x-full');    overlay.classList.add('hidden'); }
    var openBtn = document.getElementById('openSidebar');
    var closeBtn = document.getElementById('closeSidebar');
    if (openBtn)  openBtn.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });

    // --- Toast: tutup manual + hilang otomatis ---
    var wrap = document.getElementById('toast-wrap');
    if (wrap) {
        var hide = function () { wrap.style.transition = 'opacity .3s'; wrap.style.opacity = '0'; setTimeout(function () { wrap.remove(); }, 300); };
        var x = wrap.querySelector('[data-dismiss-toast]');
        if (x) x.addEventListener('click', hide);
        setTimeout(hide, 5000);
    }

    // --- Dialog konfirmasi sebelum aksi hapus ---
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm(f.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });

    // --- Validasi frontend: trim spasi + cegah double submit ---
    document.querySelectorAll('form[data-validate]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            f.querySelectorAll('input[type=text], textarea').forEach(function (el) {
                el.value = el.value.trim();
                if (el.required && el.value === '') { el.setCustomValidity('Kolom ini wajib diisi.'); } else { el.setCustomValidity(''); }
            });
            if (!f.checkValidity()) { e.preventDefault(); f.reportValidity(); return; }
            var b = f.querySelector('button[type=submit]');
            if (b) { setTimeout(function () { b.disabled = true; b.classList.add('opacity-60'); }, 0); }
        });
    });
})();
</script>
</body>
</html>
