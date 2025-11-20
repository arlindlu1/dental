<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Helpers.php';
require_once __DIR__ . '/lib/Auth.php';

$db = Database::getInstance();
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$token = trim($token);

if (!$token) {
    echo "Invalid invite token.";
    exit;
}

// Lookup invite
$stmt = $db->prepare("SELECT * FROM invites WHERE token = ? AND used = 0 AND (expires_at IS NULL OR expires_at >= NOW()) LIMIT 1");
$stmt->bind_param("s", $token);
$res = safe_stmt_fetch_assoc($stmt);
if (!$res) {
    echo "Invite token not found or expired.";
    exit;
}

$email = $res['email'];
$clinicId = $res['clinic_id'];

// Handle password set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !Auth::verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (empty($password) || $password !== $password2) {
            $error = 'Passwords do not match or are empty.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Check if user exists within the same clinic
            $uStmt = $db->prepare("SELECT id FROM users WHERE email = ? AND clinic_id = ? LIMIT 1");
            $uStmt->bind_param("si", $email, $clinicId);
            $user = safe_stmt_fetch_assoc($uStmt);

            if ($user) {
                // Update password and activate user (scoped to clinic)
                $up = $db->prepare("UPDATE users SET password = ?, is_active = 1 WHERE id = ? AND clinic_id = ?");
                $up->bind_param("sii", $hashed, $user['id'], $clinicId);
                $up->execute();
                $userId = $user['id'];
            } else {
                // Create user with admin role
                $name = 'Admin';
                $ins = $db->prepare("INSERT INTO users (clinic_id, email, password, name, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)");
                $ins->bind_param("isss", $clinicId, $email, $hashed, $name);
                $ins->execute();
                $userId = $db->lastInsertId();
            }

            // Mark invite used (scoped to clinic)
            $mark = $db->prepare("UPDATE invites SET used = 1, used_at = NOW() WHERE id = ? AND clinic_id = ?");
            $mark->bind_param("ii", $res['id'], $clinicId);
            $mark->execute();

            // Optional: log activity (if session available)
            if (method_exists('Auth', 'logActivity')) {
                Auth::logActivity('accept_invite', 'invite', $res['id'], json_encode(['email' => $email, 'user_id' => $userId]));
            }

            echo "Password set successfully. You can now <a href=\"/login.php\">login</a>.";
            exit;
        }
    }
}


?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accept Invite</title>
    <style>body{font-family:Arial,Helvetica,sans-serif; padding:24px;}</style>
</head>
<body>
    <h1>Set your password</h1>
    <p>Setting password for: <strong><?= htmlspecialchars($email) ?></strong></p>
    <?php if (!empty($error)): ?>
        <div style="color:red; margin-bottom:12px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
        <div style="margin-bottom:8px;">
            <label>New password<br>
            <input type="password" name="password" required style="width: 300px; padding:6px;"></label>
        </div>
        <div style="margin-bottom:12px;">
            <label>Repeat password<br>
            <input type="password" name="password2" required style="width: 300px; padding:6px;"></label>
        </div>
        <div>
            <button type="submit" name="set_password">Set Password</button>
        </div>
    </form>
</body>
</html>
