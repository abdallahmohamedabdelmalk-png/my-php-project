<?php
require "auth.php";
require "db.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $stmt = $pdo->prepare(
        "SELECT id, name, email, age, grade
         FROM students
         WHERE name LIKE ? OR email LIKE ?
         ORDER BY id ASC"
    );

    $term = "%" . $search . "%";
    $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query(
        "SELECT id, name, email, age, grade
         FROM students
         ORDER BY id ASC"
    );
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>All Students</h1>
            <p class="text-muted">Search and view student information</p>
        </div>

        <a href="dashbord.php" class="btn btn-outline-primary">
            Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm p-4 mb-4">
        <form method="GET" action="students.php">
            <div class="input-group">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    placeholder="Search by name or email..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button type="submit" class="btn btn-primary">
                    Search
                </button>

                <a href="students.php" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                   <tr>
                       <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>Grade</th>
                        <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
               
            <?php if (count($students) > 0): ?>
               <?php foreach ($students as $student): ?>
                   <tr>
                        <td><?= (int) $student["id"] ?></td>
                        <td><?= htmlspecialchars($student["name"]) ?></td>
                        <td><?= htmlspecialchars($student["email"]) ?></td>
                        <td><?= htmlspecialchars((string) $student["age"]) ?></td>
                      <td><?= htmlspecialchars((string) $student["grade"]) ?>%</td> 
            <td>
                <a
                    href="edit_student.php?id=<?= (int) $student["id"] ?>"
                    class="btn btn-sm btn-warning"
                >
                    Edit
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="text-center py-4">
            No students found.
        </td>
    </tr>
<?php endif; ?>

                </tbody>
            </table>
        </div>

        <p class="text-muted mb-0">
            Results found: <?= count($students) ?>
        </p>
    </div>

</div>
</body>
</html>
