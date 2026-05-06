    const html      = document.documentElement;
    const toggleBtn = document.getElementById('themeToggle');
    const iconMoon  = document.getElementById('iconMoon');
    const iconSun   = document.getElementById('iconSun');
    const KEY       = 'gz_theme';

    function applyTheme(theme) {
        html.setAttribute('data-bs-theme', theme);
        localStorage.setItem(KEY, theme);
        iconMoon.style.display = theme === 'dark' ? 'none'         : 'inline-block';
        iconSun.style.display  = theme === 'dark' ? 'inline-block' : 'none';
    }

    // Apply on load, then wire up the button
    applyTheme(localStorage.getItem(KEY) || 'light');
    toggleBtn.addEventListener('click', () => {
        applyTheme(html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
    });