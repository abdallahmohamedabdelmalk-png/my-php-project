
<?php
require "auth.php";
require "db.php";

$error = "";
$success = "";

$name = "";
$email = "";
$age = "";
$grade = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $age = filter_var($_POST["age"] ?? "", FILTER_VALIDATE_INT);
    $grade = filter_var($_POST["grade"] ?? "", FILTER_VALIDATE_INT);
    $password = $_POST["password"] ?? "";

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $age === false || $age === null || $age < 1 || $age > 120 ||
        $grade === false || $grade === null || $grade < 0 || $grade > 100 ||
        strlen($password) < 8
    ) {
        $error = "Please enter valid information. Password must be at least 8 characters.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id FROM students WHERE email = ?"
        );
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO students (name, email, age, grade, password)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $age,
                $grade,
                $hashedPassword
            ]);

            $success = "Student added successfully!";

            $name = "";
            $email = "";
            $age = "";
            $grade = "";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container py-5" style="max-width: 700px;">

    <h1 class="mb-2">Add New Student</h1>
    <p class="text-muted mb-4">Enter the student's information below.</p>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST" action="add_student.php">

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?= htmlspecialchars($name) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input
                        type="number"
                        id="age"
                        name="age"
                        class="form-control"
                        min="1"
                        max="120"
                        value="<?= htmlspecialchars((string)$age) ?>"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="grade" class="form-label">Grade (%)</label>
                    <input
                        type="number"
                        id="grade"
                        name="grade"
                        class="form-control"
                        min="0"
                        max="100"
                        value="<?= htmlspecialchars((string)$grade) ?>"
                        required
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    minlength="8"
                    required
                >
                <small class="text-muted">
                    Use at least 8 characters.
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                Add Student
            </button>

            <a href="students.php" class="btn btn-outline-secondary ms-2">
                Back to Students
            </a>

        </form>
    </div>

</div>
</body>
</html>
