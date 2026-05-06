 if (document.getElementById('selectAll')) {

        const selectAll   = document.getElementById('selectAll');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const countEl     = document.getElementById('selectedCount');
        const totalEl     = document.getElementById('selectedTotal');

        function getCheckboxes() { return document.querySelectorAll('.item-check'); }

        function updateFooter() {
            let count = 0, total = 0;
            getCheckboxes().forEach(cb => {
                const row = cb.closest('tr');
                if (cb.checked) {
                    count++;
                    total += parseFloat(cb.dataset.price);
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            });
            countEl.textContent  = count;
            totalEl.textContent  = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            checkoutBtn.disabled = count === 0;
            const cbs = getCheckboxes();
            selectAll.checked       = count === cbs.length;
            selectAll.indeterminate = count > 0 && count < cbs.length;
        }

        // QTY BUTTONS
        document.querySelectorAll('.qty-btn').forEach(function(qtyBtn) {
            qtyBtn.addEventListener('click', function () {
                const index     = this.dataset.index;
                const action    = this.dataset.action;
                const qtyInput  = document.querySelector(`.qty-field[data-index="${index}"]`);
                const checkbox  = document.querySelector(`.item-check[data-index="${index}"]`);
                const unitPrice = parseFloat(qtyInput.dataset.price);

                let qty = parseInt(qtyInput.value);
                if (action === 'plus')  qty++;
                if (action === 'minus') qty = Math.max(1, qty - 1);

                qtyInput.value = qty;

                const subtotal   = unitPrice * qty;
                const subtotalEl = document.getElementById(`subtotal-${index}`);
                subtotalEl.textContent = '₱' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                checkbox.dataset.price = subtotal;

                fetch(`update_cart_qty.php?index=${index}&qty=${qty}`).catch(() => {});
                updateFooter();
            });
        });

        selectAll.addEventListener('change', () => {
            getCheckboxes().forEach(cb => cb.checked = selectAll.checked);
            updateFooter();
        });
        getCheckboxes().forEach(cb => cb.addEventListener('change', updateFooter));
        updateFooter();
    }

    // ── THEME ──
    (function () {
        var themeBtn = document.getElementById('themeToggle');
        var iconMoon = document.getElementById('iconMoon');
        var iconSun  = document.getElementById('iconSun');
        var KEY      = 'gz_theme';

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