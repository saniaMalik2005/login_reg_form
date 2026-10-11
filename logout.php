<?php
session_start();
session_unset();
session_destroy();

// কুকি ডিলিট করা
if (isset($_COOKIE['remember_email'])) {
    setcookie('remember_email', '', time() - 3600, "/");
}

header("Location: login.php");
exit();
?>