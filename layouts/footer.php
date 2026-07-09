<!-- FOOTER -->
<footer>
    <div class="footer-logo">Lumus Studio</div>
    <p>&copy; <?php echo date('Y'); ?> Lumus Studio &middot; Designed by Hathisa Thissara &middot; Sri Lanka</p>
    <div style="display:flex; gap:16px; margin-top:12px; flex-wrap:wrap; justify-content:center;">
        <a href="privacy.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Privacy Policy</a>
        <a href="terms.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Terms of Service</a>
        <a href="refund.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Refund Policy</a>
    </div>
    <div style="display:flex; gap:16px; margin-top:8px;">
        <?php if (!empty($is_logged_in)): ?>
            <a href="dashboard/index.php" style="color: var(--gold); text-decoration:none; font-size:0.85rem;">My Account</a>
        <?php else: ?>
            <a href="dashboard/register.php" style="color: var(--gold); text-decoration:none; font-size:0.85rem;">Get Started</a>
            <a href="dashboard/login.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Sign In</a>
        <?php endif; ?>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Navbar scroll effect
const navbar = document.getElementById('navbar');
if (navbar) {
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
}

// Close the Bootstrap mobile menu automatically when a nav link is tapped
document.addEventListener('DOMContentLoaded', function () {
    const collapseEl = document.getElementById('navMain');
    if (collapseEl && window.bootstrap) {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        collapseEl.querySelectorAll('.nav-link, .btn-nav').forEach(link => {
            link.addEventListener('click', () => {
                if (collapseEl.classList.contains('show')) {
                    bsCollapse.hide();
                }
            });
        });
    }
});

// Scroll reveal animation
const reveals = document.querySelectorAll('.reveal');
if (reveals.length > 0) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
            }
        });
    }, { threshold: 0.1 });
    reveals.forEach(el => observer.observe(el));
}

// Live countdown for theme previews
function updateThemeCountdowns() {
    const target = new Date("2026-12-31 00:00:00").getTime(); // Updated placeholder date
    const now = new Date().getTime();
    const dist = target - now;
    if (dist < 0) return;
    const d = String(Math.floor(dist / 86400000)).padStart(2, '0');
    const h = String(Math.floor((dist % 86400000) / 3600000)).padStart(2, '0');
    const m = String(Math.floor((dist % 3600000) / 60000)).padStart(2, '0');
    const s = String(Math.floor((dist % 60000) / 1000)).padStart(2, '0');
    document.querySelectorAll('.t-cd-days').forEach(el => el.textContent = d);
    document.querySelectorAll('.t-cd-hrs').forEach(el => el.textContent = h);
    document.querySelectorAll('.t-cd-min').forEach(el => el.textContent = m);
    document.querySelectorAll('.t-cd-sec').forEach(el => el.textContent = s);
}
updateThemeCountdowns();
setInterval(updateThemeCountdowns, 1000);
</script>
</body>
</html>