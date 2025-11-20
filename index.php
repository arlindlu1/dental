<?php
/**
 * dentisti.pro - Main Entry Point
 * Routes to landing page or redirects to login/dashboard
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/lib/Helpers.php';

// Check if installed
if (!file_exists(__DIR__ . '/config/config.php')) {
    redirect('/install/installer.php');
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/Lang.php';

// Initialize language
$lang = new Lang();

if (isset($_GET['lang'])) {
    Lang::setLang($_GET['lang']);
    // Redirect to remove the query parameter
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// If user is logged in, redirect to dashboard
if (Auth::check()) {
    redirect('/views/dashboard.php');
}

// Show landing page
require_once __DIR__ . '/landing/index.php';
