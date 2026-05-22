        </main>
    </div><!-- /main-area -->
</div><!-- /app-shell -->

<footer class="page-footer" role="contentinfo" style="margin-left: var(--sidebar-w);">
    &copy; <?= date('Y') ?> SiMoSoBa System. Dibuat untuk efisiensi farmasi Indonesia.
</footer>

<!-- ═══ GLOBAL SCRIPTS ════════════════════════════════════════════ -->
<script>
/* ── Theme Toggle ─────────────────────────────────────── */
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('simo-theme', next);
    updateThemeIcon(next);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('theme-icon');
    if (!icon) return;
    icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
}

// Init theme icon on load
(function() {
    const saved = localStorage.getItem('simo-theme') || 'light';
    updateThemeIcon(saved);
})();

/* ── Sidebar (Mobile) ─────────────────────────────────── */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const isOpen = sidebar.classList.toggle('open');
    overlay.classList.toggle('hidden', !isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.remove('open');
    overlay.classList.add('hidden');
    document.body.style.overflow = '';
}

// Show mobile menu button on small screens
(function() {
    const btn = document.getElementById('menu-toggle');
    if (!btn) return;
    function checkMobile() {
        btn.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
    }
    checkMobile();
    window.addEventListener('resize', checkMobile);
})();

/* ── Toast Notifications ─────────────────────────────── */
function showToast(message, type = 'info', duration = 3500) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#6366f1' };

    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="bi ${icons[type] || icons.info}" style="color:${colors[type] || colors.info};font-size:1rem;flex-shrink:0"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left:auto;color:var(--text-muted);background:none;border:none;cursor:pointer;font-size:1rem;padding:0 4px;"><i class="bi bi-x"></i></button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all .3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/* ── Confirm Delete Helper ───────────────────────────── */
function confirmDelete(message, callback) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        callback();
    }
}

/* ── API Helper ──────────────────────────────────────── */
async function apiRequest(url, method = 'GET', data = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (data && method !== 'GET') {
        opts.body = JSON.stringify(data);
    }
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
}

/* ── Page entrance animation ─────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('page-enter');
});
</script>
</body>
</html>