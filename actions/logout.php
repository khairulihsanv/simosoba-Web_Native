<?php
// actions/logout.php

require_once dirname(__DIR__) . '/init.php';

session_destroy();
header("Location: ../index.php?page=login");
exit();
?>
