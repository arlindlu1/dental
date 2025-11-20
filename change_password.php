<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Helpers.php';

if (!Auth::check()) {
    redirect('/login.php');
}

$user = Auth::user();
$db = Database::getInstance();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($new) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $clinicId = $_SESSION['clinic_id'] ?? 0;
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ? AND clinic_id = ?");
            $stmt->bind_param("sii", $hashed, $user['id'], $clinicId);
            $stmt->execute();
            // remove password change flag if present
            // This table stores flags keyed by `user_id` only; deleting by the current
            // authenticated user's id is safe and intentional (no clinic_id column exists).
            try {
                $stmt2 = $db->prepare("DELETE FROM password_change_required WHERE user_id = ?");
                if ($stmt2) {
                    $stmt2->bind_param("i", $user['id']);
                    $stmt2->execute();
                }
            } catch (Exception $e) {
                // ignore
            }
            $success = true;
        }
    }
}

include __DIR__ . '/views/layout/header.php';
?>
<div class="container" style="max-width:600px; margin:40px auto;">
    <div class="card">
        <h1>Set a new password</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success">Password updated. <a href="/views/dashboard.php">Continue</a></div><?php endif; ?>
        <?php if (!$success): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <div style="margin-top:12px;">
                <button class="btn btn-primary" type="submit">Set Password</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/views/layout/footer.php'; ?>
