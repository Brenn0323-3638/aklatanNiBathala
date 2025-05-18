<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Get and Validate Quiz ID from URL ---
$quizId = null;
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
} else {
    // Use a consistent session key for quiz-related errors if redirecting
    $_SESSION['quiz_error'] = "Invalid Quiz ID provided.";
    header('Location: manageQuiz.php'); // Redirect back to quiz list
    exit;
}

// --- Fetch Quiz Details (Title) and its Questions ---
$quizTitle = "Quiz ID: " . $quizId; // Default title
$questions = []; // Initialize questions array
$errorMessage = '';
$successMessage = '';

try {
    $pdo = connect_db();
    if ($pdo) {
        // Fetch Quiz Title first to ensure the quiz exists
        $sqlQuiz = "SELECT title FROM quizzes WHERE quiz_id = :quizId";
        $stmtQuiz = $pdo->prepare($sqlQuiz);
        $stmtQuiz->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtQuiz->execute();
        $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

        if (!$quiz) {
            // Quiz ID from URL doesn't exist in the database
            $_SESSION['quiz_error'] = "Quiz not found for ID: " . $quizId;
            header('Location: manageQuiz.php');
            exit;
        }
        $quizTitle = $quiz['title']; // Use the actual quiz title
        $stmtQuiz->closeCursor(); // Good practice

        // Now fetch Questions for this Quiz
        // Assuming you have tables `quiz_questions` linked by `quiz_id`
        $sqlQuestions = "SELECT question_id, question_text, question_type, order_index
                         FROM quiz_questions
                         WHERE quiz_id = :quizId
                         ORDER BY order_index ASC, question_id ASC"; // Order by specified order or ID
        $stmtQuestions = $pdo->prepare($sqlQuestions);
        $stmtQuestions->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtQuestions->execute();
        $questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException $e) {
     // Check if the error is "table not found" for questions
    if (strpos($e->getMessage(), 'Base table or view not found') !== false && strpos($e->getMessage(), 'quiz_questions') !== false) {
         // Provide helpful message if table is missing
         $errorMessage = "The 'quiz_questions' table does not seem to exist. Please ensure it has been created in the database.";
    } else {
        $errorMessage = "Database error fetching data: " . $e->getMessage();
    }
    error_log("Manage Questions Error (Quiz ID: {$quizId}): " . $e->getMessage());
}

// --- Handle Session Messages (Use distinct keys for question messages) ---
// These messages would be set by addQuestion, editQuestion, deleteQuestion processes
if (isset($_SESSION['question_success'])) {
    $successMessage = $_SESSION['question_success'];
    unset($_SESSION['question_success']);
}
if (isset($_SESSION['question_error'])) {
    // Append or set error message
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['question_error'] : $_SESSION['question_error'];
    unset($_SESSION['question_error']);
}

?>

<!-- Main Content Area for Managing Questions -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Manage Questions</h3>
                    <p>For Quiz: <strong><?php echo htmlspecialchars($quizTitle); ?></strong></p>
                </div>
            </div>
             <div>
                 <!-- Link to add a question *for this specific quiz* -->
                 <a href="addQuestion.php?quiz_id=<?php echo $quizId; ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Question</a>
                 <a href="manageQuiz.php" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left me-1"></i> Back to Quizzes</a>
            </div>
        </div>

        <!-- Display Session Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; // Might contain HTML breaks if tables missing ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Question Listing -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Existing Questions</h5>
                 <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th> <!-- Order/ID -->
                                <th>Question Text</th>
                                <th style="width: 15%;">Type</th>
                                <th style="width: 25%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($questions)): ?>
                                <?php foreach ($questions as $index => $question): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td> <!-- Display simple numbering based on loop index -->
                                        <td><?php echo nl2br(htmlspecialchars(substr($question['question_text'], 0, 150))) . (strlen($question['question_text']) > 150 ? '...' : ''); // Show excerpt, respect newlines ?></td>
                                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $question['question_type']))); // Format type nicely (e.g., 'multiple_choice' -> 'Multiple Choice') ?></td>
                                        <td>
                                            <?php // Conditionally show 'Manage Answers' button ?>
                                            <?php if ($question['question_type'] == 'multiple_choice'): ?>
                                                <a href="manageAnswers.php?question_id=<?php echo $question['question_id']; ?>&quiz_id=<?php echo $quizId; ?>" class="btn btn-sm btn-success" title="Manage Answers">
                                                    <i class="fas fa-check-square"></i> <span class="d-none d-md-inline">Answers</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php // Edit link passes both IDs ?>
                                            <a href="editQuestion.php?question_id=<?php echo $question['question_id']; ?>&quiz_id=<?php echo $quizId; ?>" class="btn btn-sm btn-warning" title="Edit Question">
                                                <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                                            </a>
                                            <?php // Delete link passes both IDs ?>
                                            <a href="deleteQuestion.php?question_id=<?php echo $question['question_id']; ?>&quiz_id=<?php echo $quizId; ?>" class="btn btn-sm btn-danger" title="Delete Question" onclick="return confirm('Are you sure you want to delete this question and its answers? This cannot be undone.');">
                                                <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No questions found for this quiz yet. Click "Add New Question" to create one.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                 </div> <!-- End table-responsive -->
            </div> <!-- End card-body -->
        </div> <!-- End card -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>