

<?php
session_start();
require_once "db.php";

$error = "";
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $ageInput = $_POST["age"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $requestedRole = $_POST["role"] ?? "";

    $age = ($ageInput === "")
        ? null
        : filter_var($ageInput, FILTER_VALIDATE_INT);

    if ($name === "" || $email === "" || $password === "") {
        $error = "من فضلك املأ الاسم والبريد الإلكتروني وكلمة المرور.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "البريد الإلكتروني غير صحيح.";

    } elseif (strlen($password) < 8) {
        $error = "كلمة المرور لازم تكون 8 أحرف على الأقل.";

    } elseif (
        $ageInput !== "" &&
        ($age === false || $age < 5 || $age > 100)
    ) {
        $error = "السن لازم يكون من 5 إلى 100 سنة.";

    } elseif (!in_array($gender, ["male", "female"], true)) {
        $error = "من فضلك اختر ذكر أو أنثى.";

    } elseif (!in_array($requestedRole, ["student", "teacher"], true)) {
        $error = "من فضلك اختر طالب أو مدرس.";

    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM students WHERE email = ?"
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $error = "البريد الإلكتروني مسجل بالفعل.";

            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $status = ($requestedRole === "teacher")
                    ? "pending"
                    : "approved";

                $stmt = $pdo->prepare(
                    "INSERT INTO students
                    (name, email, password, age, gender, role, teacher_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $name,
                    $email,
                    $hashedPassword,
                    $age,
                    $gender,
                    $requestedRole,
                    $status
                ]);

                if ($requestedRole === "teacher") {
                    header("Location: index1.php?pending=1");
                } else {
                    header("Location: index1.php?registered=1");
                }

                exit;
            }

        } catch (PDOException $e) {
            $error = "حصلت مشكلة أثناء إنشاء الحساب. راجع إعدادات قاعدة البيانات.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>

<div class="register-container">
    <h1>إنشاء حساب جديد</h1>
    <p>انضم إلى منصتنا التعليمية وابدأ التعلم.</p>

    <?php if ($error !== ""): ?>
        <div class="error-message">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="name">الاسم بالكامل</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>"
            maxlength="100"
            autocomplete="name"
            required
        >

        <label for="email">البريد الإلكتروني</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
            maxlength="150"
            autocomplete="email"
            required
        >

        <label for="password">كلمة المرور</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <label for="age">السن (اختياري)</label>
        <input
            type="number"
            id="age"
            name="age"
            min="5"
            max="100"
        >

      
<!-- نوع الحساب -->
<div class="mb-3">
    
<label for="role">نوع الحساب</label>
<select id="role" name="role" disabled>
    <option value="student" selected>طالب</option>
    <option value="teacher">مدرس</option>
</select>
<p>لإنشاء حساب مدرس، تواصل مع مسؤول المنصة.</p>

</div>

<!-- النوع -->
<div class="mb-3">
    <label for="gender" class="form-label">النوع</label>
    <select name="gender" id="gender" class="form-select" required>
        <option value="">اختر النوع</option>
        <option value="male">ذكر</option>
        <option value="female">أنثى</option>
    </select>
</div>


        <button type="submit">إنشاء الحساب</button>
    </form>

    <p class="login-link">
        عندك حساب بالفعل؟
        <a href="index1.php">سجّل دخولك</a>
    </p>
</div>

</body>
</html>
