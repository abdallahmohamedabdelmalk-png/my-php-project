<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Portal</title>

    <!-- ================= BOOTSTRAP ================= -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- ================= CUSTOM CSS ================= -->
    <link rel="stylesheet" href="./style.css">
</head>

<body>

    <!-- ================= LOGIN PAGE ================= -->

    <div class="container min-vh-100 d-flex justify-content-center align-items-center">

        <div class="login-card">

            <!-- Logo / Title -->
            <div class="text-center mb-4">

                <div class="portal-logo">
                    SP
                </div>

                <h2>Welcome Back</h2>

                <p class="text-muted">
                    Sign in to your Student Portal
                </p>

            </div>


            <!-- Login Form -->
       <form method="post" action="login.php">
                <!-- Email -->
                <div class="mb-3">

                    <label class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control modern-input"
                        placeholder="Enter your email"
                        required

                    >

                </div>


                <!-- Password -->
                <div class="mb-4">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control modern-input"
                        placeholder="Enter your password"
                        required

                    >

                </div>


                <!-- Login Button -->
                <button
                    type="submit"
                    class="btn login-btn w-100"
                >
                    Login
                </button>

            </form>
            
            </form>

            <!-- Register Link -->
            <div class="text-center mt-4">
                <p class="mb-2">Don't have an account?</p>
                <a href="register.php" class="btn btn-outline-primary w-100">
                    Create New Account
                </a>
            </div>



            <!-- Footer -->
            <p class="text-center text-muted small mt-4 mb-0">
                Student Portal © 2026
            </p>

        </div>

    </div>

</body>
</html>