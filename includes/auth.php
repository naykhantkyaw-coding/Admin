<?php

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Optional: Check if user is approved
if ($_SESSION['status'] !== 'Approved') {
    session_destroy();
    header("Location: ../login.php?error=Account pending approval");
    exit();
}
?>