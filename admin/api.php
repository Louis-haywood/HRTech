<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('X-Content-Type-Options: nosniff');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ── Public actions ────────────────────────────────────────────────────────────

if ($action === 'login' && $method === 'POST') {
    $password = $_POST['password'] ?? '';
    if (attempt_login($password)) {
        json_response(['success' => true, 'csrf' => csrf_token()]);
    } else {
        json_response(['error' => 'Incorrect password. Please try again.'], 401);
    }
}

if ($action === 'logout') {
    do_logout();
    json_response(['success' => true]);
}

if ($action === 'check') {
    json_response(['logged_in' => is_logged_in()]);
}

// ── Protected actions — everything below requires login ───────────────────────

if (!is_logged_in()) {
    json_response(['error' => 'Unauthorised'], 401);
}

// CSRF guard for all state-changing requests
if ($method === 'POST' && $action !== 'login') {
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf($token)) {
        json_response(['error' => 'Invalid request token. Please refresh and try again.'], 403);
    }
}

$db = get_db();

switch ($action) {

    // ── SETTINGS ─────────────────────────────────────────────────────────────

    case 'get_settings':
        $rows = $db->query(
            "SELECT `key`, value FROM settings WHERE `key` != 'admin_password' AND `key` != 'first_login'"
        )->fetchAll(PDO::FETCH_KEY_PAIR);
        json_response($rows);

    case 'save_settings':
        $allowed = [
            'site_name', 'hero_badge', 'hero_heading', 'hero_subtext',
            'about_heading', 'about_text_1', 'about_text_2',
            'stat_1_number', 'stat_1_label', 'stat_2_number', 'stat_2_label',
            'contact_address', 'contact_phone', 'contact_email',
        ];
        $stmt = $db->prepare("REPLACE INTO settings (`key`, value) VALUES (?, ?)");
        foreach ($allowed as $key) {
            if (array_key_exists($key, $_POST)) {
                $stmt->execute([$key, trim($_POST[$key])]);
            }
        }
        json_response(['success' => true]);

    case 'change_password':
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $hash    = $db->query("SELECT value FROM settings WHERE `key` = 'admin_password'")->fetchColumn();
        if (!password_verify($current, $hash)) {
            json_response(['error' => 'Current password is incorrect.'], 401);
        }
        if (strlen($new) < 6) {
            json_response(['error' => 'New password must be at least 6 characters.'], 422);
        }
        $db->prepare("UPDATE settings SET value = ? WHERE `key` = 'admin_password'")->execute([
            password_hash($new, PASSWORD_DEFAULT),
        ]);
        $db->prepare("UPDATE settings SET value = '0' WHERE `key` = 'first_login'")->execute();
        json_response(['success' => true]);

    // ── SERVICES ─────────────────────────────────────────────────────────────

    case 'get_services':
        json_response($db->query("SELECT * FROM services ORDER BY sort_order")->fetchAll());

    case 'save_service': {
        $id       = (int)($_POST['id'] ?? 0);
        $icon     = trim($_POST['icon']  ?? '💡');
        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body']  ?? '');
        $featured = (int)($_POST['featured'] ?? 0);
        $order    = (int)($_POST['sort_order'] ?? 0);

        if (!$title) {
            json_response(['error' => 'Service title is required.'], 422);
        }

        // Only one service can be featured at a time
        if ($featured) {
            $db->prepare("UPDATE services SET featured = 0 WHERE id != ?")->execute([$id]);
        }

        if ($id) {
            $db->prepare(
                "UPDATE services SET icon=?, title=?, body=?, featured=?, sort_order=? WHERE id=?"
            )->execute([$icon, $title, $body, $featured, $order, $id]);
        } else {
            $max = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM services")->fetchColumn();
            $db->prepare(
                "INSERT INTO services (icon, title, body, featured, sort_order) VALUES (?,?,?,?,?)"
            )->execute([$icon, $title, $body, $featured, $max + 1]);
            $id = (int)$db->lastInsertId();
        }
        json_response(['success' => true, 'id' => $id]);
    }

    case 'delete_service':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        }
        json_response(['success' => true]);

    // ── PRODUCTIONS ──────────────────────────────────────────────────────────

    case 'get_productions': {
        $rows = $db->query("SELECT * FROM productions ORDER BY sort_order")->fetchAll();
        foreach ($rows as &$row) {
            $row['tags'] = json_decode($row['tags'], true) ?: [];
        }
        json_response($rows);
    }

    case 'save_production': {
        $id    = (int)($_POST['id']    ?? 0);
        $title = trim($_POST['title']  ?? '');
        $venue = trim($_POST['venue']  ?? '');
        $year  = (int)($_POST['year']  ?? date('Y'));
        $order = (int)($_POST['sort_order'] ?? 0);

        // Tags arrive as comma-separated string from the form
        $raw_tags = trim($_POST['tags'] ?? '');
        $tags     = array_values(array_filter(array_map('trim', explode(',', $raw_tags))));
        $tags_json = json_encode($tags, JSON_UNESCAPED_UNICODE);

        if (!$title) {
            json_response(['error' => 'Production title is required.'], 422);
        }

        if ($id) {
            $db->prepare(
                "UPDATE productions SET title=?, venue=?, year=?, tags=?, sort_order=? WHERE id=?"
            )->execute([$title, $venue, $year, $tags_json, $order, $id]);
        } else {
            $max = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM productions")->fetchColumn();
            $db->prepare(
                "INSERT INTO productions (title, venue, year, tags, sort_order) VALUES (?,?,?,?,?)"
            )->execute([$title, $venue, $year, $tags_json, $max + 1]);
            $id = (int)$db->lastInsertId();
        }
        json_response(['success' => true, 'id' => $id]);
    }

    case 'delete_production':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM productions WHERE id = ?")->execute([$id]);
        }
        json_response(['success' => true]);

    // ── SKILLS ───────────────────────────────────────────────────────────────

    case 'get_skills':
        json_response($db->query("SELECT * FROM skills ORDER BY sort_order")->fetchAll());

    case 'save_skill': {
        $id    = (int)($_POST['id']   ?? 0);
        $name  = trim($_POST['name']  ?? '');
        $order = (int)($_POST['sort_order'] ?? 0);

        if (!$name) {
            json_response(['error' => 'Skill name is required.'], 422);
        }

        if ($id) {
            $db->prepare("UPDATE skills SET name=?, sort_order=? WHERE id=?")->execute([$name, $order, $id]);
        } else {
            $max = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM skills")->fetchColumn();
            $db->prepare("INSERT INTO skills (name, sort_order) VALUES (?,?)")->execute([$name, $max + 1]);
            $id = (int)$db->lastInsertId();
        }
        json_response(['success' => true, 'id' => $id]);
    }

    case 'delete_skill':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM skills WHERE id = ?")->execute([$id]);
        }
        json_response(['success' => true]);

    // ── SUBMISSIONS ──────────────────────────────────────────────────────────

    case 'get_submissions':
        json_response($db->query(
            "SELECT * FROM submissions ORDER BY submitted_at DESC"
        )->fetchAll());

    case 'mark_read':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("UPDATE submissions SET is_read = 1 WHERE id = ?")->execute([$id]);
        }
        json_response(['success' => true]);

    case 'delete_submission':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM submissions WHERE id = ?")->execute([$id]);
        }
        json_response(['success' => true]);

    // ── DEFAULT ──────────────────────────────────────────────────────────────

    default:
        json_response(['error' => 'Unknown action.'], 404);
}
