
<?php
session_start();
require_once "db.php";

// التأكد إن المستخدم مسجل دخول
if (
    empty($_SESSION["logged_in"]) ||
    empty($_SESSION["user_id"])
) {
    header("Location: index1.php");
    exit;
}

// الادمن فقط يقدر يحذف الكورسات
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("غير مسموح لك بحذف الكورسات. الأدمن فقط.");
}

// الحذف لازم يتم عن طريق POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: courses.php");
    exit;
}

// التحقق من CSRF
if (
    empty($_POST["csrf_token"]) ||
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    exit("طلب غير صالح. ارجع للصفحة وحاول مرة أخرى.");
}

$course_id = filter_input(INPUT_POST, "course_id", FILTER_VALIDATE_INT);

if (!$course_id || $course_id < 1) {
    exit("رقم الكورس غير صحيح.");
}


$stmt = $pdo->prepare(
    "DELETE FROM courses WHERE id = ?"
);
$stmt->execute([$course_id]);

header("Location: courses.php");
exit;

