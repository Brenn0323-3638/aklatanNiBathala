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
    $_SESSION['question_error'] = "Invalid IDs provided for managing answers.";
    // Try redirecting back to quiz list if quizId is unknown, otherwise back to questions list if possible
    header('Location: ' . ($quizId ? 'manageQuestions.php?quiz_id='.$quizId : 'manageQuiz.php'));
    exit;
}

// --- Fetch Question Text and Existing Answers ---
$questionText = "Question ID: " . $questionId; // Default
$quizTitle = "Quiz ID: " . $quizId; // Default
$answers = [];
$errorMessage = '';
$successMessage = '';

try {
    $pdo = connect_db();
    if ($pdo) {
        // Fetch Question Text and Quiz Title
        $sqlQuestion = "SELECT q.question_text, z.title as quiz_title
                        FROM quiz_questions q
                        JOIN quizzes z ON q.quiz_id = z.quiz_id
                        WHERE q.question_id = :questionId AND q.quiz_id = :quizId"; // Check both IDs match
        $stmtQuestion = $pdo->prepare($sqlQuestion);
        $stmtQuestion->bindParam(':questionId', $questionId, PDO::PARAM_INT);
        $stmtQuestion->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtQuestion->execute();
        $questionData = $stmtQuestion->fetch(PDO::FETCH_ASSOC);

        if (!$questionData) {
            $_SESSION['question_error'] = "Question not found or does not belong to the specified quiz.";
            header('Location: manageQuestions.php?quiz_id=' . $quizId);
            exit;
        }
        $questionText = $questionData['question_text'];
        $quizTitle = $questionData['quiz_title'];
        $stmtQuestion->closeCursor();

        // Fetch Answers for this Question
        $sqlAnswers = "SELECT answer_id, answer_text, is_correct
                       FROM quiz_answers
                       WHERE question_id = :questionId
                       ORDER BY answer_id ASC"; // Order consistently
        $stmtAnswers = $pdo->prepare($sqlAnswers);
        $stmtAnswers->bindParam(':questionId', $questionId, PDO::PARAM_INT);
        $stmtAnswers->execute();
        $answers = $stmtAnswers->fetchAll(PDO::FETCH_ASSOC);

        if (empty($answers)) {
            // This shouldn't happen if created correctly, but handle it
             $errorMessage = "No answer options found for this question. Please add some (or check database).";
             // Maybe redirect back? Or allow adding from here? For now, show error.
        }

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch(PDOException $e) {
     $errorMessage = "Database error: " . $e->getMessage();
     error_log("Manage Answers Fetch Error (Question ID: {$questionId}): " . $e->getMessage());
}

// --- Handle Session Messages (from processManageAnswers) ---
if (isset($_SESSION['answer_success'])) {
    $successMessage = $_SESSION['answer_success'];
    unset($_SESSION['answer_success']);
}
if (isset($_SESSION['answer_error'])) {
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['answer_error'] : $_SESSION['answer_error'];
    unset($_SESSION['answer_error']);
}

?>

<!-- Main Content Area for Managing Answers -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Manage Answers</h3>
                    <p>For Quiz: <strong><?php echo htmlspecialchars($quizTitle); ?></strong></p>
                </div>
            </div>
             <div>
                 <a href="manageQuestions.php?quiz_id=<?php echo $quizId; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Questions</a>
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
                <?php echo $errorMessage; // Might contain HTML ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Question Display & Answer Form Card -->
        <div class="card">
            <div class="card-header">
                <strong>Question:</strong>
                <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($questionText)); ?></p>
            </div>
            <div class="card-body">
                <h5 class="card-title">Answer Options</h5>
                <p class="card-text"><small>Edit the text for each option and select the radio button for the single correct answer.</small></p>

                <?php if (!empty($answers)): ?>
                <!-- The Form submits all answers and the correct choice -->
                <form action="processManageAnswers.php" method="POST">
                    <!-- Hidden fields to pass IDs -->
                    <input type="hidden" name="questionId" value="<?php echo $questionId; ?>">
                    <input type="hidden" name="quizId" value="<?php echo $quizId; ?>"> <!-- For redirecting back -->

                    <!-- Loop through existing answers -->
                    <?php foreach ($answers as $answer): ?>
                        <div class="input-group mb-3">
                             <div class="input-group-text">
                                <input class="form-check-input mt-0"
                                       type="radio"
                                       name="isCorrect"
                                       value="<?php echo $answer['answer_id']; ?>"
                                       aria-label="Mark answer ID <?php echo $answer['answer_id']; ?> as correct"
                                       <?php echo $answer['is_correct'] ? 'checked' : ''; ?>
                                       required>
                             </div>
                             <!-- Input for editing answer text, name includes answer ID -->
                             <input type="text"
                                    class="form-control"
                                    name="answer_text[<?php echo $answer['answer_id']; ?>]"
                                    value="<?php echo htmlspecialchars($answer['answer_text']); ?>"
                                    aria-label="Answer text for ID <?php echo $answer['answer_id']; ?>"
                                    required>
                             <!-- Optional: Add delete button per answer? More complex processing -->
                             <!-- <button type="button" class="btn btn-outline-danger btn-sm">X</button> -->
                        </div>
                    <?php endforeach; ?>

                    <!-- Add New Answer Option (Optional Feature - requires JS and backend logic) -->
                    <!-- <div id="newAnswerOptionsContainer"></div>
                    <button type="button" id="addNewAnswerBtn" class="btn btn-sm btn-outline-secondary mb-3">Add New Answer Option</button> -->

                    <hr>
                    <button type="submit" class="btn btn-primary">Save Answer Changes</button>

                </form>
                 <!-- End Form -->
                 <?php endif; ?>

            </div> <!-- End Card Body -->
        </div> <!-- End Card -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>

<!-- Optional JS for adding new answer options dynamically -->