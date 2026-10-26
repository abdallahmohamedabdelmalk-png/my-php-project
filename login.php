

<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index1.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $pdo->prepare(
    "SELECT * FROM students WHERE email = ? LIMIT 1"
);
$stmt->execute([$email]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);


$passwordMatches = false;

if ($student) {
    $storedPassword = $student["password"];

    // Check passwords that are already hashed.
    if (password_verify($password, $storedPassword)) {
        $passwordMatches = true;
    } else {
        // Check whether the stored password is legacy plain text.
        $info = password_get_info($storedPassword);

        if (
        empty($info["algo"]) &&
            hash_equals($storedPassword, $password)
        ) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);

            $update = $pdo->prepare(
                "UPDATE students SET password = ? WHERE id = ?"
            );

            $update->execute([$newHash, $student["id"]]);

            $student["password"] = $newHash;
            $passwordMatches = true;
        }
    }
}



if ($student && $passwordMatches) {

    session_regenerate_id(true);

    $_SESSION["logged_in"] = true;
    $_SESSION["user_id"] = (int) $student["id"];
    $_SESSION["role"] = $student["role"];

    $_SESSION["student_name"] = $student["name"];
    $_SESSION["student_age"] = $student["age"];
    $_SESSION["student_grade"] = $student["grade"];
    $_SESSION["student_email"] = $student["email"];

    header("Location: dashbord.php");
    exit;

} else {
    header("Location: index1.php?error=invalid");
    exit;
}
