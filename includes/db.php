<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u463907152_HRTech');
define('DB_USER', 'u463907152_HRTech');
define('DB_PASS', 'Harry2026!');

function get_db(): PDO {
    static $db = null;
    if ($db === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $db = new PDO($dsn, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            init_db($db);
        } catch (PDOException $e) {
            die('<pre style="color:red;padding:2rem;">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
    }
    return $db;
}

function init_db(PDO $db): void {
    // Seed on first run
    $count = $db->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ((int)$count === 0) {
        seed_defaults($db);
    }
}

function seed_defaults(PDO $db): void {
    // ── Settings ────────────────────────────────────────────
    $settings = [
        'site_name'       => 'HR Lighting Services',
        'hero_badge'      => 'Based in Stratford-upon-Avon',
        'hero_heading'    => 'Lighting Design & Technical Production',
        'hero_subtext'    => 'Professional lighting design, live operation, and technical production services for theatre, live events, and commercial clients across the UK.',
        'about_heading'   => 'Harry Richardson',
        'about_text_1'    => 'A hardworking, motivated, and technically skilled lighting professional with a strong passion for theatre and live events. Currently studying a BTEC Level 3 Extended Diploma in Production Arts (Technical) at BOA Stage & Screen Production Academy.',
        'about_text_2'    => 'With hands-on experience across a variety of shows and technical environments, I\'ve built the confidence to perform effectively under pressure — both independently and as part of a team.',
        'stat_1_number'   => '8+',
        'stat_1_label'    => 'Productions as Lighting Designer or Operator',
        'stat_2_number'   => '3',
        'stat_2_label'    => 'Professional Lighting Consoles Operated',
        'contact_address' => 'Lower Quinton, Stratford-upon-Avon',
        'contact_phone'   => '07305 598 139',
        'contact_email'   => 'HarrysLighting@outlook.com',
        // Temporary password — admin is prompted to change this on first login
        'admin_password'  => password_hash('hrls2026', PASSWORD_DEFAULT),
        'first_login'     => '1',
    ];

    $stmt = $db->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?)");
    foreach ($settings as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    // ── Services ─────────────────────────────────────────────
    $services = [
        [1, '💡', 'Lighting Design',     'Bespoke lighting schemes designed from concept to cue sheet. Proficient with EOS ETC, CHAMSYS QUICKQ, and Vari-lite FLX software.',         1],
        [2, '🎛️', 'Live Operation',      'Experienced console operator for theatre productions and live events. Accurate, reliable cueing under pressure with full show control.',     0],
        [3, '🔧', 'Rigging & Technical', 'Stage rigging, PA system set-up, production electrician services, and follow spot operation. Safe, efficient, and methodical.',            0],
    ];
    $stmt = $db->prepare("INSERT INTO services (sort_order, icon, title, body, featured) VALUES (?, ?, ?, ?, ?)");
    foreach ($services as $s) {
        $stmt->execute($s);
    }

    // ── Productions ──────────────────────────────────────────
    $productions = [
        [1, 'Mary Poppins',          'Stratford-upon-Avon High School', 2025, '["LD","Operator","Technical DSM","Technical Manager","Production Electrician"]'],
        [2, 'The Borrowers',         'Stratford Youth Theatre',          2026, '["ALD","Lighting Technician","LX Ops"]'],
        [3, 'High School Musical',   'Stratford-upon-Avon High School', 2025, '["LD","Operator","Technical DSM","Technical Manager","Production Electrician"]'],
        [4, 'Cinderella',            'Cover — November & December 2025',2025, '["Cover"]'],
        [5, 'Machinal',              'Stratford-upon-Avon High School', 2024, '["Operator","Technical Lead"]'],
        [6, "Midsummer Night's Dream",'Stratford-upon-Avon High School',2024, '["LD","Operator","Technical DSM","Technical Manager","Production Electrician"]'],
        [7, 'Matilda',               'Stratford-upon-Avon High School', 2023, '["LD","Operator","Rigger"]'],
        [8, 'February Gig',          'Stratford-upon-Avon High School', 2024, '["LD","Operator","Technical DSM","Production Electrician"]'],
    ];
    $stmt = $db->prepare("INSERT INTO productions (sort_order, title, venue, year, tags) VALUES (?, ?, ?, ?, ?)");
    foreach ($productions as $p) {
        $stmt->execute($p);
    }

    // ── Skills ───────────────────────────────────────────────
    $skills = [
        'EOS ETC', 'CHAMSYS QUICKQ', 'Vari-lite FLX', 'Allen & Heath SQ7',
        'Qlab 5', 'Follow Spot Operation', 'Stage Rigging',
        'PA System Set-up', 'Stage Mgmt Comms', 'Production Electrician',
    ];
    $stmt = $db->prepare("INSERT INTO skills (sort_order, name) VALUES (?, ?)");
    foreach ($skills as $i => $name) {
        $stmt->execute([$i + 1, $name]);
    }
}
