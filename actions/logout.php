<?php
// actions/logout.php

require_once dirname(__DIR__) . '/init.php';

// 1. Clear signed auth cookie (serverless auth)
_smsb_clearAuthCookie();

// 2. Clear Remember Me token from DB + cookie
$auth->clearRememberMe();

// 3. Wipe session data
$_SESSION = [];

// 4. Delete session cookie
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// 5. Destroy session
@session_destroy();

header('Location: ' . BASE_URL . '/?page=login');
exit();
