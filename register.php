<?php
// Include the common user header
require_once 'header.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Or maybe a user dashboard page
    exit();
}

// Get registration error messages (if redirected back from processing script)
$errors = $_SESSION['register_errors'] ?? [];
// Get previously submitted form data to repopulate fields (optional but good UX)
$old_input = $_SESSION['old_register_input'] ?? [];

// Clear session variables after retrieving them
unset($_SESSION['register_errors']);
unset($_SESSION['old_register_input']);
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9"> <!-- Made slightly wider for more fields -->

             <!-- Use content-section for consistent styling -->
            <div class="content-section mt-4 mb-5 p-4 p-md-5 border rounded shadow-sm bg-white">
                <h2 class="text-center mb-4">Register New Account</h2>

                 <!-- Display Validation Errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-3">
                        <h5 class="alert-heading">Please fix the following errors:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="processRegistration.php" method="POST" novalidate>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required value="<?php echo htmlspecialchars($old_input['firstName'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required value="<?php echo htmlspecialchars($old_input['lastName'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($old_input['username'] ?? ''); ?>">
                        <div class="form-text">Must be unique. Only letters, numbers, and underscores allowed. Min 3 characters.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>">
                        <div class="form-text">Must be a valid and unique email address.</div>
                    </div>

                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required aria-describedby="passwordHelp">
                             <div id="passwordHelp" class="form-text">Minimum 8 characters.</div>
                        </div>
                        <div class="col-md-6 mb-4">
                             <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>

                    <div class="text-center">
                        <small class="text-muted">
                            Already have an account? <a href="login.php">Login Here</a>
                        </small>
                    </div>
                </form>
                <!-- End Form -->
            </div><!-- /.content-section -->
        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>