<?php
require_once 'adminHeader.php'; // Includes session check

// --- Handle Session Messages ---
$errorMessage = '';
if (isset($_SESSION['quiz_error'])) {
    $errorMessage = $_SESSION['quiz_error'];
    unset($_SESSION['quiz_error']);
}
// Success messages are usually shown on the listing page after redirect,
// but you could add one here if needed after a failed validation attempt that returns.
// $successMessage = '';
// if (isset($_SESSION['quiz_success'])) { ... }

?>

<!-- Main Content Area for Adding Quiz -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Removed toggle button -->
                <div>
                    <h3>Add New Quiz</h3>
                    <p>Enter the details for the new quiz.</p>
                </div>
            </div>
             <div>
                 <a href="manageQuiz.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Quiz List</a>
            </div>
        </div>

        <!-- Display Session Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php // Add success message display here if needed ?>

        <!-- Add Quiz Form Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quiz Details</h5>

                <!-- The Form -->
                <form action="processAddQuiz.php" method="POST">
                    <!-- Optional: CSRF Token -->
                    <!-- <input type="hidden" name="csrf_token" value="<?php // echo generate_csrf_token(); ?>"> -->

                    <div class="mb-3">
                        <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="quizTitle" name="quizTitle" required placeholder="e.g., Philippine Mythology Basics">
                    </div>

                    <div class="mb-3">
                        <label for="quizDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="quizDescription" name="quizDescription" rows="4" placeholder="Briefly describe the quiz content or purpose."></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isActive" name="isActive" value="1">
                        <label class="form-check-label" for="isActive">Make this quiz active?</label>
                        <div class="form-text">Active quizzes will be visible and available for users to take.</div>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary">Save Quiz</button>
                </form>
                 <!-- End Form -->

            </div> <!-- End Card Body -->
        </div> <!-- End Card -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>