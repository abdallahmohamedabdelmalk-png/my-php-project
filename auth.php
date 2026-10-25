
<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true) {
    header("Location: index1.php");
    exit;
}
