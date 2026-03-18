<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$db          = get_db();
$s           = settings();
$services    = $db->query("SELECT * FROM services    ORDER BY sort_order")->fetchAll();
$productions = $db->query("SELECT * FROM productions ORDER BY sort_order")->fetchAll();
$skills      = $db->query("SELECT * FROM skills      ORDER BY sort_order")->fetchAll();
$portfolio   = $db->query("SELECT * FROM portfolio   ORDER BY sort_order, id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= esc(setting('site_name')) ?></title>
  <link rel="stylesheet" href="style.css" />
  <style>
    textarea { min-height: 130px; }

    /* ── HERO ────────────────────────────────────────────────────── */
    .hero-outer {
      background: linear-gradient(135deg, #0D0D12 60%, #1A0A2E 100%);
      border-bottom: 1px solid var(--c-border);
    }
    .hero {
      padding-block: var(--section-v);
      display: grid;
      grid-template-columns: 1fr 280px;
      gap: 3rem;
      align-items: end;
    }

    .hero-eyebrow { margin-bottom: 1.5rem; }

    .hero-title {
      font-family: var(--f-display);
      font-style: italic;
      font-weight: 900;
      font-size: var(--s-hero);
      line-height: 1.0;
      letter-spacing: -0.02em;
    }

    .hero-aside {
      padding-left: 2rem;
      border-left: 1px solid var(--c-border);
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      padding-bottom: 0.25rem;
    }

    .hero-desc {
      font-size: var(--s-13);
      color: var(--c-muted);
      line-height: 1.8;
    }

    .hero-cta {
      font-family: var(--f-mono);
      font-size: var(--s-13);
      font-weight: 700;
      color: var(--c-accent);
      letter-spacing: 0.04em;
      display: inline-block;
      transition: letter-spacing 0.2s;
    }
    .hero-cta:hover { letter-spacing: 0.1em; }

    /* ── SERVICES ────────────────────────────────────────────────── */
    .service-row {
      display: grid;
      grid-template-columns: 2.5rem 1fr;
      gap: 1.5rem;
      padding-block: 2rem;
      border-bottom: 1px solid var(--c-border);
      align-items: start;
    }
    .service-row:last-child { border-bottom: none; }

    .service-idx {
      font-family: var(--f-mono);
      font-size: var(--s-11);
      color: var(--c-muted);
      letter-spacing: 0.06em;
      padding-top: 0.55rem;
    }

    .service-name {
      font-family: var(--f-display);
      font-size: var(--s-36);
      font-weight: 700;
      line-height: 1.05;
      margin-bottom: 0.75rem;
      letter-spacing: -0.015em;
    }
    .service-row.featured .service-name {
      background: linear-gradient(135deg, #9B5CF6, #7C3AED);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .service-body {
      font-size: var(--s-13);
      color: var(--c-muted);
      line-height: 1.8;
      max-width: 58ch;
    }

    /* ── PRODUCTIONS ─────────────────────────────────────────────── */
    .production-row {
      display: grid;
      grid-template-columns: 1fr 5rem;
      gap: 2rem;
      padding-block: 1.5rem;
      border-bottom: 1px solid var(--c-border);
      align-items: center;
    }
    .production-row:last-child { border-bottom: none; }

    .production-title {
      font-family: var(--f-display);
      font-size: var(--s-20);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 0.3rem;
    }

    .production-venue {
      font-size: var(--s-13);
      color: var(--c-muted);
      margin-bottom: 0.6rem;
    }

    .production-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.35rem;
    }

    .production-year {
      font-family: var(--f-display);
      font-size: var(--s-36);
      font-weight: 700;
      color: var(--c-border);
      line-height: 1;
      text-align: right;
    }

    /* ── PORTFOLIO ───────────────────────────────────────────────── */
    .portfolio-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1px;
      background: var(--c-border);
    }

    .portfolio-item {
      position: relative;
      aspect-ratio: 1;
      overflow: hidden;
      background: var(--c-surface);
    }

    .portfolio-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.4s ease;
    }

    .portfolio-item:hover img { transform: scale(1.05); }

    .portfolio-caption {
      position: absolute;
      inset: 0;
      background: rgba(13,13,18,0.75);
      display: flex;
      align-items: flex-end;
      padding: 0.875rem;
      opacity: 0;
      transition: opacity 0.25s;
    }

    .portfolio-item:hover .portfolio-caption { opacity: 1; }

    .portfolio-caption span {
      font-size: var(--s-11);
      font-weight: 700;
      letter-spacing: 0.07em;
      text-transform: uppercase;
      color: var(--c-fg);
      line-height: 1.3;
    }

    @media (max-width: 860px) {
      .portfolio-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
      .portfolio-grid { grid-template-columns: 1fr; }
    }

    /* ── ABOUT ───────────────────────────────────────────────────── */
    .about-grid {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 4rem;
      align-items: start;
    }

    .about-name {
      font-family: var(--f-display);
      font-size: var(--s-28);
      font-weight: 700;
      letter-spacing: -0.01em;
      margin-bottom: 1.5rem;
      line-height: 1.1;
    }

    .about-body {
      font-size: var(--s-15);
      color: var(--c-muted);
      line-height: 1.85;
      margin-bottom: 1rem;
    }

    .skills-wrap {
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--c-border);
    }
    .skills-label { margin-bottom: 0.75rem; }
    .skills-flow {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }
    .skill-chip {
      font-family: var(--f-mono);
      font-size: var(--s-11);
      letter-spacing: 0.07em;
      text-transform: uppercase;
      color: var(--c-muted);
      border: 1px solid var(--c-border);
      padding: 3px 10px;
    }

    .about-aside { display: flex; flex-direction: column; gap: 2rem; }

    .stat-block {
      border-top: 2px solid var(--c-accent);
      padding-top: 1rem;
    }
    .stat-number {
      font-family: var(--f-display);
      font-size: 3.75rem;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 0.4rem;
    }
    .stat-label {
      font-size: var(--s-13);
      color: var(--c-muted);
      line-height: 1.4;
    }

    .edu-block {
      border-top: 1px solid var(--c-border);
      padding-top: 1.5rem;
    }
    .edu-block-label { margin-bottom: 1.25rem; }
    .edu-item { margin-bottom: 1.25rem; }
    .edu-item:last-child { margin-bottom: 0; }
    .edu-item-title {
      font-size: var(--s-15);
      font-weight: 700;
      line-height: 1.3;
      margin-bottom: 0.2rem;
    }
    .edu-item-detail {
      font-size: var(--s-13);
      color: var(--c-muted);
      line-height: 1.55;
    }

    /* ── CONTACT ─────────────────────────────────────────────────── */
    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1.5fr;
      gap: 4rem;
      align-items: start;
    }

    .contact-heading {
      font-family: var(--f-display);
      font-style: italic;
      font-size: var(--s-36);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
    }

    .contact-desc {
      font-size: var(--s-13);
      color: var(--c-muted);
      line-height: 1.75;
      margin-bottom: 2rem;
    }

    .contact-detail {
      display: flex;
      gap: 1rem;
      align-items: baseline;
      margin-bottom: 0.75rem;
      font-size: var(--s-13);
    }
    .contact-detail-key {
      font-size: var(--s-11);
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--c-muted);
      min-width: 4.5rem;
      flex-shrink: 0;
    }
    .contact-detail a { color: var(--c-accent); }
    .contact-detail a:hover { text-decoration: underline; }

    /* form */
    form { display: flex; flex-direction: column; gap: 1.25rem; }
    .field { display: flex; flex-direction: column; gap: 0.4rem; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    .submit-btn {
      font-family: var(--f-mono);
      font-size: var(--s-13);
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      background: linear-gradient(135deg, #9B5CF6, #7C3AED);
      color: #fff;
      border: none;
      padding: 0.875rem 1.75rem;
      cursor: pointer;
      align-self: flex-start;
      transition: background 0.15s, box-shadow 0.15s;
    }
    .submit-btn:hover    { background: linear-gradient(135deg, #9B5CF6, #C084FC); box-shadow: 0 0 24px rgba(155,92,246,0.4); }
    .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .gdpr-note {
      font-size: var(--s-11);
      color: var(--c-muted);
      letter-spacing: 0.04em;
    }

    .form-error {
      display: none;
      border: 1px solid #8b2e2e;
      padding: 0.75rem 1rem;
      font-size: var(--s-13);
      color: #e07070;
    }

    .success-msg {
      display: none;
      border: 1px solid var(--c-border);
      padding: 2rem;
    }
    .success-msg-title {
      font-family: var(--f-display);
      font-size: var(--s-28);
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .success-msg-body {
      font-size: var(--s-13);
      color: var(--c-muted);
    }

    /* ── FOOTER ──────────────────────────────────────────────────── */
    footer {
      padding: 1.5rem var(--gutter);
      border-top: 1px solid var(--c-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .footer-brand {
      font-family: var(--f-mono);
      font-size: var(--s-11);
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }
    .footer-brand em { font-style: normal; color: var(--c-accent); }

    .footer-links {
      display: flex;
      gap: 1.5rem;
    }
    .footer-links a {
      font-family: var(--f-mono);
      font-size: var(--s-11);
      letter-spacing: 0.07em;
      text-transform: uppercase;
      color: var(--c-muted);
      transition: color 0.15s;
    }
    .footer-links a:hover { color: var(--c-fg); }

    .footer-copy {
      font-family: var(--f-mono);
      font-size: var(--s-11);
      color: var(--c-muted);
    }

    /* ── RESPONSIVE ──────────────────────────────────────────────── */
    @media (max-width: 860px) {
      .hero {
        grid-template-columns: 1fr;
        gap: 2rem;
      }
      .hero-aside {
        border-left: none;
        border-top: 1px solid var(--c-border);
        padding-left: 0;
        padding-top: 1.5rem;
      }
      .about-grid    { grid-template-columns: 1fr; }
      .contact-grid  { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
      .nav-links    { display: none; }
      .form-row     { grid-template-columns: 1fr; }
      .production-year { font-size: var(--s-28); }
      footer { flex-direction: column; align-items: flex-start; }
      .footer-links { display: none; }
    }
  </style>
</head>
<body>

  <div class="demo-banner">Preview — content subject to change</div>

  <nav id="navbar">
    <span class="nav-brand">HR <em>Lighting</em></span>
    <ul class="nav-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#productions">Productions</a></li>
      <?php if ($portfolio): ?><li><a href="#portfolio">Portfolio</a></li><?php endif; ?>
      <li><a href="#about">About</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
  </nav>

  <!-- HERO -->
  <div class="hero-outer">
    <div class="wrap">
      <div class="hero">
        <div class="hero-main">
          <p class="hero-eyebrow eyebrow"><?= esc(setting('hero_badge')) ?> &nbsp;·&nbsp; Lighting Designer</p>
          <h1 class="hero-title"><?= nl2br(esc(setting('hero_heading'))) ?></h1>
        </div>
        <aside class="hero-aside">
          <p class="hero-desc"><?= esc(setting('hero_subtext')) ?></p>
          <a href="#contact" class="hero-cta">Get in touch &rarr;</a>
        </aside>
      </div>
    </div>
  </div>

  <!-- SERVICES -->
  <div class="section-outer" id="services">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">01</span>
          <h2 class="section-title">Services</h2>
        </div>
        <?php foreach ($services as $i => $svc): ?>
          <div class="service-row <?= $svc['featured'] ? 'featured' : '' ?>">
            <span class="service-idx eyebrow"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
            <div>
              <h3 class="service-name"><?= esc($svc['title']) ?></h3>
              <p class="service-body"><?= esc($svc['body']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- PRODUCTIONS -->
  <div class="section-outer" id="productions">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">02</span>
          <h2 class="section-title">Productions</h2>
        </div>
        <?php foreach ($productions as $prod):
          $tags = json_decode($prod['tags'], true) ?: [];
        ?>
          <div class="production-row">
            <div class="production-main">
              <h3 class="production-title"><?= esc($prod['title']) ?></h3>
              <p class="production-venue"><?= esc($prod['venue']) ?></p>
              <?php if ($tags): ?>
                <div class="production-tags">
                  <?php foreach ($tags as $tag): ?>
                    <span class="tag"><?= esc($tag) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="production-year"><?= (int)$prod['year'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php if ($portfolio): ?>
  <!-- PORTFOLIO -->
  <div class="section-outer" id="portfolio">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow">03</span>
          <h2 class="section-title">Portfolio</h2>
        </div>
        <div class="portfolio-grid">
          <?php foreach ($portfolio as $photo): ?>
            <div class="portfolio-item">
              <img src="../uploads/<?= esc($photo['filename']) ?>" alt="<?= esc($photo['caption']) ?>" loading="lazy" />
              <?php if ($photo['caption']): ?>
                <div class="portfolio-caption"><span><?= esc($photo['caption']) ?></span></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ABOUT -->
  <div class="section-outer" id="about">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow"><?= $portfolio ? '04' : '03' ?></span>
          <h2 class="section-title">About</h2>
        </div>
        <div class="about-grid">

          <div class="about-text">
            <h3 class="about-name"><?= esc(setting('about_heading')) ?></h3>
            <p class="about-body"><?= esc(setting('about_text_1')) ?></p>
            <p class="about-body"><?= esc(setting('about_text_2')) ?></p>

            <?php if ($skills): ?>
              <div class="skills-wrap">
                <p class="eyebrow skills-label">Equipment &amp; Skills</p>
                <div class="skills-flow">
                  <?php foreach ($skills as $sk): ?>
                    <span class="skill-chip"><?= esc($sk['name']) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="about-aside">
            <div class="stat-block">
              <div class="stat-number"><?= esc(setting('stat_1_number')) ?></div>
              <div class="stat-label"><?= esc(setting('stat_1_label')) ?></div>
            </div>
            <div class="stat-block">
              <div class="stat-number"><?= esc(setting('stat_2_number')) ?></div>
              <div class="stat-label"><?= esc(setting('stat_2_label')) ?></div>
            </div>
            <div class="edu-block">
              <p class="eyebrow edu-block-label">Education &amp; Training</p>
              <div class="edu-item">
                <p class="edu-item-title">BTEC Level 3 Extended Diploma</p>
                <p class="edu-item-detail">Production Arts (Technical) — BOA Stage &amp; Screen Academy, Birmingham (2025–2027)</p>
              </div>
              <div class="edu-item">
                <p class="edu-item-title">Stratford-upon-Avon High School</p>
                <p class="edu-item-detail">GCSEs including Drama (Grade 6), Food Technology (Distinction 2) — 2020–2025</p>
              </div>
              <div class="edu-item">
                <p class="edu-item-title">Basic First Aid</p>
                <p class="edu-item-detail">Trained via Army Cadets</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- CONTACT -->
  <div id="contact">
    <div class="wrap">
      <div class="section-inner">
        <div class="section-head">
          <span class="section-num eyebrow"><?= $portfolio ? '05' : '04' ?></span>
          <h2 class="section-title">Contact</h2>
        </div>
        <div class="contact-grid">

          <div class="contact-info">
            <h3 class="contact-heading">Let's work<br>together.</h3>
            <p class="contact-desc">Got a production coming up? Get in touch and I'll get back to you quickly.</p>
            <div class="contact-detail">
              <span class="contact-detail-key">Location</span>
              <span><?= esc(setting('contact_address')) ?></span>
            </div>
            <div class="contact-detail">
              <span class="contact-detail-key">Phone</span>
              <a href="tel:<?= esc(preg_replace('/\s+/', '', setting('contact_phone'))) ?>">
                <?= esc(setting('contact_phone')) ?>
              </a>
            </div>
            <div class="contact-detail">
              <span class="contact-detail-key">Email</span>
              <a href="mailto:<?= esc(setting('contact_email')) ?>">
                <?= esc(setting('contact_email')) ?>
              </a>
            </div>
          </div>

          <div>
            <form id="contactForm" novalidate>
              <div class="form-error" id="formError"></div>
              <div class="form-row">
                <div class="field">
                  <label for="name">Name</label>
                  <input type="text" id="name" name="name" placeholder="Your name" required />
                </div>
                <div class="field">
                  <label for="email">Email</label>
                  <input type="email" id="email" name="email" placeholder="your@email.com" required />
                </div>
              </div>
              <div class="field">
                <label for="phone">Phone <span style="font-weight:400;letter-spacing:0">(optional)</span></label>
                <input type="tel" id="phone" name="phone" placeholder="07700 900000" />
              </div>
              <div class="field">
                <label for="project_type">Project Type</label>
                <select id="project_type" name="project_type">
                  <option value="" disabled selected>Select a type…</option>
                  <option>Theatre Production</option>
                  <option>Live Event</option>
                  <option>Commercial</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="field">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Tell me about your production, dates, venue…" required></textarea>
              </div>
              <button type="submit" class="submit-btn" id="submitBtn">Send Message &rarr;</button>
              <p class="gdpr-note">Your details won't be shared with anyone.</p>
            </form>

            <div class="success-msg" id="successMsg">
              <p class="success-msg-title">Message sent.</p>
              <p class="success-msg-body">Thanks for getting in touch. I'll reply within 24 hours.</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer>
    <span class="footer-brand">HR <em>Lighting</em></span>
    <ul class="footer-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#productions">Productions</a></li>
      <?php if ($portfolio): ?><li><a href="#portfolio">Portfolio</a></li><?php endif; ?>
      <li><a href="#about">About</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <p class="footer-copy">&copy; <?= date('Y') ?> <?= esc(setting('site_name')) ?></p>
  </footer>

  <script>
    // Active nav on scroll
    const navLinks = document.querySelectorAll('nav a[href^="#"]');
    const sections = document.querySelectorAll('[id]');
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 80) current = s.id;
      });
      navLinks.forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current);
      });
    });

    // Reload when admin saves changes (same browser, BroadcastChannel)
    new BroadcastChannel('hrls_site').onmessage = () => location.reload();

    // Contact form
    document.getElementById('contactForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn  = document.getElementById('submitBtn');
      const err  = document.getElementById('formError');
      const succ = document.getElementById('successMsg');

      btn.disabled    = true;
      btn.textContent = 'Sending\u2026';
      err.style.display = 'none';

      const body = new URLSearchParams(new FormData(this));
      try {
        const res  = await fetch('api.php', { method: 'POST', body });
        const json = await res.json();
        if (json.success) {
          this.style.display = 'none';
          succ.style.display = 'block';
        } else {
          err.textContent   = json.error || 'Something went wrong. Please try again.';
          err.style.display = 'block';
          btn.disabled      = false;
          btn.textContent   = 'Send Message \u2192';
        }
      } catch {
        err.textContent   = 'Could not send. Please email directly.';
        err.style.display = 'block';
        btn.disabled      = false;
        btn.textContent   = 'Send Message \u2192';
      }
    });
  </script>

</body>
</html>
