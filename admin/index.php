<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$show_login          = !is_logged_in();
$show_first_login    = is_logged_in() && setting('first_login') === '1';
$csrf                = is_logged_in() ? csrf_token() : '';
$unread_count        = 0;

if (is_logged_in()) {
    $unread_count = (int)get_db()->query("SELECT COUNT(*) FROM submissions WHERE is_read = 0")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — HR Lighting Services</title>
  <link rel="stylesheet" href="../style.css" />
  <style>
    textarea { min-height: 90px; }
    nav { top: 0; }

    /* ── PAGES / SECTIONS ────────────────────────────────────────── */
    .page { display: none; border-bottom: 1px solid var(--c-border); }
    .page.active { display: block; }
    .section-head-action { margin-left: auto; }

    /* ── LOGIN ───────────────────────────────────────────────────── */
    .login-outer {
      min-height: calc(100vh - var(--banner-h) - var(--nav-h));
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--section-v) var(--gutter);
    }

    .login-box {
      width: 100%;
      max-width: 380px;
      border: 1px solid var(--c-border);
      padding: 3rem 2.5rem;
    }

    .login-box h1 {
      font-family: var(--f-display);
      font-style: italic;
      font-size: 2.25rem;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 0.5rem;
    }
    .login-sub {
      font-size: 0.8125rem;
      color: var(--c-muted);
      margin-bottom: 2rem;
    }

    .login-error {
      border: 1px solid rgba(239,68,68,0.4);
      padding: 0.75rem 1rem;
      font-size: 0.8125rem;
      color: #fca5a5;
      margin-bottom: 1rem;
      display: none;
    }

    /* ── FIRST LOGIN BANNER ──────────────────────────────────────── */
    .first-login-banner {
      border: 1px solid rgba(251,191,36,0.4);
      padding: 1rem 1.25rem;
      margin-top: 1.5rem;
      font-size: 0.8125rem;
      color: #fde68a;
    }
    .first-login-banner strong { display: block; margin-bottom: 0.25rem; }

    /* ── CARDS ───────────────────────────────────────────────────── */
    .card {
      border-bottom: 1px solid var(--c-border);
      padding-block: 2rem;
    }
    .card:last-child { border-bottom: none; padding-bottom: 0; }

    .card-title {
      font-family: var(--f-display);
      font-size: 1.25rem;
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
    }

    /* ── FORMS & INPUTS ──────────────────────────────────────────── */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1rem; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group.full { grid-column: 1 / -1; }

    /* ── BUTTONS ─────────────────────────────────────────────────── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 0.75rem 1.5rem;
      border-radius: 0;
      font-family: var(--f-mono);
      font-size: 0.8125rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      border: none;
      transition: background 0.15s, box-shadow 0.15s;
    }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .btn-primary { background: linear-gradient(135deg, var(--c-accent), #7C3AED); color: #fff; }
    .btn-primary:hover:not(:disabled) { background: linear-gradient(135deg, var(--c-accent), var(--c-accent-light)); box-shadow: 0 0 24px rgba(155,92,246,0.4); }

    .btn-danger  { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .btn-danger:hover:not(:disabled)  { background: rgba(239,68,68,0.25); }

    .btn-ghost   { background: rgba(155,92,246,0.1); color: var(--c-accent-light); }
    .btn-ghost:hover:not(:disabled)   { background: rgba(155,92,246,0.2); }

    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.6875rem; }

    /* ── ALERTS ──────────────────────────────────────────────────── */
    .alert {
      padding: 0.75rem 1rem;
      font-size: 0.8125rem;
      margin-bottom: 1.5rem;
      display: none;
      border: 1px solid var(--c-border);
    }
    .alert.show { display: block; }
    .alert-success { border-color: rgba(34,197,94,0.3); color: #86efac; }
    .alert-error   { border-color: rgba(239,68,68,0.3);  color: #fca5a5; }

    /* ── LIST ITEMS ──────────────────────────────────────────────── */
    .item-list { display: flex; flex-direction: column; }

    .item-card {
      border-bottom: 1px solid var(--c-border);
      padding-block: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }
    .item-card:last-child { border-bottom: none; }
    .item-card-info { flex: 1; min-width: 0; }
    .item-card-info strong { display: block; font-size: 0.9375rem; font-weight: 700; margin-bottom: 0.15rem; }
    .item-card-info span   { font-size: 0.8125rem; color: var(--c-muted); }
    .item-card-actions { display: flex; gap: 8px; flex-shrink: 0; }

    /* Tags */
    .tag-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }

    /* ── MODAL ───────────────────────────────────────────────────── */
    .modal-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(4px);
      z-index: 500;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }
    .modal-backdrop.open { display: flex; }

    .modal {
      background: var(--c-surface);
      border: 1px solid var(--c-border);
      padding: 2rem;
      width: 100%;
      max-width: 520px;
      position: relative;
    }
    .modal h2 {
      font-family: var(--f-display);
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      line-height: 1;
    }
    .modal-close {
      position: absolute;
      top: 1rem; right: 1.25rem;
      background: none; border: none;
      font-size: 1.25rem; color: var(--c-muted);
      cursor: pointer; line-height: 1;
    }
    .modal-close:hover { color: var(--c-fg); }
    .modal-footer { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; }

    /* ── INBOX ───────────────────────────────────────────────────── */
    .submission {
      border-bottom: 1px solid var(--c-border);
      padding-block: 1.5rem;
      position: relative;
    }
    .submission:last-child { border-bottom: none; }
    .submission.unread { border-left: 3px solid var(--c-accent); padding-left: 1.25rem; }
    .submission-meta { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem; align-items: baseline; }
    .submission-meta strong { font-size: 0.9375rem; font-weight: 700; }
    .submission-meta span  { font-size: 0.8125rem; color: var(--c-muted); }
    .submission p { font-size: 0.875rem; color: #d1d1e0; line-height: 1.65; white-space: pre-wrap; }
    .submission-actions { display: flex; gap: 8px; margin-top: 1rem; flex-wrap: wrap; }
    .unread-dot {
      width: 7px; height: 7px; border-radius: 50%; background: var(--c-accent);
      display: inline-block; margin-right: 6px; flex-shrink: 0;
    }

    /* ── PORTFOLIO ADMIN ─────────────────────────────────────────── */
    .upload-zone {
      border: 2px dashed var(--c-border);
      padding: 2.5rem;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      margin-bottom: 2rem;
    }
    .upload-zone.drag-over {
      border-color: var(--c-accent);
      background: rgba(155,92,246,0.07);
    }
    .upload-zone-label {
      font-size: var(--s-13);
      color: var(--c-muted);
      display: block;
      margin-bottom: 1rem;
    }
    .upload-zone input[type=file] { display: none; }

    .photo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 12px;
    }
    .photo-card {
      border: 1px solid var(--c-border);
      background: var(--c-surface);
      overflow: hidden;
    }
    .photo-card img {
      width: 100%;
      aspect-ratio: 1;
      object-fit: cover;
      display: block;
    }
    .photo-card-body { padding: 0.5rem; }
    .photo-caption-input {
      font-size: 0.6875rem;
      padding: 0.35rem 0.5rem;
      margin-bottom: 6px;
    }
    .photo-card-actions { display: flex; gap: 6px; }
    .photo-card-actions .btn { flex: 1; justify-content: center; font-size: 0.6rem; padding: 0.3rem; }

    /* ── HAMBURGER ───────────────────────────────────────────────── */
    .nav-hamburger {
      display: none;
      flex-direction: column;
      justify-content: center;
      gap: 5px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      flex-shrink: 0;
    }
    .nav-hamburger span {
      display: block;
      width: 22px;
      height: 2px;
      background: var(--c-muted);
      transition: transform 0.2s, opacity 0.2s;
    }
    .nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); background: var(--c-fg); }
    .nav-hamburger.open span:nth-child(2) { opacity: 0; }
    .nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); background: var(--c-fg); }

    /* ── RESPONSIVE ──────────────────────────────────────────────── */
    @media (max-width: 860px) {
      .form-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 700px) {
      /* Show hamburger, hide inline nav links */
      .nav-hamburger { display: flex; }
      .nav-links {
        display: none;
        position: absolute;
        top: 100%;
        left: 0; right: 0;
        background: var(--c-surface);
        border-bottom: 1px solid var(--c-border);
        flex-direction: column;
        gap: 0;
        z-index: 190;
      }
      .nav-links.open { display: flex; }
      .nav-links li { width: 100%; }
      .nav-links button.nav-item {
        width: 100%;
        text-align: left;
        padding: 0.875rem var(--gutter);
        font-size: 0.9375rem;
        border-bottom: 1px solid rgba(155,92,246,0.08);
      }
      .nav-links li:last-child button.nav-item { border-bottom: none; }

      /* Section head: wrap action button to its own line */
      .section-head-action { width: 100%; margin-left: 0; }
      .section-head-action .btn { width: 100%; justify-content: center; }
    }

    @media (max-width: 500px) {
      /* Item cards: stack actions below info */
      .item-card { flex-wrap: wrap; gap: 0.75rem; }
      .item-card-actions { width: 100%; }

      /* Modals: slide up from bottom, full width */
      .modal-backdrop { align-items: flex-end; padding: 0; }
      .modal { max-height: 90dvh; overflow-y: auto; max-width: 100%; }
      .modal-footer { position: sticky; bottom: 0; background: var(--c-surface); padding-top: 1rem; margin-top: 1rem; }

      /* Login box: flush edges */
      .login-box { padding: 2rem 1.25rem; }
    }

  </style>
</head>
<body>

<?php if ($show_login): ?>
<!-- ═══════════════════════════════════════
     LOGIN SCREEN
═══════════════════════════════════════ -->

  <nav>
    <span class="nav-brand">HR <em>Lighting</em></span>
  </nav>

  <div class="login-outer">
    <div class="login-box">
      <h1>Sign In</h1>
      <p class="login-sub">Admin panel — enter your password to continue.</p>
      <div class="login-error" id="loginError"></div>
      <div class="form-group">
        <label for="loginPass">Password</label>
        <input type="password" id="loginPass" placeholder="Enter your password" autocomplete="current-password" />
      </div>
      <button class="btn btn-primary" style="width:100%;justify-content:center;" id="loginBtn">Sign In &rarr;</button>
    </div>
  </div>

<script>
  async function doLogin() {
    const pass = document.getElementById('loginPass').value;
    const err  = document.getElementById('loginError');
    const btn  = document.getElementById('loginBtn');
    if (!pass) return;

    btn.disabled = true;
    btn.textContent = 'Signing in…';
    err.style.display = 'none';

    const fd = new FormData();
    fd.append('action', 'login');
    fd.append('password', pass);

    try {
      const res  = await fetch('api.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (json.success) {
        location.reload();
      } else {
        err.textContent   = json.error || 'Incorrect password.';
        err.style.display = 'block';
        btn.disabled      = false;
        btn.textContent   = 'Sign In →';
      }
    } catch {
      err.textContent   = 'Connection error. Please try again.';
      err.style.display = 'block';
      btn.disabled      = false;
      btn.textContent   = 'Sign In →';
    }
  }

  document.getElementById('loginBtn').addEventListener('click', doLogin);
  document.getElementById('loginPass').addEventListener('keydown', e => {
    if (e.key === 'Enter') doLogin();
  });
</script>

<?php else: ?>
<!-- ═══════════════════════════════════════
     DASHBOARD
═══════════════════════════════════════ -->

  <nav id="navbar">
    <span class="nav-brand">HR <em>Lighting</em></span>
    <ul class="nav-links" id="navLinks">
      <li><button class="nav-item active" data-page="settings">Settings</button></li>
      <li><button class="nav-item" data-page="services">Services</button></li>
      <li><button class="nav-item" data-page="productions">Productions</button></li>
      <li><button class="nav-item" data-page="skills">Skills</button></li>
      <li><button class="nav-item" data-page="portfolio">Portfolio</button></li>
      <li><button class="nav-item" data-page="inbox">Inbox<?php if ($unread_count > 0): ?><span class="badge"><?= $unread_count ?></span><?php endif; ?></button></li>
      <li><button class="nav-item" data-page="password">Password</button></li>
      <li><button class="nav-item" id="logoutBtn">Sign Out</button></li>
    </ul>
    <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <?php if ($show_first_login): ?>
  <div class="wrap">
    <div class="first-login-banner">
      <strong>Welcome! Please change your password before doing anything else.</strong>
      The temporary password is <code>hrls2026</code> — click <strong>Password</strong> in the nav to change it now.
    </div>
  </div>
  <?php endif; ?>

  <!-- ── SITE SETTINGS ── -->
  <div class="page active" id="page-settings">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">01</span>
          <h2 class="section-title">Site Settings</h2>
          <div class="section-head-action">
            <button class="btn btn-primary" id="saveSettingsBtn">Save Changes</button>
          </div>
        </div>

        <div class="alert" id="settingsAlert"></div>

        <div class="card">
          <div class="card-title">Hero Section</div>
          <div class="form-group">
            <label>Location Badge (top of page)</label>
            <input type="text" id="s_hero_badge" placeholder="e.g. Based in Stratford-upon-Avon" />
          </div>
          <div class="form-group">
            <label>Main Heading</label>
            <input type="text" id="s_hero_heading" placeholder="e.g. Lighting Design & Technical Production" />
          </div>
          <div class="form-group">
            <label>Subtext (paragraph under heading)</label>
            <textarea id="s_hero_subtext" rows="3"></textarea>
          </div>
        </div>

        <div class="card">
          <div class="card-title">About Section</div>
          <div class="form-group">
            <label>Your Name / Heading</label>
            <input type="text" id="s_about_heading" />
          </div>
          <div class="form-group">
            <label>First Paragraph</label>
            <textarea id="s_about_text_1" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Second Paragraph</label>
            <textarea id="s_about_text_2" rows="3"></textarea>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Stats (the two big numbers)</div>
          <div class="form-grid">
            <div class="form-group">
              <label>First Number (e.g. 8+)</label>
              <input type="text" id="s_stat_1_number" />
            </div>
            <div class="form-group">
              <label>First Label</label>
              <input type="text" id="s_stat_1_label" />
            </div>
            <div class="form-group">
              <label>Second Number (e.g. 3)</label>
              <input type="text" id="s_stat_2_number" />
            </div>
            <div class="form-group">
              <label>Second Label</label>
              <input type="text" id="s_stat_2_label" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Contact Details</div>
          <div class="form-group">
            <label>Address</label>
            <input type="text" id="s_contact_address" />
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" id="s_contact_phone" />
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" id="s_contact_email" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── SERVICES ── -->
  <div class="page" id="page-services">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">02</span>
          <h2 class="section-title">Services</h2>
          <div class="section-head-action">
            <button class="btn btn-primary" id="addServiceBtn">+ Add Service</button>
          </div>
        </div>
        <div class="alert" id="servicesAlert"></div>
        <div class="item-list" id="servicesList">
          <p style="color:var(--c-muted)">Loading…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── PRODUCTIONS ── -->
  <div class="page" id="page-productions">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">03</span>
          <h2 class="section-title">Productions</h2>
          <div class="section-head-action">
            <button class="btn btn-primary" id="addProductionBtn">+ Add Production</button>
          </div>
        </div>
        <div class="alert" id="productionsAlert"></div>
        <div class="item-list" id="productionsList">
          <p style="color:var(--c-muted)">Loading…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── SKILLS ── -->
  <div class="page" id="page-skills">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">04</span>
          <h2 class="section-title">Skills</h2>
          <div class="section-head-action">
            <button class="btn btn-primary" id="addSkillBtn">+ Add Skill</button>
          </div>
        </div>
        <div class="alert" id="skillsAlert"></div>
        <div class="item-list" id="skillsList">
          <p style="color:var(--c-muted)">Loading…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── PORTFOLIO ── -->
  <div class="page" id="page-portfolio">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">05</span>
          <h2 class="section-title">Portfolio</h2>
        </div>
        <div class="alert" id="portfolioAlert"></div>

        <div class="upload-zone" id="uploadZone">
          <label for="photoFileInput">
            <span class="upload-zone-label">Drag &amp; drop photos here, or click to select files</span>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('photoFileInput').click()">Choose Photos</button>
          </label>
          <input type="file" id="photoFileInput" accept="image/jpeg,image/png,image/webp,image/gif" multiple />
        </div>

        <div class="photo-grid" id="photoGrid">
          <p style="color:var(--c-muted)">Loading…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── INBOX ── -->
  <div class="page" id="page-inbox">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">06</span>
          <h2 class="section-title">Inbox</h2>
        </div>
        <div class="alert" id="inboxAlert"></div>
        <div id="inboxList">
          <p style="color:var(--c-muted)">Loading…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── PASSWORD ── -->
  <div class="page" id="page-password">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">07</span>
          <h2 class="section-title">Change Password</h2>
        </div>
        <div class="alert" id="passwordAlert"></div>
        <div style="max-width: 400px;">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" id="currentPass" autocomplete="current-password" />
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" id="newPass" autocomplete="new-password" />
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" id="confirmPass" autocomplete="new-password" />
          </div>
          <button class="btn btn-primary" id="changePassBtn">Update Password &rarr;</button>
        </div>
      </div>
    </div>
  </div>

<!-- ═══════════════════════════════════════
     MODALS
═══════════════════════════════════════ -->

<!-- Service modal -->
<div class="modal-backdrop" id="serviceModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('serviceModal')">✕</button>
    <h2 id="serviceModalTitle">Add Service</h2>
    <input type="hidden" id="service_id" value="" />
    <div class="form-group">
      <label>Icon (emoji)</label>
      <input type="text" id="service_icon" placeholder="e.g. 💡" maxlength="4" />
    </div>
    <div class="form-group">
      <label>Title</label>
      <input type="text" id="service_title" placeholder="e.g. Lighting Design" />
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea id="service_body" rows="3" placeholder="Describe this service…"></textarea>
    </div>
    <div class="form-group">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" id="service_featured" style="width:auto;margin:0;" />
        Make this the featured (purple) card
      </label>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('serviceModal')">Cancel</button>
      <button class="btn btn-primary" id="saveServiceBtn">Save Service</button>
    </div>
  </div>
</div>

<!-- Production modal -->
<div class="modal-backdrop" id="productionModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('productionModal')">✕</button>
    <h2 id="productionModalTitle">Add Production</h2>
    <input type="hidden" id="prod_id" value="" />
    <div class="form-group">
      <label>Show Title</label>
      <input type="text" id="prod_title" placeholder="e.g. Mary Poppins" />
    </div>
    <div class="form-group">
      <label>Venue / Company</label>
      <input type="text" id="prod_venue" placeholder="e.g. Stratford-upon-Avon High School" />
    </div>
    <div class="form-group">
      <label>Year</label>
      <input type="number" id="prod_year" min="2000" max="2100" value="<?= date('Y') ?>" />
    </div>
    <div class="form-group">
      <label>Your Roles (comma-separated)</label>
      <input type="text" id="prod_tags" placeholder="e.g. LD, Operator, Technical DSM" />
      <span style="font-size:0.6875rem;color:var(--c-muted);margin-top:4px;">Separate each role with a comma</span>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('productionModal')">Cancel</button>
      <button class="btn btn-primary" id="saveProdBtn">Save Production</button>
    </div>
  </div>
</div>

<!-- Skill modal -->
<div class="modal-backdrop" id="skillModal">
  <div class="modal" style="max-width:380px;">
    <button class="modal-close" onclick="closeModal('skillModal')">✕</button>
    <h2 id="skillModalTitle">Add Skill</h2>
    <input type="hidden" id="skill_id" value="" />
    <div class="form-group">
      <label>Skill Name</label>
      <input type="text" id="skill_name" placeholder="e.g. EOS ETC" />
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('skillModal')">Cancel</button>
      <button class="btn btn-primary" id="saveSkillBtn">Save Skill</button>
    </div>
  </div>
</div>

<script>
const CSRF = <?= json_encode($csrf) ?>;

// ── Navigation ──────────────────────────────────────────────

document.querySelectorAll('.nav-item[data-page]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('page-' + btn.dataset.page).classList.add('active');

    // Load data for the opened page
    const loaders = { settings: loadSettings, services: loadServices, productions: loadProductions, skills: loadSkills, portfolio: loadPortfolio, inbox: loadInbox };
    loaders[btn.dataset.page]?.();
  });
});

// ── Logout ──────────────────────────────────────────────────

document.getElementById('logoutBtn').addEventListener('click', async () => {
  const fd = new FormData(); fd.append('action', 'logout'); fd.append('csrf', CSRF);
  await fetch('api.php', { method: 'POST', body: fd });
  location.reload();
});

// ── Helpers ─────────────────────────────────────────────────

const _siteChannel = new BroadcastChannel('hrls_site');

async function api(action, data = {}) {
  const fd = new FormData();
  fd.append('action', action);
  fd.append('csrf', CSRF);
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  const res  = await fetch('api.php', { method: 'POST', body: fd });
  const json = await res.json();
  const contentActions = ['save_settings','save_service','delete_service','save_production','delete_production','save_skill','delete_skill','upload_photo','save_photo_caption','delete_photo'];
  if (json.success && contentActions.includes(action)) {
    _siteChannel.postMessage('reload');
  }
  return json;
}

async function apiGet(action) {
  const res = await fetch(`api.php?action=${action}`, { method: 'POST', body: (() => { const f = new FormData(); f.append('csrf', CSRF); return f; })() });
  return res.json();
}

function showAlert(id, msg, type = 'success') {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className   = `alert alert-${type} show`;
  setTimeout(() => el.classList.remove('show'), 4000);
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close modal on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
  backdrop.addEventListener('click', e => { if (e.target === backdrop) backdrop.classList.remove('open'); });
});

// ── SETTINGS ────────────────────────────────────────────────

async function loadSettings() {
  const data = await apiGet('get_settings');
  const map = {
    hero_badge: 's_hero_badge', hero_heading: 's_hero_heading', hero_subtext: 's_hero_subtext',
    about_heading: 's_about_heading', about_text_1: 's_about_text_1', about_text_2: 's_about_text_2',
    stat_1_number: 's_stat_1_number', stat_1_label: 's_stat_1_label',
    stat_2_number: 's_stat_2_number', stat_2_label: 's_stat_2_label',
    contact_address: 's_contact_address', contact_phone: 's_contact_phone', contact_email: 's_contact_email',
  };
  Object.entries(map).forEach(([key, elId]) => {
    const el = document.getElementById(elId);
    if (el && data[key] !== undefined) el.value = data[key];
  });
}

document.getElementById('saveSettingsBtn').addEventListener('click', async () => {
  const btn = document.getElementById('saveSettingsBtn');
  btn.disabled = true; btn.textContent = 'Saving…';
  const res = await api('save_settings', {
    hero_badge: document.getElementById('s_hero_badge').value,
    hero_heading: document.getElementById('s_hero_heading').value,
    hero_subtext: document.getElementById('s_hero_subtext').value,
    about_heading: document.getElementById('s_about_heading').value,
    about_text_1: document.getElementById('s_about_text_1').value,
    about_text_2: document.getElementById('s_about_text_2').value,
    stat_1_number: document.getElementById('s_stat_1_number').value,
    stat_1_label: document.getElementById('s_stat_1_label').value,
    stat_2_number: document.getElementById('s_stat_2_number').value,
    stat_2_label: document.getElementById('s_stat_2_label').value,
    contact_address: document.getElementById('s_contact_address').value,
    contact_phone: document.getElementById('s_contact_phone').value,
    contact_email: document.getElementById('s_contact_email').value,
  });
  btn.disabled = false; btn.textContent = 'Save Changes';
  if (res.success) showAlert('settingsAlert', 'Settings saved.');
  else showAlert('settingsAlert', res.error || 'Error saving.', 'error');
});

// ── SERVICES ────────────────────────────────────────────────

async function loadServices() {
  const services = await apiGet('get_services');
  const list = document.getElementById('servicesList');
  if (!services.length) { list.innerHTML = '<p style="color:var(--c-muted)">No services yet. Click Add Service.</p>'; return; }
  list.innerHTML = services.map(s => `
    <div class="item-card">
      <span style="font-size:24px">${s.icon}</span>
      <div class="item-card-info">
        <strong>${escHtml(s.title)} ${s.featured == 1 ? '<span class="tag">Featured</span>' : ''}</strong>
        <span>${escHtml(s.body.substring(0, 80))}…</span>
      </div>
      <div class="item-card-actions">
        <button class="btn btn-ghost btn-sm" onclick="editService(${JSON.stringify(s).replace(/"/g,'&quot;')})">Edit</button>
        <button class="btn btn-danger btn-sm" onclick="deleteService(${s.id}, '${escHtml(s.title)}')">Delete</button>
      </div>
    </div>
  `).join('');
}

document.getElementById('addServiceBtn').addEventListener('click', () => {
  document.getElementById('serviceModalTitle').textContent = 'Add Service';
  document.getElementById('service_id').value    = '';
  document.getElementById('service_icon').value  = '💡';
  document.getElementById('service_title').value = '';
  document.getElementById('service_body').value  = '';
  document.getElementById('service_featured').checked = false;
  openModal('serviceModal');
});

function editService(s) {
  document.getElementById('serviceModalTitle').textContent = 'Edit Service';
  document.getElementById('service_id').value    = s.id;
  document.getElementById('service_icon').value  = s.icon;
  document.getElementById('service_title').value = s.title;
  document.getElementById('service_body').value  = s.body;
  document.getElementById('service_featured').checked = s.featured == 1;
  openModal('serviceModal');
}

document.getElementById('saveServiceBtn').addEventListener('click', async () => {
  const btn = document.getElementById('saveServiceBtn');
  btn.disabled = true; btn.textContent = 'Saving…';
  const res = await api('save_service', {
    id:       document.getElementById('service_id').value,
    icon:     document.getElementById('service_icon').value,
    title:    document.getElementById('service_title').value,
    body:     document.getElementById('service_body').value,
    featured: document.getElementById('service_featured').checked ? 1 : 0,
  });
  btn.disabled = false; btn.textContent = 'Save Service';
  if (res.success) { closeModal('serviceModal'); loadServices(); showAlert('servicesAlert', 'Service saved.'); }
  else showAlert('servicesAlert', res.error || 'Error saving.', 'error');
});

async function deleteService(id, title) {
  if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
  const res = await api('delete_service', { id });
  if (res.success) { loadServices(); showAlert('servicesAlert', 'Service deleted.'); }
  else showAlert('servicesAlert', res.error || 'Error deleting.', 'error');
}

// ── PRODUCTIONS ─────────────────────────────────────────────

async function loadProductions() {
  const prods = await apiGet('get_productions');
  const list  = document.getElementById('productionsList');
  if (!prods.length) { list.innerHTML = '<p style="color:var(--c-muted)">No productions yet. Click Add Production.</p>'; return; }
  list.innerHTML = prods.map(p => `
    <div class="item-card">
      <div style="font-family:var(--f-display);font-size:2rem;font-weight:700;color:var(--c-border);min-width:52px;text-align:center;line-height:1">${p.year}</div>
      <div class="item-card-info">
        <strong>${escHtml(p.title)}</strong>
        <span>${escHtml(p.venue)}</span>
        <div class="tag-list">${(p.tags||[]).map(t => `<span class="tag">${escHtml(t)}</span>`).join('')}</div>
      </div>
      <div class="item-card-actions">
        <button class="btn btn-ghost btn-sm" onclick="editProduction(${JSON.stringify(p).replace(/"/g,'&quot;')})">Edit</button>
        <button class="btn btn-danger btn-sm" onclick="deleteProduction(${p.id}, '${escHtml(p.title)}')">Delete</button>
      </div>
    </div>
  `).join('');
}

document.getElementById('addProductionBtn').addEventListener('click', () => {
  document.getElementById('productionModalTitle').textContent = 'Add Production';
  document.getElementById('prod_id').value    = '';
  document.getElementById('prod_title').value = '';
  document.getElementById('prod_venue').value = '';
  document.getElementById('prod_year').value  = new Date().getFullYear();
  document.getElementById('prod_tags').value  = '';
  openModal('productionModal');
});

function editProduction(p) {
  document.getElementById('productionModalTitle').textContent = 'Edit Production';
  document.getElementById('prod_id').value    = p.id;
  document.getElementById('prod_title').value = p.title;
  document.getElementById('prod_venue').value = p.venue;
  document.getElementById('prod_year').value  = p.year;
  document.getElementById('prod_tags').value  = (p.tags || []).join(', ');
  openModal('productionModal');
}

document.getElementById('saveProdBtn').addEventListener('click', async () => {
  const btn = document.getElementById('saveProdBtn');
  btn.disabled = true; btn.textContent = 'Saving…';
  const res = await api('save_production', {
    id:    document.getElementById('prod_id').value,
    title: document.getElementById('prod_title').value,
    venue: document.getElementById('prod_venue').value,
    year:  document.getElementById('prod_year').value,
    tags:  document.getElementById('prod_tags').value,
  });
  btn.disabled = false; btn.textContent = 'Save Production';
  if (res.success) { closeModal('productionModal'); loadProductions(); showAlert('productionsAlert', 'Production saved.'); }
  else showAlert('productionsAlert', res.error || 'Error saving.', 'error');
});

async function deleteProduction(id, title) {
  if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
  const res = await api('delete_production', { id });
  if (res.success) { loadProductions(); showAlert('productionsAlert', 'Production deleted.'); }
  else showAlert('productionsAlert', res.error || 'Error deleting.', 'error');
}

// ── SKILLS ───────────────────────────────────────────────────

async function loadSkills() {
  const skills = await apiGet('get_skills');
  const list   = document.getElementById('skillsList');
  if (!skills.length) { list.innerHTML = '<p style="color:var(--c-muted)">No skills yet. Click Add Skill.</p>'; return; }
  list.innerHTML = skills.map(s => `
    <div class="item-card">
      <div class="item-card-info">
        <strong>${escHtml(s.name)}</strong>
      </div>
      <div class="item-card-actions">
        <button class="btn btn-ghost btn-sm" onclick="editSkill(${JSON.stringify(s).replace(/"/g,'&quot;')})">Edit</button>
        <button class="btn btn-danger btn-sm" onclick="deleteSkill(${s.id}, '${escHtml(s.name)}')">Delete</button>
      </div>
    </div>
  `).join('');
}

document.getElementById('addSkillBtn').addEventListener('click', () => {
  document.getElementById('skillModalTitle').textContent = 'Add Skill';
  document.getElementById('skill_id').value   = '';
  document.getElementById('skill_name').value = '';
  openModal('skillModal');
});

function editSkill(s) {
  document.getElementById('skillModalTitle').textContent = 'Edit Skill';
  document.getElementById('skill_id').value   = s.id;
  document.getElementById('skill_name').value = s.name;
  openModal('skillModal');
}

document.getElementById('saveSkillBtn').addEventListener('click', async () => {
  const btn = document.getElementById('saveSkillBtn');
  btn.disabled = true; btn.textContent = 'Saving…';
  const res = await api('save_skill', {
    id:   document.getElementById('skill_id').value,
    name: document.getElementById('skill_name').value,
  });
  btn.disabled = false; btn.textContent = 'Save Skill';
  if (res.success) { closeModal('skillModal'); loadSkills(); showAlert('skillsAlert', 'Skill saved.'); }
  else showAlert('skillsAlert', res.error || 'Error saving.', 'error');
});

async function deleteSkill(id, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  const res = await api('delete_skill', { id });
  if (res.success) { loadSkills(); showAlert('skillsAlert', 'Skill deleted.'); }
  else showAlert('skillsAlert', res.error || 'Error deleting.', 'error');
}

// ── INBOX ────────────────────────────────────────────────────

async function loadInbox() {
  const subs = await apiGet('get_submissions');
  const list = document.getElementById('inboxList');
  if (!subs.length) {
    list.innerHTML = '<p style="color:var(--c-muted)">No enquiries yet. When someone fills in your contact form, they\'ll appear here.</p>';
    return;
  }
  list.innerHTML = subs.map(s => `
    <div class="submission ${s.is_read == 0 ? 'unread' : ''}" id="sub-${s.id}">
      <div class="submission-meta">
        ${s.is_read == 0 ? '<span class="unread-dot"></span>' : ''}
        <strong>${escHtml(s.name)}</strong>
        <span>${escHtml(s.email)}</span>
        ${s.phone ? `<span>${escHtml(s.phone)}</span>` : ''}
        ${s.project_type ? `<span>${escHtml(s.project_type)}</span>` : ''}
        <span style="margin-left:auto;font-size:0.6875rem;color:var(--c-muted)">${escHtml(s.submitted_at)}</span>
      </div>
      <p>${escHtml(s.message)}</p>
      <div class="submission-actions">
        <a href="mailto:${escHtml(s.email)}?subject=Re: Your enquiry to HR Lighting Services" class="btn btn-ghost btn-sm">Reply by Email</a>
        ${s.is_read == 0 ? `<button class="btn btn-ghost btn-sm" onclick="markRead(${s.id})">Mark as Read</button>` : ''}
        <button class="btn btn-danger btn-sm" onclick="deleteSubmission(${s.id})">Delete</button>
      </div>
    </div>
  `).join('');
}

async function markRead(id) {
  const res = await api('mark_read', { id });
  if (res.success) {
    const el = document.getElementById('sub-' + id);
    el.classList.remove('unread');
    el.querySelector('.unread-dot')?.remove();
    el.querySelector(`[onclick="markRead(${id})"]`)?.remove();
  }
}

async function deleteSubmission(id) {
  if (!confirm('Delete this enquiry permanently?')) return;
  const res = await api('delete_submission', { id });
  if (res.success) { document.getElementById('sub-' + id)?.remove(); showAlert('inboxAlert', 'Enquiry deleted.'); }
}

// ── PASSWORD ─────────────────────────────────────────────────

document.getElementById('changePassBtn').addEventListener('click', async () => {
  const btn     = document.getElementById('changePassBtn');
  const current = document.getElementById('currentPass').value;
  const newP    = document.getElementById('newPass').value;
  const confirm = document.getElementById('confirmPass').value;

  if (newP !== confirm) {
    showAlert('passwordAlert', 'New passwords do not match.', 'error'); return;
  }
  if (newP.length < 6) {
    showAlert('passwordAlert', 'Password must be at least 6 characters.', 'error'); return;
  }

  btn.disabled = true; btn.textContent = 'Updating…';
  const res = await api('change_password', { current_password: current, new_password: newP });
  btn.disabled = false; btn.textContent = 'Update Password →';

  if (res.success) {
    showAlert('passwordAlert', 'Password updated.');
    document.getElementById('currentPass').value = '';
    document.getElementById('newPass').value      = '';
    document.getElementById('confirmPass').value  = '';
    document.querySelector('.first-login-banner')?.remove();
  } else {
    showAlert('passwordAlert', res.error || 'Error updating password.', 'error');
  }
});

// ── PORTFOLIO ────────────────────────────────────────────────

async function loadPortfolio() {
  const photos = await apiGet('get_portfolio');
  const grid = document.getElementById('photoGrid');
  if (!photos.length) {
    grid.innerHTML = '<p style="color:var(--c-muted)">No photos yet. Upload some above.</p>';
    return;
  }
  grid.innerHTML = photos.map(p => `
    <div class="photo-card" id="photo-${p.id}">
      <img src="../uploads/${escHtml(p.filename)}" alt="${escHtml(p.caption)}" loading="lazy" />
      <div class="photo-card-body">
        <input class="photo-caption-input" type="text" placeholder="Caption (optional)"
               value="${escHtml(p.caption)}" data-id="${p.id}" />
        <div class="photo-card-actions">
          <button class="btn btn-ghost btn-sm" onclick="saveCaption(${p.id})">Save</button>
          <button class="btn btn-danger btn-sm" onclick="deletePhoto(${p.id})">Delete</button>
        </div>
      </div>
    </div>
  `).join('');
}

async function saveCaption(id) {
  const input = document.querySelector(`.photo-caption-input[data-id="${id}"]`);
  const res = await api('save_photo_caption', { id, caption: input.value });
  if (res.success) showAlert('portfolioAlert', 'Caption saved.');
  else showAlert('portfolioAlert', res.error || 'Error saving caption.', 'error');
}

async function deletePhoto(id) {
  if (!confirm('Delete this photo permanently?')) return;
  const res = await api('delete_photo', { id });
  if (res.success) {
    document.getElementById('photo-' + id)?.remove();
    showAlert('portfolioAlert', 'Photo deleted.');
    const grid = document.getElementById('photoGrid');
    if (!grid.querySelector('.photo-card')) {
      grid.innerHTML = '<p style="color:var(--c-muted)">No photos yet. Upload some above.</p>';
    }
  } else {
    showAlert('portfolioAlert', res.error || 'Error deleting.', 'error');
  }
}

async function uploadPhotos(files) {
  if (!files.length) return;
  const btn = document.querySelector('#uploadZone .btn');
  btn.disabled = true; btn.textContent = 'Uploading…';

  const fd = new FormData();
  fd.append('action', 'upload_photo');
  fd.append('csrf', CSRF);
  Array.from(files).forEach(f => fd.append('photos[]', f));

  try {
    const res  = await fetch('api.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) {
      showAlert('portfolioAlert', `${json.ids.length} photo${json.ids.length !== 1 ? 's' : ''} uploaded.`);
      loadPortfolio();
      _siteChannel.postMessage('reload');
    } else {
      showAlert('portfolioAlert', json.error || 'Upload failed.', 'error');
    }
  } catch {
    showAlert('portfolioAlert', 'Upload failed. Please try again.', 'error');
  }

  btn.disabled = false; btn.textContent = 'Choose Photos';
  document.getElementById('photoFileInput').value = '';
}

// File input change
document.getElementById('photoFileInput').addEventListener('change', e => {
  uploadPhotos(e.target.files);
});

// Drag-and-drop
const _uploadZone = document.getElementById('uploadZone');
_uploadZone.addEventListener('dragover', e => { e.preventDefault(); _uploadZone.classList.add('drag-over'); });
_uploadZone.addEventListener('dragleave', () => _uploadZone.classList.remove('drag-over'));
_uploadZone.addEventListener('drop', e => {
  e.preventDefault();
  _uploadZone.classList.remove('drag-over');
  uploadPhotos(e.dataTransfer.files);
});

// ── XSS helper ───────────────────────────────────────────────

function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Hamburger menu ───────────────────────────────────────────

const _hamburger = document.getElementById('navHamburger');
const _navLinks  = document.getElementById('navLinks');

_hamburger.addEventListener('click', () => {
  const open = _navLinks.classList.toggle('open');
  _hamburger.classList.toggle('open', open);
  _hamburger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
});

// Close on nav item click
_navLinks.querySelectorAll('button').forEach(btn => {
  btn.addEventListener('click', () => {
    _navLinks.classList.remove('open');
    _hamburger.classList.remove('open');
    _hamburger.setAttribute('aria-label', 'Open menu');
  });
});

// Close on outside tap
document.addEventListener('click', e => {
  if (!_navLinks.contains(e.target) && e.target !== _hamburger && !_hamburger.contains(e.target)) {
    _navLinks.classList.remove('open');
    _hamburger.classList.remove('open');
  }
});

// ── Init — load first page ────────────────────────────────────

loadSettings();
</script>

<?php endif; ?>
</body>
</html>
