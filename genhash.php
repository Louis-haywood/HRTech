<?php
// ONE-TIME USE — delete this file immediately after use
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['p'])) {
    echo htmlspecialchars(password_hash($_POST['p'], PASSWORD_DEFAULT));
    exit;
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Hash Generator</title></head>
<body style="font-family:monospace;padding:2rem;background:#111;color:#eee">
<form method="POST">
  <input type="text" name="p" placeholder="password" style="padding:.5rem;width:300px" />
  <button type="submit" style="padding:.5rem 1rem">Generate</button>
</form>
</body></html>
