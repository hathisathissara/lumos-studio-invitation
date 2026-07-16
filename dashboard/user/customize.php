<?php
session_start();
require '../../config/config.php';
require '../../templates/includes/music_library.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$wedding_id = $_SESSION['wedding_id'];
$msg_design   = "";
$msg_language = "";
$msg_music    = "";

// Ensure columns exist (safe to run repeatedly)
try {
    $pdo->exec("ALTER TABLE weddings ADD COLUMN invite_language VARCHAR(5) DEFAULT 'en'");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE weddings ADD COLUMN music_track VARCHAR(50) DEFAULT NULL");
} catch (Exception $e) {}

$all_templates = [
    'premium_gold'     => ['label' => 'Premium Gold',     'sub' => 'Dark Theme',     'primary' => '#8a6520', 'accent' => '#c9a05a', 'paper' => '#fdfaf5'],
    'minimal_light'    => ['label' => 'Minimal Light',    'sub' => 'Clean Theme',    'primary' => '#8f6f42', 'accent' => '#b8935a', 'paper' => '#faf9f6'],
    'terracotta_bloom' => ['label' => 'Terracotta Bloom', 'sub' => 'Warm Theme',     'primary' => '#8f4526', 'accent' => '#c1633d', 'paper' => '#faf5ec'],
    'plum_parchment'   => ['label' => 'Plum Parchment',   'sub' => 'Elegant Theme',  'primary' => '#4a2c3b', 'accent' => '#8a9a7e', 'paper' => '#f8f2e9'],
    'floral_garden'    => ['label' => 'Floral Garden',    'sub' => 'Floral Theme',   'primary' => '#a15873', 'accent' => '#8fac7a', 'paper' => '#fffaf7'],
    'beach_tropical'   => ['label' => 'Beach Tropical',   'sub' => 'Tropical Theme', 'primary' => '#9c7e3f', 'accent' => '#c9a961', 'paper' => '#f8f4ea'],
    'rustic_boho'      => ['label' => 'Rustic Boho',      'sub' => 'Boho Theme',     'primary' => '#9c6b4a', 'accent' => '#c98f6b', 'paper' => '#faf9f6'],
    'royal_classic'    => ['label' => 'Royal Classic',    'sub' => 'Royal Theme',    'primary' => '#1c2340', 'accent' => '#c6a15b', 'paper' => '#faf7f0'],
    'indian_royal'     => ['label' => 'Indian Royal',     'sub' => 'Indian Theme',   'primary' => '#6e1626', 'accent' => '#d4af37', 'paper' => '#fff8ec'],
];

$languages = [
    'en' => ['label' => 'English',  'native' => 'English'],
    'si' => ['label' => 'Sinhala',  'native' => 'සිංහල'],
    'ta' => ['label' => 'Tamil',    'native' => 'தமிழ்'],
];

// ---- Save: Design Template ----
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_design'])) {
    $template = $_POST['template_name'] ?? '';
    if (isset($all_templates[$template])) {
        $pdo->prepare("UPDATE weddings SET template_name = ? WHERE id = ? AND user_id = ?")
            ->execute([$template, $wedding_id, $user_id]);
        $msg_design = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Design template updated!</div>";
    } else {
        $msg_design = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please choose a valid template.</div>";
    }
}

// ---- Save: Language ----
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_language'])) {
    $lang = $_POST['invite_language'] ?? '';
    if (isset($languages[$lang])) {
        $pdo->prepare("UPDATE weddings SET invite_language = ? WHERE id = ? AND user_id = ?")
            ->execute([$lang, $wedding_id, $user_id]);
        $msg_language = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Invitation language updated!</div>";
    } else {
        $msg_language = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please choose a valid language.</div>";
    }
}

// ---- Save: Music ----
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_music'])) {
    $music_enabled = isset($_POST['music_enabled']) && $_POST['music_enabled'] == '1';
    $track = $_POST['music_track'] ?? '';

    if (!$music_enabled) {
        $pdo->prepare("UPDATE weddings SET music_track = NULL WHERE id = ? AND user_id = ?")
            ->execute([$wedding_id, $user_id]);
        $msg_music = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Background music turned off.</div>";
    } elseif (isset($MUSIC_LIBRARY[$track])) {
        $pdo->prepare("UPDATE weddings SET music_track = ? WHERE id = ? AND user_id = ?")
            ->execute([$track, $wedding_id, $user_id]);
        $msg_music = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Background music updated!</div>";
    } else {
        $msg_music = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please choose a track, or switch music off.</div>";
    }
}

// Load current wedding data
$stmtGetWed = $pdo->prepare("SELECT bride_name, groom_name, template_name, invite_language, music_track, slug FROM weddings WHERE id = ? AND user_id = ?");
$stmtGetWed->execute([$wedding_id, $user_id]);
$wedding_data = $stmtGetWed->fetch();

$current_template = !empty($wedding_data['template_name']) ? $wedding_data['template_name'] : 'premium_gold';
$current_language  = !empty($wedding_data['invite_language']) ? $wedding_data['invite_language'] : 'en';
$current_music     = $wedding_data['music_track'] ?? '';
$slug              = $wedding_data['slug'] ?? '';

require '../layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 16px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color: #dc2626; }

    .settings-accordion { display: flex; flex-direction: column; gap: 12px; max-width: 900px; margin: 0 auto; }

    .acc-item { background:#fff; border:1.5px solid #eef0f5; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.04); transition: box-shadow 0.3s; }
    .acc-item.open { box-shadow: 0 6px 28px rgba(0,0,0,0.09); }
    .acc-header { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; cursor:pointer; background:#fafbfc; border:none; width:100%; text-align:left; transition:background 0.2s; gap:14px; }
    .acc-header:hover { background:#f4f6fa; }
    .acc-header-left { display:flex; align-items:center; gap:14px; }
    .acc-icon { width:42px; height:42px; border-radius:11px; background:rgba(201,169,110,0.12); display:flex; align-items:center; justify-content:center; color:#c9a96e; font-size:1rem; flex-shrink:0; }
    .acc-title { font-size:0.97rem; font-weight:700; color:#1a1a2e; letter-spacing:-0.1px; }
    .acc-sub { font-size:0.78rem; color:#64748b; margin-top:2px; }
    .acc-chevron { width:30px; height:30px; border-radius:8px; background:rgba(201,169,110,0.08); display:flex; align-items:center; justify-content:center; color:#c9a96e; font-size:0.8rem; transition:transform 0.3s ease, background 0.2s; flex-shrink:0; }
    .acc-item.open .acc-chevron { transform: rotate(180deg); background: rgba(201,169,110,0.18); }
    .acc-body { max-height:0; overflow:hidden; transition:max-height 0.4s cubic-bezier(0.4,0,0.2,1), padding 0.3s ease; padding:0 22px; }
    .acc-item.open .acc-body { max-height:3000px; padding:22px 22px 26px; }

    .btn-save { background: linear-gradient(135deg, #1a1a2e, #2d2d50); color:#c9a96e; border:none; border-radius:10px; padding:12px 28px; font-family:'Inter',sans-serif; font-size:0.88rem; font-weight:700; cursor:pointer; transition:all 0.25s; display:inline-flex; align-items:center; gap:8px; margin-top:6px; }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,26,46,0.3); }

    /* ===== Template grid ===== */
    .tpl-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:16px; margin-bottom:18px; }
    .tpl-card { position:relative; border:2px solid #e2e8f0; border-radius:16px; overflow:hidden; cursor:pointer; transition: all 0.2s; background:#fff; }
    .tpl-card:hover { border-color:#c9a96e; transform: translateY(-2px); }
    .tpl-card.selected { border-color:#c9a96e; box-shadow: 0 0 0 3px rgba(201,169,110,0.2); }
    .tpl-card input { position:absolute; opacity:0; pointer-events:none; }
    .tpl-swatch { height:70px; }
    .tpl-body { padding:12px 14px; }
    .tpl-name { font-size:0.85rem; font-weight:700; color:#1a1a2e; }
    .tpl-sub { font-size:0.72rem; color:#94a3b8; margin-top:2px; }
    .tpl-check { position:absolute; top:8px; right:8px; width:22px; height:22px; border-radius:50%; background:#c9a96e; color:#fff; display:none; align-items:center; justify-content:center; font-size:0.7rem; }
    .tpl-card.selected .tpl-check { display:flex; }
    .tpl-test-link { display:inline-flex; align-items:center; gap:5px; font-size:0.72rem; color:#8f6f42; text-decoration:none; padding:8px 14px 4px; }
    .tpl-test-link:hover { text-decoration:underline; }

    /* ===== Language options ===== */
    .lang-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:14px; margin-bottom:18px; }
    .lang-card { border:2px solid #e2e8f0; border-radius:14px; padding:18px 14px; text-align:center; cursor:pointer; transition:all 0.2s; }
    .lang-card:hover { border-color:#c9a96e; }
    .lang-card.selected { border-color:#c9a96e; background:rgba(201,169,110,0.06); }
    .lang-card input { position:absolute; opacity:0; pointer-events:none; }
    .lang-native { font-size:1.15rem; font-weight:700; color:#1a1a2e; }
    .lang-english { font-size:0.75rem; color:#94a3b8; margin-top:4px; }

    /* ===== Music ===== */
    .music-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#f8fafc; border-radius:12px; margin-bottom:16px; }
    .switch { position:relative; display:inline-block; width:46px; height:26px; }
    .switch input { opacity:0; width:0; height:0; }
    .slider { position:absolute; cursor:pointer; inset:0; background:#cbd5e1; border-radius:26px; transition:.3s; }
    .slider:before { position:absolute; content:""; height:20px; width:20px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
    .switch input:checked + .slider { background:#c9a96e; }
    .switch input:checked + .slider:before { transform: translateX(20px); }

    .music-list { display:flex; flex-direction:column; gap:10px; }
    .music-item { display:flex; align-items:center; gap:12px; border:1.5px solid #e2e8f0; border-radius:12px; padding:12px 14px; cursor:pointer; transition:all 0.2s; }
    .music-item:hover { border-color:#c9a96e; }
    .music-item.selected { border-color:#c9a96e; background:rgba(201,169,110,0.06); }
    .music-item input { accent-color:#c9a96e; }
    .music-item-name { font-size:0.87rem; font-weight:600; color:#1a1a2e; flex:1; }
    .music-preview-btn { background:#eef0f5; border:none; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#1a1a2e; cursor:pointer; flex-shrink:0; }
    .music-preview-btn:hover { background:#c9a96e; color:#fff; }

    /* ===== Mobile sticky save bar ===== */
    .mobile-save-bar {
        position: fixed;
        left: 0; right: 0; bottom: 0;
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        background: #fff;
        border-top: 1.5px solid #eef0f5;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
        box-shadow: 0 -6px 24px rgba(0,0,0,0.1);
        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .mobile-save-bar.active { transform: translateY(0); }
    .msb-info { min-width: 0; }
    .msb-title { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
    .msb-sub { font-size: 0.94rem; font-weight: 700; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 55vw; margin-top: 1px; }
    .msb-save-btn { background: linear-gradient(135deg, #1a1a2e, #2d2d50); color: #c9a96e; border: none; border-radius: 10px; padding: 12px 22px; font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; transition: transform 0.15s; }
    .msb-save-btn:active { transform: scale(0.96); }

    @media (max-width: 767px) {
        .mobile-save-bar.active { display: flex; }
        .settings-accordion { padding-bottom: 86px; }
        /* Compact 2-column template grid so the whole list is shorter and easier to scan */
        .tpl-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .tpl-swatch { height: 56px; }
        .tpl-body { padding: 10px 12px; }
        .tpl-name { font-size: 0.8rem; }
        .tpl-test-link { padding: 6px 12px 2px; }
        /* Hide the in-form save buttons on mobile — the sticky bar replaces them */
        .acc-body > form > .btn-save { display: none; }
    }
</style>

<div class="settings-accordion">

    <!-- ======== 1. Design Template ======== -->
    <div class="acc-item open" id="acc-design">
        <button type="button" class="acc-header" onclick="toggleAcc('acc-design')">
            <div class="acc-header-left">
                <div class="acc-icon"><i class="fas fa-paint-brush"></i></div>
                <div>
                    <div class="acc-title">Design Template</div>
                    <div class="acc-sub">Choose the look & feel of your invitation</div>
                </div>
            </div>
            <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
        </button>
        <div class="acc-body">
            <?php echo $msg_design; ?>
            <form method="POST" action="customize.php" id="designForm">
                <div class="tpl-grid">
                    <?php foreach ($all_templates as $key => $tpl): ?>
                    <label class="tpl-card <?php echo $current_template === $key ? 'selected' : ''; ?>" data-tpl="<?php echo $key; ?>">
                        <input type="radio" name="template_name" value="<?php echo $key; ?>" <?php echo $current_template === $key ? 'checked' : ''; ?> onchange="selectTpl(this)">
                        <div class="tpl-swatch" style="background: linear-gradient(135deg, <?php echo $tpl['primary']; ?>, <?php echo $tpl['accent']; ?>);"></div>
                        <div class="tpl-body">
                            <div class="tpl-name"><?php echo $tpl['label']; ?></div>
                            <div class="tpl-sub"><?php echo $tpl['sub']; ?></div>
                        </div>
                        <div class="tpl-check"><i class="fas fa-check"></i></div>
                        <?php if (!empty($slug)): ?>
                        <a class="tpl-test-link" target="_blank" rel="noopener"
                           href="../../invite.php?slug=<?php echo urlencode($slug); ?>&preview_template=<?php echo urlencode($key); ?>"
                           onclick="event.stopPropagation();">
                            <i class="fas fa-external-link-alt"></i> Test this template
                        </a>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="update_design" class="btn-save">
                    <i class="fas fa-save"></i> Save Design Template
                </button>
            </form>
        </div>
    </div>

    <!-- ======== 2. Language ======== -->
    <div class="acc-item" id="acc-language">
        <button type="button" class="acc-header" onclick="toggleAcc('acc-language')">
            <div class="acc-header-left">
                <div class="acc-icon"><i class="fas fa-language"></i></div>
                <div>
                    <div class="acc-title">Invitation Language</div>
                    <div class="acc-sub">Interface labels shown to your guests</div>
                </div>
            </div>
            <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
        </button>
        <div class="acc-body">
            <?php echo $msg_language; ?>
            <p style="font-size:0.82rem; color:#64748b; margin-bottom:16px;">
                This changes labels like RSVP, Countdown and Programme headings. Anything you typed yourself
                (love story, venue, event names) stays exactly as you wrote it.
            </p>
            <form method="POST" action="customize.php" id="languageForm">
                <div class="lang-grid">
                    <?php foreach ($languages as $code => $l): ?>
                    <label class="lang-card <?php echo $current_language === $code ? 'selected' : ''; ?>" onclick="selectLang(this)">
                        <input type="radio" name="invite_language" value="<?php echo $code; ?>" <?php echo $current_language === $code ? 'checked' : ''; ?>>
                        <div class="lang-native"><?php echo $l['native']; ?></div>
                        <div class="lang-english"><?php echo $l['label']; ?></div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="update_language" class="btn-save">
                    <i class="fas fa-save"></i> Save Language
                </button>
            </form>
        </div>
    </div>

    <!-- ======== 3. Music ======== -->
    <div class="acc-item" id="acc-music">
        <button type="button" class="acc-header" onclick="toggleAcc('acc-music')">
            <div class="acc-header-left">
                <div class="acc-icon"><i class="fas fa-music"></i></div>
                <div>
                    <div class="acc-title">Background Music</div>
                    <div class="acc-sub">Optional music that plays on your invitation</div>
                </div>
            </div>
            <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
        </button>
        <div class="acc-body">
            <?php echo $msg_music; ?>
            <form method="POST" action="customize.php" id="musicForm">
                <div class="music-toggle-row">
                    <div>
                        <div style="font-size:0.88rem; font-weight:600; color:#1a1a2e;">Play music on my invitation</div>
                        <div style="font-size:0.76rem; color:#94a3b8; margin-top:2px;">Guests can always mute it themselves</div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="musicEnabledInput" name="music_enabled" value="1" <?php echo !empty($current_music) ? 'checked' : ''; ?> onchange="toggleMusicList(this.checked)">
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="music-list" id="musicList" style="<?php echo empty($current_music) ? 'display:none;' : ''; ?>">
                    <?php foreach ($MUSIC_LIBRARY as $key => $track): ?>
                    <label class="music-item <?php echo $current_music === $key ? 'selected' : ''; ?>" onclick="selectMusic(this)">
                        <input type="radio" name="music_track" value="<?php echo $key; ?>" <?php echo $current_music === $key ? 'checked' : ''; ?>>
                        <i class="fas fa-compact-disc" style="color:#c9a96e;"></i>
                        <span class="music-item-name"><?php echo htmlspecialchars($track['label']); ?></span>
                        <button type="button" class="music-preview-btn" onclick="event.preventDefault(); event.stopPropagation(); previewTrack('<?php echo htmlspecialchars($track['file']); ?>', this)">
                            <i class="fas fa-play"></i>
                        </button>
                    </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" name="update_music" class="btn-save">
                    <i class="fas fa-save"></i> Save Music Choice
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Mobile-only floating save bar: keeps the Save action reachable without scrolling past the template grid -->
<div class="mobile-save-bar" id="mobileSaveBar">
    <div class="msb-info">
        <div class="msb-title" id="msbTitle">Design Template</div>
        <div class="msb-sub" id="msbSub"></div>
    </div>
    <button type="button" class="msb-save-btn" id="msbSaveBtn" onclick="submitActiveForm()">
        <i class="fas fa-save"></i> Save
    </button>
</div>

<audio id="previewPlayer"></audio>

<script>
    // ===== Mobile sticky save bar =====
    const accBarConfig = {
        'acc-design':   { title: 'Design Template',      form: 'designForm' },
        'acc-language': { title: 'Invitation Language',  form: 'languageForm' },
        'acc-music':    { title: 'Background Music',     form: 'musicForm' }
    };

    function getSelectionText(accId) {
        if (accId === 'acc-design') {
            const sel = document.querySelector('.tpl-card.selected .tpl-name');
            return sel ? sel.textContent.trim() : 'Choose a template';
        }
        if (accId === 'acc-language') {
            const sel = document.querySelector('.lang-card.selected .lang-english');
            return sel ? sel.textContent.trim() : 'Choose a language';
        }
        if (accId === 'acc-music') {
            const enabled = document.getElementById('musicEnabledInput').checked;
            if (!enabled) return 'Off';
            const sel = document.querySelector('.music-item.selected .music-item-name');
            return sel ? sel.textContent.trim() : 'Choose a track';
        }
        return '';
    }

    function updateMobileBar(accId) {
        const bar = document.getElementById('mobileSaveBar');
        const conf = accBarConfig[accId];
        if (!conf) {
            bar.classList.remove('active');
            return;
        }
        document.getElementById('msbTitle').textContent = conf.title;
        document.getElementById('msbSub').textContent = getSelectionText(accId);
        bar.dataset.targetForm = conf.form;
        bar.classList.add('active');
    }

    function refreshBarIfOpen(accId) {
        const item = document.getElementById(accId);
        if (item && item.classList.contains('open')) updateMobileBar(accId);
    }

    function submitActiveForm() {
        const bar = document.getElementById('mobileSaveBar');
        const formId = bar.dataset.targetForm;
        if (!formId) return;
        const form = document.getElementById(formId);
        if (!form) return;
        if (form.requestSubmit) form.requestSubmit();
        else form.submit();
    }

    function toggleAcc(id) {
        const item = document.getElementById(id);
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.acc-item').forEach(el => el.classList.remove('open'));
        if (!isOpen) {
            item.classList.add('open');
            updateMobileBar(id);
        } else {
            updateMobileBar(null);
        }
    }

    function selectTpl(input) {
        document.querySelectorAll('.tpl-card').forEach(c => c.classList.remove('selected'));
        input.closest('.tpl-card').classList.add('selected');
        refreshBarIfOpen('acc-design');
    }

    function selectLang(label) {
        document.querySelectorAll('.lang-card').forEach(c => c.classList.remove('selected'));
        label.classList.add('selected');
        refreshBarIfOpen('acc-language');
    }

    function selectMusic(label) {
        document.querySelectorAll('.music-item').forEach(c => c.classList.remove('selected'));
        label.classList.add('selected');
        refreshBarIfOpen('acc-music');
    }

    function toggleMusicList(show) {
        document.getElementById('musicList').style.display = show ? 'flex' : 'none';
        refreshBarIfOpen('acc-music');
    }

    // Design Template accordion starts open by default — show the bar right away on mobile
    document.addEventListener('DOMContentLoaded', function () {
        updateMobileBar('acc-design');
    });

    // Simple audition player for preset tracks — one file playing at a time
    let currentPreviewBtn = null;
    function previewTrack(file, btn) {
        const player = document.getElementById('previewPlayer');
        if (currentPreviewBtn && currentPreviewBtn !== btn) {
            currentPreviewBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
        if (player.src.endsWith(file) && !player.paused) {
            player.pause();
            btn.innerHTML = '<i class="fas fa-play"></i>';
            currentPreviewBtn = null;
            return;
        }
        player.src = file;
        player.play().catch(() => {});
        btn.innerHTML = '<i class="fas fa-pause"></i>';
        currentPreviewBtn = btn;
    }
    document.getElementById('previewPlayer').addEventListener('ended', function () {
        if (currentPreviewBtn) currentPreviewBtn.innerHTML = '<i class="fas fa-play"></i>';
        currentPreviewBtn = null;
    });
</script>

<?php require '../layouts/footer.php'; ?>