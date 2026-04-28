<?php
require_once __DIR__ . '/session_handler.php';
session_start(); session_unset(); session_destroy();
header('Location: ../login.php?logout=1'); exit();
