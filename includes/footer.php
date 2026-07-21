        </main>
    </div>
</div>

<!-- Top Instant Progress Loader Bar -->
<div id="instant-loader" style="position:fixed;top:0;left:0;height:3px;background:linear-gradient(90deg, #3b82f6, #6366f1);width:0%;transition:width 0.2s ease, opacity 0.4s ease;z-index:99999;pointer-events:none;"></div>

<script src="js/signature_pad.js"></script>
<script>
function toggleSidebarToggle() {
    const container = document.getElementById('app-container');
    if (!container) return;
    
    if (window.innerWidth <= 992) {
        container.classList.toggle('mobile-open');
    } else {
        container.classList.toggle('collapsed');
        const isCollapsed = container.classList.contains('collapsed');
        localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
    }
}

// Instant Navigation Prefetch & Loading Bar
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
    navItems.forEach(function(item) {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                const container = document.getElementById('app-container');
                if (container) container.classList.remove('mobile-open');
            }
        });
    });

    // Top progress bar on link click & link prefetching on hover
    const loader = document.getElementById('instant-loader');
    document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"])').forEach(link => {
        ['pointerover', 'touchstart'].forEach(evt => {
            link.addEventListener(evt, function() {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('javascript:') && !document.querySelector(`link[rel="prefetch"][href="${href}"]`)) {
                    const pref = document.createElement('link');
                    pref.rel = 'prefetch';
                    pref.href = href;
                    document.head.appendChild(pref);
                }
            }, { passive: true, once: true });
        });

        link.addEventListener('click', function(e) {
            if (e.metaKey || e.ctrlKey) return;
            if (loader) {
                loader.style.opacity = '1';
                loader.style.width = '75%';
            }
        });
    });
});
</script>
</body>
</html>
