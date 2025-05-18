<?php


// 1. --- Essential Includes ---
require_once 'header.php'; // Includes session start if not already active
require_once 'includes/db.php'; // Include DB connection

// 2. --- Define Redirect Targets ---
$loginPage = 'login.php';
$quizListPage = 'quizzes.php';

// 3. --- Authentication Check ---
// Quizzes can only be submitted by logged-in users to record attempts
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Please log in to take and submit quizzes.";
    header('Location: ' . $loginPage);
    exit();
}
$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// 4. --- Initialize Variables ---
$quiz = null;           // To store quiz details
$score = 0;             // User's calculated score
$totalQuestions = 0;    // Total scorable questions in the quiz
$errorMessage = '';     // To store errors for display
$quizId = null;
$submittedAnswers = [];

// 5. --- Validate Request Method ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Silently redirect GET requests back to quiz list
    header('Location: ' . $quizListPage);
    exit;
}

// 6. --- Get and Validate Input ---
$quizId = filter_input(INPUT_POST, 'quizId', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$submittedAnswers = $_POST['answers'] ?? []; // $_POST['answers'] should be like [question_id => answer_id]

if ($quizId === null || $quizId === false) {
    $errorMessage = "Invalid Quiz ID submitted.";
} elseif (empty($submittedAnswers) || !is_array($submittedAnswers)) {
    $errorMessage = "No answers were submitted or answers were in an invalid format.";
}

// 7. --- Process Quiz Submission (Only if Initial Validation Passed) ---
if (empty($errorMessage)) {
    try {
        $pdo = connect_db(); // Uses globals from your db.php

        // Fetch Quiz Title (ensure it exists)
        $sqlQuiz = "SELECT quiz_id, title FROM quizzes WHERE quiz_id = :quizId";
        $stmtQuiz = $pdo->prepare($sqlQuiz);
        $stmtQuiz->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtQuiz->execute();
        $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);
        $stmtQuiz->closeCursor();

        if (!$quiz) {
            // If quiz doesn't exist (maybe deleted after user started?)
            throw new Exception("The quiz you submitted could not be found.");
        }

        // Fetch Correct Answer IDs for this Quiz
        $correctAnswers = []; // Key: question_id, Value: correct_answer_id
        $sqlCorrect = "SELECT qa.question_id, qa.answer_id
                       FROM quiz_answers qa
                       JOIN quiz_questions q ON qa.question_id = q.question_id
                       WHERE q.quiz_id = :quizId AND qa.is_correct = 1";
        $stmtCorrect = $pdo->prepare($sqlCorrect);
        $stmtCorrect->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtCorrect->execute();
        $correctResults = $stmtCorrect->fetchAll(PDO::FETCH_ASSOC);
        $stmtCorrect->closeCursor();

        // Populate the lookup array and count questions with defined correct answers
        foreach ($correctResults as $row) {
            $correctAnswers[$row['question_id']] = $row['answer_id'];
        }
        $totalQuestions = count($correctAnswers); // Number of questions with a defined correct answer

        // Calculate Score
        if ($totalQuestions > 0) {
            foreach ($submittedAnswers as $questionId => $submittedAnswerId) {
                // Check if the submitted question ID is valid and has a correct answer defined
                if (isset($correctAnswers[$questionId])) {
                    // Convert submitted answer ID to integer for safe comparison
                    $submittedAnswerIdInt = filter_var($submittedAnswerId, FILTER_VALIDATE_INT);
                    if ($submittedAnswerIdInt !== false && $submittedAnswerIdInt === $correctAnswers[$questionId]) {
                        $score++;
                    }
                } else {
                    // Log if user submitted answer for a question not in our $correctAnswers map
                     error_log("Quiz Submit Warning: Submitted answer for Question ID {$questionId} which has no defined correct answer in Quiz ID {$quizId}.");
                }
            }

            // Save the Attempt to Database
            $sqlAttempt = "INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, attempt_timestamp)
                           VALUES (:user_id, :quiz_id, :score, :total_questions, NOW())";
            $stmtAttempt = $pdo->prepare($sqlAttempt);
            $stmtAttempt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtAttempt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmtAttempt->bindParam(':score', $score, PDO::PARAM_INT);
            $stmtAttempt->bindParam(':total_questions', $totalQuestions, PDO::PARAM_INT);

            if (!$stmtAttempt->execute()) {
                 // Log the failure but don't necessarily stop showing the score
                 error_log("Failed to save quiz attempt for User ID: {$userId}, Quiz ID: {$quizId}. Score: {$score}/{$totalQuestions}. Error: " . implode(":", $stmtAttempt->errorInfo()));
                 $errorMessage .= ($errorMessage ? "<br>" : "") . "Note: Your attempt could not be saved, but the score is shown below."; // Append warning
            }

        } else {
            // Quiz exists but has no scorable questions
            $errorMessage = "This quiz has no questions with defined correct answers, so it cannot be scored.";
            // Set totalQuestions based on submission if needed for display consistency
            $totalQuestions = count($submittedAnswers);
        }

    } catch (PDOException $e) {
        $errorMessage = "Database error while processing your answers. Please try again.";
        error_log("Submit Quiz Answers PDO Error (Quiz ID: {$quizId}, User ID: {$userId}): " . $e->getMessage());
    } catch (Exception $e) {
        $errorMessage = "An unexpected error occurred: " . $e->getMessage();
        error_log("Submit Quiz Answers General Error (Quiz ID: {$quizId}, User ID: {$userId}): " . $e->getMessage());
    }
} // End if initial validation passed

?>

<!-- Page Title Header -->
<div class="text-center my-4">
    <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #2e3a59;">Quiz Results</h1>
    <?php if ($quiz): ?>
        <p class="lead text-muted">Results for: <strong><?php echo htmlspecialchars($quiz['title']); ?></strong></p>
    <?php endif; ?>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="content-section text-center">

                <?php // --- Display Logic --- ?>

                <?php if (!empty($errorMessage) && !$quiz): // Fatal error before quiz loaded ?>
                     <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <p><?php echo htmlspecialchars($errorMessage); ?></p>
                        <hr>
                        <a href="<?php echo $quizListPage; ?>" class="btn btn-secondary">Back to Quiz List</a>
                    </div>

                <?php elseif ($quiz && $totalQuestions > 0): // Score display (might have non-fatal $errorMessage too) ?>
                    <?php if (!empty($errorMessage)): // Display non-fatal error first ?>
                         <div class="alert alert-warning" role="alert">
                           <?php echo htmlspecialchars($errorMessage); ?>
                         </div>
                    <?php endif; ?>
                    <h3 class="mb-3">Your Score:</h3>
                    <p class="display-2 fw-bold text-success">
                        <?php echo $score; ?> / <?php echo $totalQuestions; ?>
                    </p>
                    <p class="fs-4 mb-4">
                        (<?php echo round(($score / $totalQuestions) * 100); ?>%)
                    </p>
                    <a href="<?php echo $quizListPage; ?>" class="btn btn-primary me-2">Back to Quiz List</a>
                    <a href="takeQuiz.php?quiz_id=<?php echo $quizId; ?>" class="btn btn-outline-secondary">Try Again</a>

                <?php elseif ($quiz && $totalQuestions === 0): // Quiz exists but no scorable questions ?>
                     <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Quiz Submitted</h4>
                        <p><?php echo !empty($errorMessage) ? htmlspecialchars($errorMessage) : 'This quiz currently has no questions defined for scoring.'; ?></p>
                        <hr>
                        <a href="<?php echo $quizListPage; ?>" class="btn btn-secondary">Back to Quiz List</a>
                     </div>

                 <?php else: // Catch-all for other unexpected states ?>
                    <div class="alert alert-warning" role="alert">
                        Could not display results or process submission. <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                    <a href="<?php echo $quizListPage; ?>" class="btn btn-secondary">Back to Quiz List</a>
                <?php endif; ?>

            </div><!-- /.content-section -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container -->

<?php require_once 'footer.php'; ?>