<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Get and Validate IDs from URL ---
$questionId = null;
$quizId = null; // Keep quizId for navigation back

if (isset($_GET['question_id']) && filter_var($_GET['question_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $questionId = (int)$_GET['question_id'];
}
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
}

// Redirect if IDs are invalid or missing
if ($questionId === null || $quizId === null) {
    $_SESSION['question_error'] = "Invalid IDs provided for editing question.";
    header('Location: ' . ($quizId ? 'manageQuestions.php?quiz_id='.$quizId : 'manageQuiz.php'));
    exit;
}

// --- Fetch Question Data ---
$questionData = null;
$quizTitle = "Quiz ID: " . $quizId; // Default
$errorMessage = '';

try {
    $pdo = connect_db();
    if ($pdo) {
        // Fetch Question Details & Quiz Title
        $sql = "SELECT q.question_id, q.quiz_id, q.question_text, q.question_type, z.title as quiz_title
                FROM quiz_questions q
                JOIN quizzes z ON q.quiz_id = z.quiz_id
                WHERE q.question_id = :questionId AND q.quiz_id = :quizId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':questionId', $questionId, PDO::PARAM_INT);
        $stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmt->execute();
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$questionData) {
            $_SESSION['question_error'] = "Question not found or does not belong to the specified quiz.";
            header('Location: manageQuestions.php?quiz_id=' . $quizId);
            exit;
        }
        $quizTitle = $questionData['quiz_title']; // Use fetched title

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch(PDOException $e) {
    $errorMessage = "Database error fetching question: " . $e->getMessage();
    error_log("Edit Question Fetch Error (ID: {$questionId}): " . $e->getMessage());
}

// --- Handle Session Error Messages (from processEditQuestion) ---
if (isset($_SESSION['question_error'])) {
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['question_error'] : $_SESSION['question_error'];
    unset($_SESSION['question_error']);
}

?>

<!-- Main Content Area for Editing Question -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Edit Question</h3>
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
                <?php echo $errorMessage; // Might contain HTML ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($questionData && empty($errorMessage)): // Only show form if data loaded ?>
        <!-- Edit Question Form Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Question Details</h5>

                <!-- The Form submits to processEditQuestion.php -->
                <form action="processEditQuestion.php" method="POST">
                    <!-- Hidden fields for IDs -->
                    <input type="hidden" name="questionId" value="<?php echo $questionData['question_id']; ?>">
                    <input type="hidden" name="quizId" value="<?php echo $questionData['quiz_id']; ?>">

                    <div class="mb-3">
                        <label for="questionText" class="form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="questionText" name="questionText" rows="4" required><?php echo htmlspecialchars($questionData['question_text']); ?></textarea>
                    </div>

                    <!-- Question Type (Consider making this editable later if needed) -->
                    <div class="mb-3">
                        <label for="questionType" class="form-label">Question Type</label>
                        <input type="text" class="form-control" id="questionTypeDisplay" value="<?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $questionData['question_type']))); ?>" readonly disabled>
                        <!-- If you allow changing type, use a select dropdown and handle consequences (like deleting answers) in backend -->
                         <input type="hidden" name="questionType" value="<?php echo htmlspecialchars($questionData['question_type']); ?>"> <!-- Keep submitting original type for now -->
                    </div>

                    <!-- NOTE: Editing answer options happens on manageAnswers.php for multiple choice -->
                    <?php if ($questionData['question_type'] == 'multiple_choice'): ?>
                        <div class="mb-3">
                             <a href="manageAnswers.php?question_id=<?php echo $questionData['question_id']; ?>&quiz_id=<?php echo $questionData['quiz_id']; ?>" class="btn btn-outline-info">
                                <i class="fas fa-check-square me-1"></i> Manage Answer Options for this Question
                             </a>
                        </div>
                    <?php endif; ?>

                    <hr>
                    <button type="submit" class="btn btn-primary">Save Question Changes</button>
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