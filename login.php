<?php


// Include the common user header
require_once 'header.php';

// Check if the user is already logged in, redirect if they are
// Use the USER session variable you plan to set upon successful user login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect logged-in users away from login page
    exit();
}


// Check for login error messages from processUserLogin.php
$loginError = '';
if (isset($_SESSION['login_error'])) {
    $loginError = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Clear the message after displaying
}

// Check for success messages (e.g., after registration)
$successMessage = '';
if (isset($_SESSION['register_success'])) {
    $successMessage = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}


?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

             <!-- Use a similar card structure, applying content-section styles -->
            <div class="content-section mt-4 mb-5">
                <h2 class="text-center mb-4">User Login</h2>

                 <!-- Display Success Message (e.g., after registration) -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success mb-3">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                 <!-- Display Login Error Message -->
                <?php if (!empty($loginError)): ?>
                    <div class="alert alert-danger mb-3">
                        <?php echo htmlspecialchars($loginError); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="processUserLogin.php" method="POST" novalidate> <!-- Point to user login processor -->

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                         <!-- Consider adding placeholder="Enter your username" -->
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                         <!-- Consider adding placeholder="Enter your password" -->
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>

                    <div class="text-center">
                        <small class="text-muted">
                            <!-- Forgot Password Link (Implement later) -->
                            <!-- <a href="forgotPassword.php">Forgot Password?</a> | -->
                            Don't have an account? <a href="register.php">Register Here</a>
                        </small>
                    </div>

                </form>
                <!-- End Form -->

            </div><!-- /.content-section -->

        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div> <!-- /.container -->


<?php require_once 'footer.php'; ?>