<?php
require_once __DIR__ . "/session.php";
session_destroy();
if (function_exists('clearRememberMeCookie')) {
    clearRememberMeCookie();
}
header("Location: login.php");
exit;
?>
