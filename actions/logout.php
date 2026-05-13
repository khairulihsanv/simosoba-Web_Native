<?php
// actions/logout.php

require_once dirname(__DIR__) . '/init.php';

// 1. Clear Remember Me (Database & Cookie)
$auth->clearRememberMe();

// 2. Clear Session
session_unset();
session_destroy();

// 3. Clear Session Cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: ../index.php?page=login");
exit();
?>
