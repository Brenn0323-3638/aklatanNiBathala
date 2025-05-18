<?php

require_once 'header.php'; // Include the common user header
require_once 'includes/db.php'; // Include DB connection

// --- 1. Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['login_error'] = "Please log in to take quizzes.";
    header('Location: login.php');
    exit();
}
$userId = $_SESSION['user_id'];

// --- 2. Initialize Variables ---
$quiz = null;
$questions = [];
$attempts = [];
$errorMessage = '';
$quizId = null;

// --- 3. Get and Validate Quiz ID ---
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
} else {
    $errorMessage = "Invalid Quiz ID specified.";
}

// --- 4. Fetch Data (Only if Quiz ID is valid) ---
if ($quizId && empty($errorMessage)) {
    try {
        $pdo = connect_db();

        if ($pdo) {
            // --- Fetch Quiz Details (Must be active) ---
            $sqlQuizCheck = "SELECT quiz_id, title, description FROM quizzes WHERE quiz_id = :quizId AND is_active = 1";
            $stmtQuizCheck = $pdo->prepare($sqlQuizCheck);
            $stmtQuizCheck->bindParam(':quizId', $quizId, PDO::PARAM_INT);
            $stmtQuizCheck->execute();
            $quiz = $stmtQuizCheck->fetch(PDO::FETCH_ASSOC);
            $stmtQuizCheck->closeCursor();

            if (!$quiz) {
                $errorMessage = "Quiz not found or is not currently active.";
            } else {
                // --- Fetch Questions and Answers ---
                $sqlQA = "SELECT q.question_id, q.question_text, q.question_type, a.answer_id, a.answer_text
                          FROM quiz_questions q
                          LEFT JOIN quiz_answers a ON q.question_id = a.question_id
                          WHERE q.quiz_id = :quizId
                          ORDER BY q.order_index ASC, q.question_id ASC, RAND()";
                $stmtQA = $pdo->prepare($sqlQA);
                $stmtQA->bindParam(':quizId', $quizId, PDO::PARAM_INT);
                $stmtQA->execute();
                $results = $stmtQA->fetchAll(PDO::FETCH_ASSOC);
                $stmtQA->closeCursor();

                if ($results) {
                     $quizData = [];
                    foreach ($results as $row) {
                        $qId = $row['question_id'];
                        if (!isset($quizData[$qId])) {
                            $quizData[$qId] = [
                                'question_id' => $qId,
                                'question_text' => $row['question_text'],
                                'question_type' => $row['question_type'],
                                'answers' => []
                            ];
                        }
                        if ($row['answer_id']) {
                            $quizData[$qId]['answers'][] = [
                                'answer_id' => $row['answer_id'],
                                'answer_text' => $row['answer_text']
                            ];
                        }
                    }
                    $questions = array_values($quizData);
                }
                 // --- Fetch User's Past Attempts ---
                 $sqlAttempts = "SELECT attempt_id, score, total_questions, attempt_timestamp
                                 FROM quiz_attempts
                                 WHERE user_id = :userId AND quiz_id = :quizId
                                 ORDER BY attempt_timestamp DESC";
                 $stmtAttempts = $pdo->prepare($sqlAttempts);
                 $stmtAttempts->bindParam(':userId', $userId, PDO::PARAM_INT);
                 $stmtAttempts->bindParam(':quizId', $quizId, PDO::PARAM_INT);
                 $stmtAttempts->execute();
                 $attempts = $stmtAttempts->fetchAll(PDO::FETCH_ASSOC);
                 $stmtAttempts->closeCursor();

            } // End if quiz found
        } else { $errorMessage = "Database connection failed."; }
    } catch (PDOException | Exception $e) {
        $errorMessage = "An error occurred while loading the quiz details.";
        error_log("Take Quiz Load/Attempt Fetch Error (Quiz ID: {$quizId}, User ID: {$userId}): " . $e->getMessage());
    }
} // End if quizId is valid

?>

<!-- Page Title -->
<div class="text-center my-4">
    <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #2e3a59;">
        <?php echo $quiz ? htmlspecialchars($quiz['title']) : 'Take Quiz'; ?>
    </h1>
    <?php if ($quiz && !empty($quiz['description'])): ?>
        <p class="lead text-muted"><?php echo htmlspecialchars($quiz['description']); ?></p>
    <?php endif; ?>
</div>

<!-- Display Error Message if needed -->
<?php if (!empty($errorMessage)): ?>
    <div class="container">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
            <a href="quizzes.php" class="alert-link">Return to Quiz List</a>.
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>


<?php // --- Main Content Area (Only if no fatal errors and quiz loaded) --- ?>
<?php if ($quiz && empty($errorMessage)): ?>
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Display Past Attempts Section -->
            <div class="content-section mb-4">
                <h4 class="mb-3"><i class="fas fa-history me-2"></i>Your Previous Attempts</h4>
                <?php if (!empty($attempts)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                           <!-- ... table head/body for attempts ... -->
                           <thead><tr><th>Date</th><th>Score</th><th>Percentage</th></tr></thead>
                           <tbody>
                            <?php foreach ($attempts as $attempt): ?>
                                <?php $percentage = ($attempt['total_questions'] > 0) ? round(($attempt['score'] / $attempt['total_questions']) * 100) : 0; ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($attempt['attempt_timestamp'])); ?></td>
                                    <td><?php echo $attempt['score']; ?> / <?php echo $attempt['total_questions']; ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">You have not attempted this quiz yet.</p>
                <?php endif; ?>
            </div>
            <!-- End Past Attempts Section -->


            <?php if (!empty($questions)): // Show form only if questions exist ?>
                <div class="content-section">
                     <h4 class="mb-3"><i class="fas fa-edit me-2"></i>Take the Quiz</h4>
                    <form action="submitQuizAnswers.php" method="POST">
                        <!-- Hidden input for quiz ID -->
                        <input type="hidden" name="quizId" value="<?php echo $quizId; ?>">

                        <?php foreach ($questions as $index => $question): ?>
                            <div class="mb-4 pb-3 border-bottom quiz-question-block">
                                <p class="fw-bold fs-5 mb-2">Question <?php echo $index + 1; ?>:</p>
                                <p class="mb-3 question-text"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>

                                <?php // Display answers based on question type ?>
                                <?php if ($question['question_type'] == 'multiple_choice' && !empty($question['answers'])): ?>
                                    <div class="answer-options ms-3">
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="answers[<?php echo $question['question_id']; ?>]" id="answer_<?php echo $answer['answer_id']; ?>" value="<?php echo $answer['answer_id']; ?>" required>
                                                <label class="form-check-label" for="answer_<?php echo $answer['answer_id']; ?>">
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ($question['question_type'] == 'true_false'): ?>
                                     <div class="answer-options ms-3">
                                         <div class="form-check mb-2">
                                             <input class="form-check-input" type="radio" name="answers[<?php echo $question['question_id']; ?>]" id="q<?php echo $question['question_id']; ?>_true" value="True" required>
                                             <label class="form-check-label" for="q<?php echo $question['question_id']; ?>_true">True</label>
                                         </div>
                                          <div class="form-check mb-2">
                                             <input class="form-check-input" type="radio" name="answers[<?php echo $question['question_id']; ?>]" id="q<?php echo $question['question_id']; ?>_false" value="False" required>
                                             <label class="form-check-label" for="q<?php echo $question['question_id']; ?>_false">False</label>
                                         </div>
                                     </div>
                                <?php else: ?>
                                    <p class="ms-3"><em>(This question type is not yet supported for answering.)</em></p>
                                <?php endif; ?>

                            </div><!-- /.quiz-question-block -->
                        <?php endforeach; ?>

                        <!-- *** MODIFIED: Added Exit Button and Submit Button Wrapper *** -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="quizzes.php"
                               class="btn btn-secondary"
                               onclick="return confirm('Are you sure you want to exit? Your current answers will not be saved.');">
                                <i class="fas fa-times me-1"></i> Exit Quiz
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5">Submit Answers</button>
                        </div>
                         <!-- ********************************************************* -->

                    </form>
                </div><!-- /.content-section -->
            <?php elseif(empty($errorMessage)): // If quiz loaded but $questions is empty ?>
                <div class="content-section text-center">
                     <p class="lead">This quiz is ready, but no questions have been added yet!</p>
                     <a href="quizzes.php" class="btn btn-primary">Back to Quiz List</a>
                </div>
            <?php endif; ?>

        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container -->
<?php endif; // End main content display block ?>


<?php require_once 'footer.php'; ?>
