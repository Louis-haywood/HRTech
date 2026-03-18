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
  <link rel="stylesheet" href="public.css" />
</head>
<body>

  <nav id="navbar">
    <span class="nav-brand">HR <em>Lighting</em></span>
    <ul class="nav-links" id="navLinks">
      <li><a class="nav-item active" data-page="services">Services</a></li>
      <li><a class="nav-item" data-page="productions">Productions</a></li>
      <?php if ($portfolio): ?><li><a class="nav-item" data-page="portfolio">Portfolio</a></li><?php endif; ?>
      <li><a class="nav-item" data-page="about">About</a></li>
      <li><a class="nav-item" data-page="contact">Contact</a></li>
    </ul>
    <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
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
          <a class="hero-cta" data-page="contact">Get in touch &rarr;</a>
        </aside>
      </div>
    </div>
  </div>

  <!-- SERVICES -->
  <div class="page active" id="page-services">
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
  <div class="page" id="page-productions">
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
  <div class="page" id="page-portfolio">
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
  <div class="page" id="page-about">
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
  <div class="page" id="page-contact">
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
                  <label for="fname">Name</label>
                  <input type="text" id="fname" name="name" placeholder="Your name" required />
                </div>
                <div class="field">
                  <label for="femail">Email</label>
                  <input type="email" id="femail" name="email" placeholder="your@email.com" required />
                </div>
              </div>
              <div class="field">
                <label for="fphone">Phone <span style="font-weight:400;letter-spacing:0">(optional)</span></label>
                <input type="tel" id="fphone" name="phone" placeholder="07700 900000" />
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
      <li><a data-page="services">Services</a></li>
      <li><a data-page="productions">Productions</a></li>
      <?php if ($portfolio): ?><li><a data-page="portfolio">Portfolio</a></li><?php endif; ?>
      <li><a data-page="about">About</a></li>
      <li><a data-page="contact">Contact</a></li>
    </ul>
    <p class="footer-copy">&copy; <?= date('Y') ?> <?= esc(setting('site_name')) ?></p>
  </footer>

  <!-- CREDITS -->
  <div class="credits">
    <a href="https://louishaywood.uk" target="_blank" rel="noopener" class="credits-inner">
      <img src="https://louishaywood.uk/pfp.jpg" alt="Louis Haywood" class="credits-logo" />
      <div class="credits-text">
        <span class="credits-label">Designed &amp; Built by</span>
        <span class="credits-name">Louis Haywood</span>
        <span class="credits-url">louishaywood.uk</span>
      </div>
    </a>
  </div>

  <script>
    // ── Page switching ────────────────────────────────────────────
    function showPage(name) {
      document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
      const page = document.getElementById('page-' + name);
      if (page) page.classList.add('active');
      document.querySelectorAll('[data-page="' + name + '"]').forEach(a => a.classList.add('active'));
      window.scrollTo(0, 0);
    }

    document.querySelectorAll('[data-page]').forEach(el => {
      el.style.cursor = 'pointer';
      el.addEventListener('click', e => {
        e.preventDefault();
        showPage(el.dataset.page);
        _navLinks.classList.remove('open');
        _hamburger.classList.remove('open');
        _hamburger.setAttribute('aria-label', 'Open menu');
      });
    });

    // ── Hamburger menu ───────────────────────────────────────────
    const _hamburger = document.getElementById('navHamburger');
    const _navLinks  = document.getElementById('navLinks');

    _hamburger.addEventListener('click', () => {
      const open = _navLinks.classList.toggle('open');
      _hamburger.classList.toggle('open', open);
      _hamburger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });

    document.addEventListener('click', e => {
      if (!_navLinks.contains(e.target) && e.target !== _hamburger && !_hamburger.contains(e.target)) {
        _navLinks.classList.remove('open');
        _hamburger.classList.remove('open');
      }
    });

    // ── Reload when admin saves changes ──────────────────────────
    new BroadcastChannel('hrls_site').onmessage = () => location.reload();

    // ── Contact form ─────────────────────────────────────────────
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
