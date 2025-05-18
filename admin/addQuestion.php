<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Get and Validate Quiz ID from URL (Crucial to know which quiz we're adding to) ---
$quizId = null;
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
} else {
    $_SESSION['quiz_error'] = "Invalid or missing Quiz ID for adding question.";
    header('Location: manageQuiz.php'); // Redirect back to quiz list
    exit;
}

// --- Fetch Quiz Title (for display) ---
$quizTitle = "Quiz ID: " . $quizId; // Default title
$errorMessage = '';

try {
    $pdo = connect_db();
    if ($pdo) {
        $sqlQuiz = "SELECT title FROM quizzes WHERE quiz_id = :quizId";
        $stmtQuiz = $pdo->prepare($sqlQuiz);
        $stmtQuiz->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtQuiz->execute();
        $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);
        if ($quiz) {
            $quizTitle = $quiz['title'];
        } else {
             // Quiz not found, redirect
            $_SESSION['quiz_error'] = "Quiz not found for ID: " . $quizId;
            header('Location: manageQuiz.php');
            exit;
        }
    } else {
        $errorMessage = "Database connection failed.";
    }
} catch(PDOException $e) {
     $errorMessage = "Database error: " . $e->getMessage();
     error_log("Error fetching quiz title for add question (Quiz ID: {$quizId}): " . $e->getMessage());
}


// --- Handle Session Error Messages (from processAddQuestion) ---
if (isset($_SESSION['question_error'])) {
    // Append or set error message
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['question_error'] : $_SESSION['question_error'];
    unset($_SESSION['question_error']);
}

?>

<!-- Main Content Area for Adding Question -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Add New Question</h3>
                    <p>For Quiz: <strong><?php echo htmlspecialchars($quizTitle); ?></strong></p>
                </div>
            </div>
             <div>
                 <a href="manageQuestions.php?quiz_id=<?php echo $quizId; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Questions</a>
            </div>
        </div>

        <!-- Display Session Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; // Might contain HTML breaks ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add Question Form Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Question Details</h5>

                <!-- The Form points to processAddQuestion.php -->
                <form action="processAddQuestion.php" method="POST">
                    <!-- *** IMPORTANT: Hidden field for quiz_id *** -->
                    <input type="hidden" name="quizId" value="<?php echo $quizId; ?>">

                    <div class="mb-3">
                        <label for="questionText" class="form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="questionText" name="questionText" rows="4" required placeholder="Enter the full question here..."></textarea>
                    </div>

                    <!-- Question Type Selection (Start with Multiple Choice) -->
                    <div class="mb-3">
                        <label for="questionType" class="form-label">Question Type</label>
                        <select class="form-select" id="questionType" name="questionType">
                            <option value="multiple_choice" selected>Multiple Choice</option>
                            <!-- <option value="true_false">True / False</option> -->
                            <!-- Add other types later if needed -->
                        </select>
                    </div>

                    <hr>
                    <h6 class="card-subtitle mb-2 text-muted">Answers (for Multiple Choice)</h6>

                    <!-- Answer Options -->
                    <div id="answerOptionsContainer">
                        <!-- Default to 4 options, add more via JS later if needed -->
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                <!-- Radio button indicates the correct answer -->
                                <input class="form-check-input mt-0" type="radio" name="isCorrect" value="<?php echo $i; ?>" required aria-label="Mark option <?php echo $i; ?> as correct">
                            </div>
                            <!-- Answer text input, using array notation -->
                            <input type="text" class="form-control" name="answers[<?php echo $i; ?>]" placeholder="Answer Option <?php echo $i; ?>" required aria-label="Answer Option <?php echo $i; ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                    <!-- <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="addAnswerOption">Add Another Option</button> -->
                     <div class="form-text">Enter at least two answer options and select the radio button next to the single correct answer.</div>


                    <hr>

                    <button type="submit" class="btn btn-primary">Save Question</button>
                </form>
                 <!-- End Form -->

            </div> <!-- End Card Body -->
        </div> <!-- End Card -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>

<!-- Optional: Add JS here or in adminDashboard.js to handle adding more answer options dynamically -->
<!-- (JS code for adding options is commented out but provided as an example) -->