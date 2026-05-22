<?php
// actions/logout.php

require_once dirname(__DIR__) . '/init.php';

// 1. Clear Remember Me cookie & DB token
$auth->clearRememberMe();

// 2. Unset all session variables
$_SESSION = [];

// 3. Delete session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 4. Destroy session data (removes row from php_sessions table)
session_destroy();

header('Location: ' . BASE_URL . '/?page=login');
exit();
