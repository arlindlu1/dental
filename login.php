<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/Lang.php';
require_once __DIR__ . '/lib/Helpers.php';


// Only redirect to dashboard if a valid user record is present. Sessions may contain stale values.
if (Auth::user()) {
    redirect('/views/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Auth::verifyCSRFToken($_POST['csrf_token'])) {
        $error = Lang::current() === 'sq' ? 'Kërkesë e pavlefshme' : 'Invalid request';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (Auth::login($email, $password)) {
            // After login, force password change if required
            $db = Database::getInstance();
            $user = Auth::user();
            if ($user) {
                try {
                    $stmt = $db->prepare("SELECT 1 FROM password_change_required WHERE user_id = ? LIMIT 1");
                    if ($stmt) {
                        $stmt->bind_param("i", $user['id']);
                        $res = safe_stmt_fetch_assoc($stmt);
                        if ($res) {
                            redirect('/change_password.php?required=1');
                        }
                    }
                } catch (Exception $e) {
                    // ignore if migration not applied
                }
            }
            redirect('/views/dashboard.php');
        } else {
            $error = Lang::current() === 'sq' ? 'Email ose fjalëkalim i gabuar' : 'Invalid email or password';
        }
    }
}

// Handle language switch
if (isset($_GET['lang'])) {
    Lang::setLang($_GET['lang']);
    redirect('/login.php');
}
?>
<!DOCTYPE html>
<html lang="<?= Lang::current() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Lang::current() === 'sq' ? 'Hyrje' : 'Login' ?> - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-container { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 420px; width: 100%; padding: 48px 40px; }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo h1 { font-size: 32px; color: #1a202c; margin-bottom: 8px; }
        .logo p { color: #718096; font-size: 14px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #2d3748; font-size: 14px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; }
        input:focus { outline: none; border-color: #667eea; }
        .btn { background: #667eea; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; transition: background 0.2s; }
        .btn:hover { background: #5568d3; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; background: #fed7d7; color: #c53030; font-size: 14px; }
        .lang-switch { text-align: center; margin-top: 24px; }
        .lang-switch a { color: #667eea; text-decoration: none; margin: 0 8px; font-size: 14px; }
        .lang-switch a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1><?= APP_NAME ?></h1>
            <p><?= Lang::current() === 'sq' ? 'Hyni në llogarinë tuaj' : 'Login to your account' ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
            <div class="form-group">
                <label><?= Lang::current() === 'sq' ? 'Email' : 'Email' ?></label>
                <input type="email" name="email" required autofocus>
            </div>
            
            <div class="form-group">
                <label><?= Lang::current() === 'sq' ? 'Fjalëkalimi' : 'Password' ?></label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn"><?= Lang::current() === 'sq' ? 'Hyr' : 'Login' ?></button>
        </form>
        
        <div class="lang-switch">
            <a href="?lang=sq">Shqip</a> | <a href="?lang=en">English</a>
        </div>
    </div>
</body>
</html>
