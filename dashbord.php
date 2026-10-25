
<?php
require "auth.php";

$name  = $_SESSION["student_name"] ?? "Student";
$age   = $_SESSION["student_age"] ?? "Not provided";
$grade = $_SESSION["student_grade"] ?? "Not provided";
$email = $_SESSION["student_email"] ?? "Not provided";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="./style.css">
</head>

<body>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashbord.php">
                Student Portal
            </a>
            
                 <a href="students.php" class="btn btn-primary">
                       View All Students
                </a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                Logout
            </a>
        </div>
    </nav>

    <main class="container py-5">

        <div class="mb-5">
            <h1>Welcome, <?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?>!</h1>
            <p class="text-muted">
                Here is your student dashboard.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm p-4 h-100">
                    <h5>Student Name</h5>
                    <p class="text-primary mb-0">
                        <?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm p-4 h-100">
                    <h5>Age</h5>
                    <p class="text-primary mb-0">
                        <?= htmlspecialchars((string)$age, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm p-4 h-100">
                    <h5>Grade</h5>
                    <p class="text-primary mb-0">
                        <?= htmlspecialchars((string)$grade, ENT_QUOTES, 'UTF-8') ?><?= is_numeric($grade) ? '%' : '' ?>
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm p-4 h-100">
                    <h5>Email</h5>
                    <p class="text-primary mb-0 text-break">
                        <?= htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

        </div>

        <div class="card shadow-sm p-4 mt-4">
            <h4 class="mb-3">My Courses</h4>

            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between">
                    PHP
                    <span class="badge text-bg-primary">Programming</span>
                </li>

                <li class="list-group-item d-flex justify-content-between">
                    JavaScript
                    <span class="badge text-bg-warning">Frontend</span>
                </li>

                <li class="list-group-item d-flex justify-content-between">
                    MySQL
                    <span class="badge text-bg-success">Database</span>
                </li>
            </ul>
        </div>

    </main>

</body>
</html>
