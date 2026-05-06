    // ── TAB SWITCHER ──
    function switchTab(tab) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('#authTabs .nav-link').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + tab).classList.add('active');
        document.querySelectorAll('#authTabs .nav-link')[tab === 'login' ? 0 : 1].classList.add('active');
    }

    // ── THEME TOGGLE ──
    (function () {
        var themeBtn = document.getElementById('themeToggle');
        var iconMoon = document.getElementById('iconMoon');
        var iconSun  = document.getElementById('iconSun');
        var KEY      = 'gz_theme';

        function applyTheme(t) {
            document.documentElement.setAttribute('data-bs-theme', t);
            localStorage.setItem(KEY, t);
            // Swap moon <-> sun icon
            if (t === 'dark') {
                iconMoon.style.display = 'none';
                iconSun.style.display  = 'inline-block';
            } else {
                iconMoon.style.display = 'inline-block';
                iconSun.style.display  = 'none';
            }
        }

        applyTheme(localStorage.getItem(KEY) || 'light');

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        }
    })();