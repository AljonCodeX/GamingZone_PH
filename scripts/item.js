    function changeQty(delta) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > parseInt(input.max)) val = parseInt(input.max);
        input.value = val;
        document.getElementById('addCartQty').value = val;
        document.getElementById('buyNowQty').value  = val;
    }

    document.getElementById('qtyInput')?.addEventListener('input', function () {
        let val = parseInt(this.value) || 1;
        if (val < 1) val = 1;
        if (val > parseInt(this.max)) val = parseInt(this.max);
        this.value = val;
        document.getElementById('addCartQty').value = val;
        document.getElementById('buyNowQty').value  = val;
    });

    // ── THEME ──
    (function () {
        var themeBtn = document.getElementById('themeToggle');
        var iconMoon = document.getElementById('iconMoon');
        var iconSun  = document.getElementById('iconSun');
        var KEY = 'gz_theme';
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
        applyTheme(localStorage.getItem(KEY) || 'light');
        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        }
    })();