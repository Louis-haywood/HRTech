<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Version check — used by the public site to detect content changes
if (($_GET['action'] ?? '') === 'version') {
    json_response(['v' => setting('last_updated', '0')]);
}

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Parse body (supports JSON or form-encoded)
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];
if (empty($data)) {
    $data = $_POST;
}

$name    = trim($data['name']         ?? '');
$email   = trim($data['email']        ?? '');
$phone   = trim($data['phone']        ?? '');
$project = trim($data['project_type'] ?? '');
$message = trim($data['message']      ?? '');

if (!$name || !$email || !$message) {
    json_response(['error' => 'Name, email and message are required.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Please enter a valid email address.'], 422);
}

$db   = get_db();
$stmt = $db->prepare(
    "INSERT INTO submissions (name, email, phone, project_type, message)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$name, $email, $phone, $project, $message]);

// Try to send a notification email to the site owner
$owner = setting('contact_email');
if ($owner && filter_var($owner, FILTER_VALIDATE_EMAIL)) {
    $subject = "New enquiry from {$name} — HR Lighting Services";
    $body    = "You have a new website enquiry.\n\n"
             . "Name:    {$name}\n"
             . "Email:   {$email}\n"
             . "Phone:   {$phone}\n"
             . "Project: {$project}\n\n"
             . "Message:\n{$message}\n\n"
             . "---\nView all enquiries in your admin panel.";
    $headers = "From: no-reply@hrlighting.co.uk\r\nReply-To: {$email}";
    @mail($owner, $subject, $body, $headers);
}

json_response(['success' => true]);
