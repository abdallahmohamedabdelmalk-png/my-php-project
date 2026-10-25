
<?php
require "auth.php";
require "db.php";

$error = "";
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    exit("Invalid student ID.");
}

// Get the existing student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    exit("Student not found.");
}

// Update student when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $age = filter_var($_POST["age"] ?? "", FILTER_VALIDATE_INT);
    $grade = filter_var($_POST["grade"] ?? "", FILTER_VALIDATE_INT);

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $age === false || $age === null || $age < 1 || $age > 120 ||
        $grade === false || $grade === null || $grade < 0 || $grade > 100
    ) {
        $error = "Please enter valid information.";
    } else {
        // Check whether another student uses this email
        $check = $pdo->prepare(
            "SELECT id FROM students WHERE email = ? AND id != ?"
        );
        $check->execute([$email, $id]);

        if ($check->fetch()) {
            $error = "This email is already used by another student.";
        } else {
            $update = $pdo->prepare(
                "UPDATE students
                 SET name = ?, email = ?, age = ?, grade = ?
                 WHERE id = ?"
            );

            $update->execute([$name, $email, $age, $grade, $id]);

            header("Location: students.php?updated=1");
            exit;
        }
    }

    // Keep submitted values in the form if validation fails
    $student["name"] = $name;
    $student["email"] = $email;
    $student["age"] = $_POST["age"] ?? "";
    $student["grade"] = $_POST["grade"] ?? "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container py-5" style="max-width: 700px;">

    <h1 class="mb-2">Edit Student</h1>
    <p class="text-muted mb-4">Update the student's information.</p>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST" action="edit_student.php">
            <input type="hidden" name="id" value="<?= (int) $id ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?= htmlspecialchars((string) $student["name"]) ?>"
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
                    value="<?= htmlspecialchars((string) $student["email"]) ?>"
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
                        value="<?= htmlspecialchars((string) $student["age"]) ?>"
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
                        value="<?= htmlspecialchars((string) $student["grade"]) ?>"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

            <a href="students.php" class="btn btn-outline-secondary ms-2">
                Cancel
            </a>
        </form>
    </div>

</div>
</body>
</html>
