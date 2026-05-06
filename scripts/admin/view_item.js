    // ── THEME TOGGLE ──
    (function () {
        var themeBtn = document.getElementById('themeToggle');
        var iconMoon = document.getElementById('iconMoon');
        var iconSun  = document.getElementById('iconSun');
        var KEY      = 'gz_admin_theme';

        function applyTheme(t) {
            document.documentElement.setAttribute('data-bs-theme', t);
            localStorage.setItem(KEY, t);
            if (t === 'dark') {
                iconMoon.style.display = 'none';
                iconSun.style.display  = 'inline-block';
            } else {
                iconMoon.style.display = 'inline-block';
                iconSun.style.display  = 'none';
            }
        }

        applyTheme(localStorage.getItem(KEY) || 'dark');

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        }
    })();