   // ── REJECT MODAL ──
    function openReject(id, name) {
        document.getElementById('rejectId').value = id;
        document.getElementById('rejectName').textContent = 'Rejecting: "' + name + '"';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    // ── IMAGE PREVIEW ──
    document.getElementById('imgInput')?.addEventListener('change', function () {
        if (this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById('imgPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

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