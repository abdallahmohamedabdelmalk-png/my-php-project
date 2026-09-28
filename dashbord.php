<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>

    <!-- ================= BOOTSTRAP ================= -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- ================= CUSTOM CSS ================= -->
    <link rel="stylesheet" href="./style.css">

</head>

<body>

    <!-- ================= DASHBOARD ================= -->

    <div class="container py-5">

        <div class="mb-5">

            <h1>Student Dashboard</h1>

            <p class="text-muted">
                Welcome to your Student Portal
            </p>

        </div>


        <!-- ================= STUDENT INFO ================= -->

        <div class="row g-4">

            <div class="col-md-4">

                <div class="card shadow-sm p-4">

                    <h5>Student Name</h5>

                    <p class="text-muted mb-0">
                        Abdallah Mohamed
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card shadow-sm p-4">

                    <h5>Age</h5>

                    <p class="text-muted mb-0">
                        23
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card shadow-sm p-4">

                    <h5>Grade</h5>

                    <p class="text-muted mb-0">
                        85%
                    </p>

                </div>

            </div>

        </div>


        <!-- ================= COURSES ================= -->

        <div class="card shadow-sm p-4 mt-4">

            <h4 class="mb-3">
                My Courses
            </h4>

            <ul class="list-group">

                <li class="list-group-item">
                    PHP
                </li>

                <li class="list-group-item">
                    JavaScript
                </li>

                <li class="list-group-item">
                    MySQL
                </li>

            </ul>

        </div>


        <!-- ================= LOGOUT ================= -->

        <div class="mt-4">

            <a href="index1.php" class="btn btn-outline-danger">
                Logout
            </a>

        </div>

    </div>

</body>

</html>