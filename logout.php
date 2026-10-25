
<?php
session_start();

$_SESSION = [];

session_destroy();

header("Location: index1.php");
exit;
