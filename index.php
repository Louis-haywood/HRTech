<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$db          = get_db();
$s           = settings(); // all settings as array
$services    = $db->query("SELECT * FROM services    ORDER BY sort_order")->fetchAll();
$productions = $db->query("SELECT * FROM productions ORDER BY sort_order")->fetchAll();
$skills      = $db->query("SELECT * FROM skills      ORDER BY sort_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= esc(setting('site_name')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: #0d0d12;
      color: #fff;
      line-height: 1.6;
    }

    a { color: inherit; text-decoration: none; }
    img { max-width: 100%; display: block; }

    /* ── NAV ── */
    nav {
      position: sticky;
      top: 0;
      background: rgba(13, 13, 18, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(155, 92, 246, 0.2);
      z-index: 100;
      padding: 0 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
    }

    .nav-logo { font-size: 18px; font-weight: 800; }
    .nav-logo span { color: #9b5cf6; }

    .nav-links { display: flex; gap: 32px; list-style: none; }
    .nav-links a { font-size: 15px; color: #9d9db8; transition: color 0.2s; }
    .nav-links a:hover, .nav-links a.active { color: #fff; }

    .nav-cta {
      background: #9b5cf6;
      color: #fff;
      padding: 10px 22px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .nav-cta:hover { background: #7c3aed; }

    /* ── HERO ── */
    .hero {
      padding: 100px 40px;
      max-width: 900px;
      margin: 0 auto;
      text-align: center;
    }

    .hero-badge {
      display: inline-block;
      background: rgba(155, 92, 246, 0.15);
      color: #c084fc;
      border: 1px solid rgba(155, 92, 246, 0.3);
      padding: 6px 16px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 28px;
    }

    .hero h1 {
      font-size: clamp(40px, 7vw, 80px);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.03em;
      margin-bottom: 24px;
    }

    .hero h1 span { color: #9b5cf6; }

    .hero p {
      font-size: 18px;
      color: #9d9db8;
      max-width: 600px;
      margin: 0 auto 40px;
    }

    .hero-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

    .btn-primary {
      background: #9b5cf6;
      color: #fff;
      padding: 14px 28px;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      transition: background 0.2s, transform 0.1s;
    }
    .btn-primary:hover { background: #7c3aed; transform: translateY(-1px); }

    .btn-outline {
      background: transparent;
      color: #fff;
      padding: 14px 28px;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      border: 1px solid rgba(155, 92, 246, 0.4);
      transition: border-color 0.2s, background 0.2s;
    }
    .btn-outline:hover { border-color: #9b5cf6; background: rgba(155, 92, 246, 0.08); }

    /* ── SECTIONS ── */
    section { padding: 80px 40px; }
    .section-inner { max-width: 1100px; margin: 0 auto; }
    .section-label {
      font-size: 13px; font-weight: 700; letter-spacing: 0.1em;
      text-transform: uppercase; color: #9b5cf6; margin-bottom: 12px;
    }
    .section-title {
      font-size: clamp(28px, 4vw, 44px);
      font-weight: 800; letter-spacing: -0.02em; margin-bottom: 48px;
    }

    /* ── SERVICES ── */
    #services { background: #0d0d12; }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .service-card {
      background: #1a1525;
      border: 1px solid rgba(155, 92, 246, 0.2);
      border-radius: 10px;
      padding: 36px;
      transition: transform 0.2s, border-color 0.2s;
    }
    .service-card:hover { transform: translateY(-4px); border-color: rgba(155, 92, 246, 0.5); }
    .service-card.featured {
      background: linear-gradient(135deg, #9b5cf6, #7c3aed);
      border-color: transparent;
    }
    .service-card.featured:hover { transform: translateY(-4px); }

    .service-icon { font-size: 28px; margin-bottom: 16px; }
    .service-card h3 { font-size: 20px; font-weight: 700; margin-bottom: 10px; }
    .service-card p { font-size: 14px; color: #9d9db8; line-height: 1.65; }
    .service-card.featured p { color: rgba(255,255,255,0.85); }

    /* ── PRODUCTIONS ── */
    #productions { background: #0a0a10; }

    .productions-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .production-card {
      background: #1a1525;
      border: 1px solid rgba(155, 92, 246, 0.15);
      border-radius: 10px;
      padding: 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      transition: border-color 0.2s;
    }
    .production-card:hover { border-color: rgba(155, 92, 246, 0.4); }
    .production-info h3 { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
    .production-info p { font-size: 13px; color: #9d9db8; margin-bottom: 10px; }

    .production-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .tag {
      background: rgba(155, 92, 246, 0.15);
      color: #c084fc;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 500;
    }

    .production-year { font-size: 24px; font-weight: 800; color: rgba(155,92,246,0.3); flex-shrink: 0; }

    /* ── ABOUT ── */
    #about { background: #0d0d12; }

    .about-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 64px;
      align-items: start;
    }

    .about-text h2 {
      font-size: clamp(26px, 3vw, 38px);
      font-weight: 800; letter-spacing: -0.02em; margin-bottom: 20px; line-height: 1.2;
    }
    .about-text p { color: #9d9db8; font-size: 16px; margin-bottom: 16px; }

    .skills-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 28px; }
    .skill-item { display: flex; align-items: center; gap: 10px; font-size: 14px; color: #9d9db8; }
    .skill-item::before {
      content: ''; width: 6px; height: 6px; border-radius: 50%;
      background: #9b5cf6; flex-shrink: 0;
    }

    .about-stats { display: flex; flex-direction: column; gap: 20px; }

    .stat-box {
      background: #1a1525;
      border: 1px solid rgba(155,92,246,0.2);
      border-radius: 10px;
      padding: 28px;
    }
    .stat-box .number { font-size: 48px; font-weight: 800; color: #9b5cf6; line-height: 1; margin-bottom: 6px; }
    .stat-box .label { font-size: 14px; color: #9d9db8; }

    .edu-box {
      background: #1a1525;
      border: 1px solid rgba(155,92,246,0.2);
      border-radius: 10px;
      padding: 28px;
    }
    .edu-box h4 {
      font-size: 13px; font-weight: 700; color: #9b5cf6;
      text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px;
    }
    .edu-item { margin-bottom: 14px; }
    .edu-item:last-child { margin-bottom: 0; }
    .edu-item strong { display: block; font-size: 15px; font-weight: 600; margin-bottom: 2px; }
    .edu-item span { font-size: 13px; color: #9d9db8; }

    /* ── CONTACT ── */
    #contact { background: #0a0a10; }

    .contact-grid { display: grid; grid-template-columns: 1fr 1.4fr; gap: 64px; align-items: start; }
    .contact-info h2 {
      font-size: clamp(26px, 3vw, 36px);
      font-weight: 800; letter-spacing: -0.02em; margin-bottom: 20px;
    }
    .contact-info > p { color: #9d9db8; font-size: 16px; margin-bottom: 32px; }

    .contact-detail { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; font-size: 15px; color: #9d9db8; }
    .contact-detail .icon {
      width: 36px; height: 36px; background: rgba(155,92,246,0.15);
      border-radius: 8px; display: flex; align-items: center; justify-content: center;
      font-size: 16px; flex-shrink: 0;
    }
    .contact-detail a { color: #9b5cf6; }
    .contact-detail a:hover { text-decoration: underline; }

    form { display: flex; flex-direction: column; gap: 16px; }

    label {
      display: block; font-size: 13px; font-weight: 600; color: #9d9db8;
      margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;
    }

    input, select, textarea {
      width: 100%;
      background: #1a1525;
      border: 1px solid rgba(155,92,246,0.25);
      border-radius: 8px;
      color: #fff;
      font-family: inherit;
      font-size: 15px;
      padding: 12px 16px;
      outline: none;
      transition: border-color 0.2s;
      -webkit-appearance: none;
      appearance: none;
    }
    input:focus, select:focus, textarea:focus { border-color: #9b5cf6; }
    input::placeholder, textarea::placeholder { color: #4a4a6a; }
    select { cursor: pointer; }
    select option { background: #1a1525; }
    textarea { resize: vertical; min-height: 120px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .submit-btn {
      background: #9b5cf6;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-family: inherit;
      font-size: 16px;
      font-weight: 600;
      padding: 14px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .submit-btn:hover { background: #7c3aed; }
    .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    .gdpr-note { font-size: 12px; color: #4a4a6a; text-align: center; }

    .form-error {
      display: none;
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.4);
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 14px;
      color: #fca5a5;
    }

    .success-msg {
      display: none;
      background: rgba(155,92,246,0.1);
      border: 1px solid #9b5cf6;
      border-radius: 10px;
      padding: 32px;
      text-align: center;
    }
    .success-msg h3 { font-size: 20px; margin-bottom: 8px; }
    .success-msg p { color: #9d9db8; font-size: 15px; }

    /* ── DEMO MARKINGS ── */
    .demo-banner {
      background: #f59e0b;
      color: #000;
      text-align: center;
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.03em;
      position: sticky;
      top: 0;
      z-index: 200;
    }
    .demo-banner a { color: #000; text-decoration: underline; }

    /* Push nav below the banner */
    nav { top: 40px; }

    .demo-corner {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: #f59e0b;
      color: #000;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      padding: 8px 14px;
      border-radius: 6px;
      z-index: 999;
      box-shadow: 0 4px 16px rgba(0,0,0,0.4);
    }

    .demo-section-tag {
      display: inline-block;
      background: #f59e0b;
      color: #000;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 4px;
      margin-left: 12px;
      vertical-align: middle;
      position: relative;
      top: -2px;
    }

    .demo-watermark {
      pointer-events: none;
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: clamp(60px, 10vw, 120px);
      font-weight: 900;
      color: rgba(245, 158, 11, 0.06);
      letter-spacing: 0.15em;
      text-transform: uppercase;
      user-select: none;
      z-index: 0;
    }

    section { position: relative; }
    section > * { position: relative; z-index: 1; }

    /* ── FOOTER ── */
    footer {
      background: #1a1525;
      border-top: 1px solid rgba(155,92,246,0.2);
      padding: 32px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      flex-wrap: wrap;
    }
    footer .logo { font-size: 16px; font-weight: 700; }
    footer .logo span { color: #9b5cf6; }
    footer .footer-links { display: flex; gap: 24px; list-style: none; }
    footer .footer-links a { font-size: 14px; color: #9d9db8; transition: color 0.2s; }
    footer .footer-links a:hover { color: #9b5cf6; }
    footer .copy { font-size: 13px; color: #4a4a6a; }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      nav { padding: 0 20px; }
      .nav-links { display: none; }
      section { padding: 60px 20px; }
      .hero { padding: 70px 20px; }
      .productions-grid { grid-template-columns: 1fr; }
      .about-grid { grid-template-columns: 1fr; }
      .contact-grid { grid-template-columns: 1fr; }
      .form-row { grid-template-columns: 1fr; }
      .skills-grid { grid-template-columns: 1fr; }
      footer { flex-direction: column; text-align: center; padding: 24px 20px; }
      footer .footer-links { justify-content: center; }
    }
  </style>
</head>
<body>

  <!-- DEMO BANNER -->
  <div class="demo-banner">
    ⚠️ DEMO PREVIEW — This website is a demonstration for Harry Richardson / HR Lighting Services. Content is not final and the site is not yet live.
  </div>

  <!-- DEMO CORNER BADGE -->
  <div class="demo-corner">DEMO</div>

  <!-- NAV -->
  <nav id="navbar">
    <div class="nav-logo">HR <span>Lighting</span></div>
    <ul class="nav-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#productions">Productions</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#contact" class="nav-cta">Get in Touch</a></li>
    </ul>
  </nav>

  <!-- HERO -->
  <div class="hero" style="position:relative;">
    <div class="demo-watermark">DEMO</div>
    <div class="hero-badge"><?= esc(setting('hero_badge')) ?></div>
    <h1><?= nl2br(esc(setting('hero_heading'))) ?></h1>
    <p><?= esc(setting('hero_subtext')) ?></p>
    <div class="hero-buttons">
      <a href="#contact" class="btn-primary">Get a Quote</a>
      <a href="#productions" class="btn-outline">View Productions</a>
    </div>
  </div>

  <!-- SERVICES -->
  <section id="services">
    <div class="section-inner">
      <div class="demo-watermark">DEMO</div>
      <p class="section-label">What I Do</p>
      <h2 class="section-title">Services <span class="demo-section-tag">Demo</span></h2>
      <div class="services-grid">
        <?php foreach ($services as $svc): ?>
          <div class="service-card <?= $svc['featured'] ? 'featured' : '' ?>">
            <div class="service-icon"><?= esc($svc['icon']) ?></div>
            <h3><?= esc($svc['title']) ?></h3>
            <p><?= esc($svc['body']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- PRODUCTIONS -->
  <section id="productions">
    <div class="section-inner">
      <div class="demo-watermark">DEMO</div>
      <p class="section-label">Portfolio</p>
      <h2 class="section-title">Productions <span class="demo-section-tag">Demo</span></h2>
      <div class="productions-grid">
        <?php foreach ($productions as $prod):
          $tags = json_decode($prod['tags'], true) ?: [];
        ?>
          <div class="production-card">
            <div class="production-info">
              <h3><?= esc($prod['title']) ?></h3>
              <p><?= esc($prod['venue']) ?></p>
              <div class="production-tags">
                <?php foreach ($tags as $tag): ?>
                  <span class="tag"><?= esc($tag) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="production-year"><?= (int)$prod['year'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about">
    <div class="section-inner">
      <div class="about-grid">

        <div class="about-text">
          <div class="demo-watermark">DEMO</div>
          <p class="section-label">About</p>
          <h2><?= esc(setting('about_heading')) ?> <span class="demo-section-tag">Demo</span></h2>
          <p><?= esc(setting('about_text_1')) ?></p>
          <p><?= esc(setting('about_text_2')) ?></p>

          <?php if ($skills): ?>
          <div class="skills-grid">
            <?php foreach ($skills as $skill): ?>
              <div class="skill-item"><?= esc($skill['name']) ?></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="about-stats">
          <div class="stat-box">
            <div class="number"><?= esc(setting('stat_1_number')) ?></div>
            <div class="label"><?= esc(setting('stat_1_label')) ?></div>
          </div>
          <div class="stat-box">
            <div class="number"><?= esc(setting('stat_2_number')) ?></div>
            <div class="label"><?= esc(setting('stat_2_label')) ?></div>
          </div>
          <div class="edu-box">
            <h4>Education &amp; Training</h4>
            <div class="edu-item">
              <strong>BTEC Level 3 Extended Diploma</strong>
              <span>Production Arts (Technical) — BOA Stage &amp; Screen Academy, Birmingham (2025–2027)</span>
            </div>
            <div class="edu-item">
              <strong>Stratford-upon-Avon High School</strong>
              <span>GCSEs including Drama (Grade 6), Food Technology (Distinction 2) — 2020–2025</span>
            </div>
            <div class="edu-item">
              <strong>Basic First Aid</strong>
              <span>Trained via Army Cadets</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact">
    <div class="section-inner">
      <div class="contact-grid">

        <div class="contact-info">
          <div class="demo-watermark">DEMO</div>
          <p class="section-label">Contact</p>
          <h2>Let's work together. <span class="demo-section-tag">Demo</span></h2>
          <p>Got a production coming up? Get in touch and I'll get back to you quickly.</p>
          <div class="contact-detail">
            <div class="icon">📍</div>
            <span><?= esc(setting('contact_address')) ?></span>
          </div>
          <div class="contact-detail">
            <div class="icon">📞</div>
            <a href="tel:<?= esc(preg_replace('/\s+/', '', setting('contact_phone'))) ?>">
              <?= esc(setting('contact_phone')) ?>
            </a>
          </div>
          <div class="contact-detail">
            <div class="icon">✉️</div>
            <a href="mailto:<?= esc(setting('contact_email')) ?>">
              <?= esc(setting('contact_email')) ?>
            </a>
          </div>
        </div>

        <div>
          <form id="contactForm" novalidate>
            <div class="form-error" id="formError"></div>
            <div class="form-row">
              <div>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Your name" required />
              </div>
              <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" required />
              </div>
            </div>
            <div>
              <label for="phone">Phone (optional)</label>
              <input type="tel" id="phone" name="phone" placeholder="07700 900000" />
            </div>
            <div>
              <label for="project_type">Project Type</label>
              <select id="project_type" name="project_type">
                <option value="" disabled selected>Select a type…</option>
                <option>Theatre Production</option>
                <option>Live Event</option>
                <option>Commercial</option>
                <option>Other</option>
              </select>
            </div>
            <div>
              <label for="message">Message</label>
              <textarea id="message" name="message" placeholder="Tell me about your production, dates, venue…" required></textarea>
            </div>
            <button type="submit" class="submit-btn" id="submitBtn">Send Message →</button>
            <p class="gdpr-note">Your details won't be shared with anyone.</p>
          </form>

          <div class="success-msg" id="successMsg">
            <h3>✅ Message sent!</h3>
            <p>Thanks for getting in touch. I'll reply within 24 hours.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="logo">HR <span>Lighting</span></div>
    <ul class="footer-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#productions">Productions</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <p class="copy">&copy; <?= date('Y') ?> <?= esc(setting('site_name')) ?> &nbsp;·&nbsp; <span style="color:#f59e0b;font-weight:700;">DEMO PREVIEW</span></p>
  </footer>

  <script>
    // Active nav highlight on scroll
    const sections = document.querySelectorAll('section[id]');
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(s => { if (window.scrollY >= s.offsetTop - 80) current = s.id; });
      document.querySelectorAll('nav a[href^="#"]').forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current);
      });
    });

    // Contact form — POST to api.php
    document.getElementById('contactForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn   = document.getElementById('submitBtn');
      const err   = document.getElementById('formError');
      const succ  = document.getElementById('successMsg');

      btn.disabled   = true;
      btn.textContent = 'Sending…';
      err.style.display = 'none';

      const body = new URLSearchParams(new FormData(this));
      try {
        const res  = await fetch('api.php', { method: 'POST', body });
        const json = await res.json();
        if (json.success) {
          this.style.display = 'none';
          succ.style.display = 'block';
        } else {
          err.textContent  = json.error || 'Something went wrong. Please try again.';
          err.style.display = 'block';
          btn.disabled      = false;
          btn.textContent   = 'Send Message →';
        }
      } catch {
        err.textContent  = 'Could not send message. Please email directly.';
        err.style.display = 'block';
        btn.disabled      = false;
        btn.textContent   = 'Send Message →';
      }
    });
  </script>

</body>
</html>
