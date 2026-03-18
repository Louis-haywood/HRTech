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
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: -apple-system, 'Inter', BlinkMacSystemFont, sans-serif;
      background: #0d0d12;
      color: #fff;
      min-height: 100vh;
    }

    a { color: inherit; text-decoration: none; }

    /* ── LOGIN ───────────────────────────────────────────── */
    .login-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .login-box {
      background: #1a1525;
      border: 1px solid rgba(155,92,246,0.3);
      border-radius: 12px;
      padding: 48px 40px;
      width: 100%;
      max-width: 380px;
    }

    .login-box h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
    .login-box p  { font-size: 14px; color: #9d9db8; margin-bottom: 32px; }

    .login-box label { display: block; font-size: 12px; font-weight: 600; color: #9d9db8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }

    .login-box input {
      width: 100%;
      background: #0d0d12;
      border: 1px solid rgba(155,92,246,0.25);
      border-radius: 8px;
      color: #fff;
      font-size: 15px;
      padding: 12px 14px;
      margin-bottom: 20px;
      outline: none;
      font-family: inherit;
      transition: border-color 0.2s;
    }
    .login-box input:focus { border-color: #9b5cf6; }

    .login-error {
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.4);
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      color: #fca5a5;
      margin-bottom: 16px;
      display: none;
    }

    /* ── DASHBOARD ───────────────────────────────────────── */
    .dashboard { display: flex; min-height: 100vh; }

    /* Sidebar */
    .sidebar {
      width: 220px;
      min-height: 100vh;
      background: #1a1525;
      border-right: 1px solid rgba(155,92,246,0.2);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
    }

    .sidebar-logo {
      padding: 24px 20px;
      font-size: 16px;
      font-weight: 800;
      border-bottom: 1px solid rgba(155,92,246,0.15);
    }
    .sidebar-logo span { color: #9b5cf6; }
    .sidebar-logo small { display: block; font-size: 11px; font-weight: 400; color: #9d9db8; margin-top: 2px; }

    .sidebar-nav { flex: 1; padding: 16px 0; }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 20px;
      font-size: 14px;
      font-weight: 500;
      color: #9d9db8;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      position: relative;
    }
    .nav-item:hover { background: rgba(155,92,246,0.08); color: #fff; }
    .nav-item.active { background: rgba(155,92,246,0.15); color: #fff; }
    .nav-item.active::before {
      content: '';
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 3px;
      background: #9b5cf6;
      border-radius: 0 2px 2px 0;
    }

    .nav-icon { font-size: 16px; width: 20px; text-align: center; }

    .badge {
      background: #9b5cf6;
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 999px;
      margin-left: auto;
    }

    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid rgba(155,92,246,0.15);
    }

    .logout-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #9d9db8;
      cursor: pointer;
      background: none;
      border: none;
      font-family: inherit;
      width: 100%;
      padding: 8px 0;
      transition: color 0.2s;
    }
    .logout-btn:hover { color: #fca5a5; }

    /* Main content */
    .main {
      margin-left: 220px;
      flex: 1;
      padding: 40px;
      max-width: calc(100vw - 220px);
    }

    .page { display: none; }
    .page.active { display: block; }

    .page-header {
      margin-bottom: 32px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(155,92,246,0.15);
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .page-header h1 { font-size: 24px; font-weight: 800; }
    .page-header p  { font-size: 14px; color: #9d9db8; margin-top: 4px; }

    /* ── FORMS & INPUTS ──────────────────────────────────── */
    .card {
      background: #1a1525;
      border: 1px solid rgba(155,92,246,0.2);
      border-radius: 10px;
      padding: 28px;
      margin-bottom: 20px;
    }

    .card-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 20px;
      color: #fff;
    }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group.full { grid-column: 1 / -1; }

    label, .field-label {
      font-size: 12px;
      font-weight: 600;
      color: #9d9db8;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    input[type=text], input[type=email], input[type=tel],
    input[type=number], input[type=password], textarea, select {
      background: #0d0d12;
      border: 1px solid rgba(155,92,246,0.2);
      border-radius: 8px;
      color: #fff;
      font-family: inherit;
      font-size: 14px;
      padding: 10px 14px;
      outline: none;
      transition: border-color 0.2s;
      width: 100%;
      -webkit-appearance: none;
      appearance: none;
    }
    input:focus, textarea:focus, select:focus { border-color: #9b5cf6; }
    input::placeholder, textarea::placeholder { color: #4a4a6a; }
    textarea { resize: vertical; min-height: 90px; }

    /* ── BUTTONS ─────────────────────────────────────────── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 18px;
      border-radius: 6px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: background 0.2s, opacity 0.2s;
    }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .btn-primary { background: #9b5cf6; color: #fff; }
    .btn-primary:hover:not(:disabled) { background: #7c3aed; }

    .btn-danger  { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .btn-danger:hover:not(:disabled)  { background: rgba(239,68,68,0.25); }

    .btn-ghost   { background: rgba(155,92,246,0.1); color: #c084fc; }
    .btn-ghost:hover:not(:disabled)   { background: rgba(155,92,246,0.2); }

    .btn-sm { padding: 6px 12px; font-size: 13px; }

    /* ── ALERTS ──────────────────────────────────────────── */
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
      display: none;
    }
    .alert.show { display: block; }
    .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #86efac; }
    .alert-error   { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.3);  color: #fca5a5; }

    /* ── LIST ITEMS ──────────────────────────────────────── */
    .item-list { display: flex; flex-direction: column; gap: 12px; }

    .item-card {
      background: #0d0d12;
      border: 1px solid rgba(155,92,246,0.15);
      border-radius: 8px;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .item-card-info { flex: 1; min-width: 0; }
    .item-card-info strong { display: block; font-size: 14px; font-weight: 600; margin-bottom: 2px; }
    .item-card-info span  { font-size: 13px; color: #9d9db8; }
    .item-card-actions { display: flex; gap: 8px; flex-shrink: 0; }

    /* Tags display */
    .tag-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .tag {
      background: rgba(155,92,246,0.15);
      color: #c084fc;
      padding: 2px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 500;
    }

    /* ── MODAL ───────────────────────────────────────────── */
    .modal-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(4px);
      z-index: 500;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .modal-backdrop.open { display: flex; }

    .modal {
      background: #1a1525;
      border: 1px solid rgba(155,92,246,0.3);
      border-radius: 12px;
      padding: 32px;
      width: 100%;
      max-width: 520px;
      position: relative;
    }
    .modal h2 { font-size: 18px; font-weight: 700; margin-bottom: 24px; }
    .modal-close {
      position: absolute;
      top: 16px; right: 20px;
      background: none; border: none;
      font-size: 20px; color: #9d9db8;
      cursor: pointer; line-height: 1;
    }
    .modal-close:hover { color: #fff; }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }

    /* ── INBOX ───────────────────────────────────────────── */
    .submission {
      background: #0d0d12;
      border: 1px solid rgba(155,92,246,0.15);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 12px;
      position: relative;
    }
    .submission.unread { border-left: 3px solid #9b5cf6; }
    .submission-meta { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 10px; }
    .submission-meta strong { font-size: 15px; font-weight: 700; }
    .submission-meta span  { font-size: 13px; color: #9d9db8; }
    .submission p { font-size: 14px; color: #d1d1e0; line-height: 1.65; white-space: pre-wrap; }
    .submission-actions { display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
    .unread-dot {
      width: 8px; height: 8px; border-radius: 50%; background: #9b5cf6;
      display: inline-block; margin-right: 6px;
    }

    /* First login warning */
    .first-login-banner {
      background: rgba(251,191,36,0.1);
      border: 1px solid rgba(251,191,36,0.4);
      border-radius: 10px;
      padding: 16px 20px;
      margin-bottom: 24px;
      font-size: 14px;
      color: #fde68a;
    }
    .first-login-banner strong { display: block; margin-bottom: 4px; }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar { width: 100%; min-height: auto; position: relative; flex-direction: row; flex-wrap: wrap; }
      .sidebar-nav { display: flex; flex-direction: row; flex-wrap: wrap; padding: 8px; }
      .nav-item { padding: 8px 12px; font-size: 12px; }
      .main { margin-left: 0; padding: 20px; max-width: 100%; }
      .dashboard { flex-direction: column; }
      .form-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<?php if ($show_login): ?>
<!-- ═══════════════════════════════════════
     LOGIN SCREEN
═══════════════════════════════════════ -->
<div class="login-wrap">
  <div class="login-box">
    <h1>HR Lighting</h1>
    <p>Admin panel — please sign in.</p>
    <div class="login-error" id="loginError"></div>
    <label for="loginPass">Password</label>
    <input type="password" id="loginPass" placeholder="Enter your password" autocomplete="current-password" />
    <button class="btn btn-primary" style="width:100%;justify-content:center;" id="loginBtn">Sign In</button>
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
        btn.textContent   = 'Sign In';
      }
    } catch {
      err.textContent   = 'Connection error. Please try again.';
      err.style.display = 'block';
      btn.disabled      = false;
      btn.textContent   = 'Sign In';
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
<div class="dashboard">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      HR <span>Lighting</span>
      <small>Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
      <button class="nav-item active" data-page="settings">
        <span class="nav-icon">⚙️</span> Site Settings
      </button>
      <button class="nav-item" data-page="services">
        <span class="nav-icon">💡</span> Services
      </button>
      <button class="nav-item" data-page="productions">
        <span class="nav-icon">🎭</span> Productions
      </button>
      <button class="nav-item" data-page="skills">
        <span class="nav-icon">🔧</span> Skills
      </button>
      <button class="nav-item" data-page="inbox">
        <span class="nav-icon">📬</span> Inbox
        <?php if ($unread_count > 0): ?>
          <span class="badge"><?= $unread_count ?></span>
        <?php endif; ?>
      </button>
      <button class="nav-item" data-page="password">
        <span class="nav-icon">🔑</span> Password
      </button>
    </nav>

    <div class="sidebar-footer">
      <button class="logout-btn" id="logoutBtn">
        <span>↩</span> Sign Out
      </button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main">

    <?php if ($show_first_login): ?>
    <div class="first-login-banner">
      <strong>⚠️ Welcome! Please change your password before doing anything else.</strong>
      The temporary password is <code>hrls2026</code> — click <strong>Password</strong> in the sidebar to change it now.
    </div>
    <?php endif; ?>

    <!-- ── SITE SETTINGS ── -->
    <div class="page active" id="page-settings">
      <div class="page-header">
        <div>
          <h1>Site Settings</h1>
          <p>Edit the text and contact details shown on your website.</p>
        </div>
        <button class="btn btn-primary" id="saveSettingsBtn">Save Changes</button>
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

    <!-- ── SERVICES ── -->
    <div class="page" id="page-services">
      <div class="page-header">
        <div>
          <h1>Services</h1>
          <p>The three service cards shown on your homepage. The purple (featured) one appears first.</p>
        </div>
        <button class="btn btn-primary" id="addServiceBtn">+ Add Service</button>
      </div>
      <div class="alert" id="servicesAlert"></div>
      <div class="item-list" id="servicesList">
        <p style="color:#9d9db8">Loading…</p>
      </div>
    </div>

    <!-- ── PRODUCTIONS ── -->
    <div class="page" id="page-productions">
      <div class="page-header">
        <div>
          <h1>Productions</h1>
          <p>Every production shown in your portfolio. Add, edit, or remove entries here.</p>
        </div>
        <button class="btn btn-primary" id="addProductionBtn">+ Add Production</button>
      </div>
      <div class="alert" id="productionsAlert"></div>
      <div class="item-list" id="productionsList">
        <p style="color:#9d9db8">Loading…</p>
      </div>
    </div>

    <!-- ── SKILLS ── -->
    <div class="page" id="page-skills">
      <div class="page-header">
        <div>
          <h1>Skills</h1>
          <p>Technical skills shown in your about section.</p>
        </div>
        <button class="btn btn-primary" id="addSkillBtn">+ Add Skill</button>
      </div>
      <div class="alert" id="skillsAlert"></div>
      <div class="item-list" id="skillsList">
        <p style="color:#9d9db8">Loading…</p>
      </div>
    </div>

    <!-- ── INBOX ── -->
    <div class="page" id="page-inbox">
      <div class="page-header">
        <div>
          <h1>Inbox</h1>
          <p>Contact form enquiries sent through your website.</p>
        </div>
      </div>
      <div class="alert" id="inboxAlert"></div>
      <div id="inboxList">
        <p style="color:#9d9db8">Loading…</p>
      </div>
    </div>

    <!-- ── PASSWORD ── -->
    <div class="page" id="page-password">
      <div class="page-header">
        <div>
          <h1>Change Password</h1>
          <p>Update your admin panel password.</p>
        </div>
      </div>
      <div class="alert" id="passwordAlert"></div>
      <div class="card" style="max-width: 400px;">
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
        <button class="btn btn-primary" id="changePassBtn">Update Password</button>
      </div>
    </div>

  </main>
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
      <span style="font-size:12px;color:#9d9db8;margin-top:4px;">Separate each role with a comma</span>
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
    const loaders = { settings: loadSettings, services: loadServices, productions: loadProductions, skills: loadSkills, inbox: loadInbox };
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

async function api(action, data = {}) {
  const fd = new FormData();
  fd.append('action', action);
  fd.append('csrf', CSRF);
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  const res = await fetch('api.php', { method: 'POST', body: fd });
  return res.json();
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
  if (res.success) showAlert('settingsAlert', '✅ Settings saved successfully!');
  else showAlert('settingsAlert', res.error || 'Error saving.', 'error');
});

// ── SERVICES ────────────────────────────────────────────────

async function loadServices() {
  const services = await apiGet('get_services');
  const list = document.getElementById('servicesList');
  if (!services.length) { list.innerHTML = '<p style="color:#9d9db8">No services yet. Click Add Service.</p>'; return; }
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
  if (res.success) { closeModal('serviceModal'); loadServices(); showAlert('servicesAlert', '✅ Service saved!'); }
  else showAlert('servicesAlert', res.error || 'Error saving.', 'error');
});

async function deleteService(id, title) {
  if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
  const res = await api('delete_service', { id });
  if (res.success) { loadServices(); showAlert('servicesAlert', '✅ Service deleted.'); }
  else showAlert('servicesAlert', res.error || 'Error deleting.', 'error');
}

// ── PRODUCTIONS ─────────────────────────────────────────────

async function loadProductions() {
  const prods = await apiGet('get_productions');
  const list  = document.getElementById('productionsList');
  if (!prods.length) { list.innerHTML = '<p style="color:#9d9db8">No productions yet. Click Add Production.</p>'; return; }
  list.innerHTML = prods.map(p => `
    <div class="item-card">
      <div style="font-size:22px;font-weight:800;color:rgba(155,92,246,0.4);min-width:52px;text-align:center">${p.year}</div>
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
  if (res.success) { closeModal('productionModal'); loadProductions(); showAlert('productionsAlert', '✅ Production saved!'); }
  else showAlert('productionsAlert', res.error || 'Error saving.', 'error');
});

async function deleteProduction(id, title) {
  if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
  const res = await api('delete_production', { id });
  if (res.success) { loadProductions(); showAlert('productionsAlert', '✅ Production deleted.'); }
  else showAlert('productionsAlert', res.error || 'Error deleting.', 'error');
}

// ── SKILLS ───────────────────────────────────────────────────

async function loadSkills() {
  const skills = await apiGet('get_skills');
  const list   = document.getElementById('skillsList');
  if (!skills.length) { list.innerHTML = '<p style="color:#9d9db8">No skills yet. Click Add Skill.</p>'; return; }
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
  if (res.success) { closeModal('skillModal'); loadSkills(); showAlert('skillsAlert', '✅ Skill saved!'); }
  else showAlert('skillsAlert', res.error || 'Error saving.', 'error');
});

async function deleteSkill(id, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  const res = await api('delete_skill', { id });
  if (res.success) { loadSkills(); showAlert('skillsAlert', '✅ Skill deleted.'); }
  else showAlert('skillsAlert', res.error || 'Error deleting.', 'error');
}

// ── INBOX ────────────────────────────────────────────────────

async function loadInbox() {
  const subs = await apiGet('get_submissions');
  const list = document.getElementById('inboxList');
  if (!subs.length) {
    list.innerHTML = '<div class="card" style="text-align:center;color:#9d9db8;padding:48px">No enquiries yet. When someone fills in your contact form, they\'ll appear here.</div>';
    return;
  }
  list.innerHTML = subs.map(s => `
    <div class="submission ${s.is_read == 0 ? 'unread' : ''}" id="sub-${s.id}">
      <div class="submission-meta">
        ${s.is_read == 0 ? '<span class="unread-dot"></span>' : ''}
        <strong>${escHtml(s.name)}</strong>
        <span>✉️ ${escHtml(s.email)}</span>
        ${s.phone ? `<span>📞 ${escHtml(s.phone)}</span>` : ''}
        ${s.project_type ? `<span>📋 ${escHtml(s.project_type)}</span>` : ''}
        <span style="margin-left:auto;font-size:12px;color:#6b6b80">${escHtml(s.submitted_at)}</span>
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
  if (res.success) { document.getElementById('sub-' + id)?.remove(); showAlert('inboxAlert', '✅ Enquiry deleted.'); }
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
  btn.disabled = false; btn.textContent = 'Update Password';

  if (res.success) {
    showAlert('passwordAlert', '✅ Password updated successfully!');
    document.getElementById('currentPass').value = '';
    document.getElementById('newPass').value      = '';
    document.getElementById('confirmPass').value  = '';
    // Remove the first-login warning banner
    document.querySelector('.first-login-banner')?.remove();
  } else {
    showAlert('passwordAlert', res.error || 'Error updating password.', 'error');
  }
});

// ── XSS helper ───────────────────────────────────────────────

function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Init — load first page ────────────────────────────────────

loadSettings();
</script>

<?php endif; ?>
</body>
</html>
