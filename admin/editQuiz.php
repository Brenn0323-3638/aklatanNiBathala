<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Get and Validate Quiz ID from URL ---
$quizId = null;
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
} else {
    $_SESSION['quiz_error'] = "Invalid Quiz ID provided for editing.";
    header('Location: manageQuiz.php'); // Redirect back to quiz list
    exit;
}

// --- Fetch Existing Quiz Data ---
$quizData = null;
$errorMessage = '';

try {
    $pdo = connect_db();
    if ($pdo) {
        $sql = "SELECT quiz_id, title, description, is_active FROM quizzes WHERE quiz_id = :quizId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmt->execute();
        $quizData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quizData) {
            // Quiz ID from URL doesn't exist
            $_SESSION['quiz_error'] = "Quiz not found for ID: " . $quizId;
            header('Location: manageQuiz.php');
            exit;
        }
    } else {
        $errorMessage = "Database connection failed.";
    }
} catch(PDOException $e) {
    $errorMessage = "Database error fetching quiz details: " . $e->getMessage();
    error_log("Edit Quiz Fetch Error (ID: {$quizId}): " . $e->getMessage());
}

// --- Handle Session Error Messages (from processEditQuiz) ---
if (isset($_SESSION['quiz_error'])) {
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['quiz_error'] : $_SESSION['quiz_error'];
    unset($_SESSION['quiz_error']);
}

?>

<!-- Main Content Area for Editing Quiz -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Edit Quiz</h3>
                    <p>Modify details for: <strong><?php echo $quizData ? htmlspecialchars($quizData['title']) : 'Quiz ID ' . $quizId; ?></strong></p>
                </div>
            </div>
             <div>
                 <a href="manageQuiz.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Quiz List</a>
            </div>
        </div>

        <!-- Display Session Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; // Might contain HTML ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($quizData && empty($errorMessage)): // Only show form if data loaded ?>
        <!-- Edit Quiz Form Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quiz Details</h5>

                <!-- The Form -->
                <form action="processEditQuiz.php" method="POST">
                    <!-- *** IMPORTANT: Hidden field for quiz_id *** -->
                    <input type="hidden" name="quizId" value="<?php echo $quizData['quiz_id']; ?>">

                    <div class="mb-3">
                        <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="quizTitle" name="quizTitle" required value="<?php echo htmlspecialchars($quizData['title']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="quizDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="quizDescription" name="quizDescription" rows="4"><?php echo htmlspecialchars($quizData['description']); ?></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isActive" name="isActive" value="1" <?php echo $quizData['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="isActive">Make this quiz active?</label>
                        <div class="form-text">Active quizzes will be visible and available for users to take.</div>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
                 <!-- End Form -->

            </div> <!-- End Card Body -->
        </div> <!-- End Card -->
        <?php endif; ?>

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>