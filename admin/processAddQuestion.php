<?php
session_start(); // Start session for messages and auth check

// 1. --- Essential Includes and Setup ---
require_once '../includes/db.php'; // Your database connection script

// --- Authentication Check ---
if (!isset($_SESSION['adminId'])) {
    $_SESSION['login_error'] = "Please log in to manage quizzes.";
    header("Location: adminLogin.php");
    exit();
}

// --- Define Redirect Targets ---
$successRedirectTarget = 'manageQuestions.php'; // Default success redirect
$errorRedirectTarget = 'addQuestion.php';   // Default error redirect
$quizId = null; // Initialize quizId

// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve and Validate Input ---
    // Get Quiz ID from hidden field
    if (isset($_POST['quizId']) && filter_var($_POST['quizId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $quizId = (int)$_POST['quizId'];
        // Set specific redirect targets including quiz_id
        $successRedirectTarget .= '?quiz_id=' . $quizId;
        $errorRedirectTarget .= '?quiz_id=' . $quizId;
    } else {
        $_SESSION['quiz_error'] = "Invalid or missing Quiz ID when adding question.";
        header("Location: manageQuiz.php"); // Redirect to main quiz list if ID missing
        exit;
    }

    $questionText = trim($_POST['questionText'] ?? '');
    $questionType = trim($_POST['questionType'] ?? 'multiple_choice'); // Default if not sent
    $answers = $_POST['answers'] ?? []; // Get the array of answers
    $correctAnswerIndex = $_POST['isCorrect'] ?? null; // Get the index (value) of the selected radio button

    // --- Validation ---
    if (empty($questionText)) {
        $_SESSION['question_error'] = "Question Text cannot be empty.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }
    if ($questionType == 'multiple_choice') {
        if (empty($answers) || !is_array($answers) || count($answers) < 2) { // Ensure at least 2 answers for MC
            $_SESSION['question_error'] = "At least two answer options are required for Multiple Choice questions.";
            header("Location: " . $errorRedirectTarget);
            exit;
        }
        // Check if answers themselves are empty (remove empty ones)
        $filteredAnswers = array_filter($answers, function($a) { return trim($a) !== ''; });
        if (count($filteredAnswers) < 2) {
             $_SESSION['question_error'] = "At least two non-empty answer options are required.";
             header("Location: " . $errorRedirectTarget);
             exit;
        }
         // Ensure one correct answer is selected and its index exists in the submitted answers
        if ($correctAnswerIndex === null || !isset($answers[$correctAnswerIndex])) {
            $_SESSION['question_error'] = "You must select one correct answer for Multiple Choice questions.";
            header("Location: " . $errorRedirectTarget);
            exit;
        }
         // Ensure the selected correct answer text is not empty
        if (trim($answers[$correctAnswerIndex]) === '') {
            $_SESSION['question_error'] = "The selected correct answer cannot be empty.";
             header("Location: " . $errorRedirectTarget);
             exit;
        }
    }
    // Add validation for other question types later if needed


    // 4. --- Database Operation ---
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['question_error'] = "Database connection error.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }

    try {
        // Start Transaction - Essential for inserting into multiple tables
        $pdo->beginTransaction();

        // --- Insert into `quiz_questions` table ---
        $sqlQuestion = "INSERT INTO quiz_questions (quiz_id, question_text, question_type, order_index)
                        VALUES (:quiz_id, :question_text, :question_type, :order_index)";
        $stmtQuestion = $pdo->prepare($sqlQuestion);

        // Optional: Calculate next order index (simple approach)
        $sqlOrder = "SELECT MAX(order_index) as max_order FROM quiz_questions WHERE quiz_id = :quizId";
        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmtOrder->execute();
        $maxOrder = $stmtOrder->fetchColumn();
        $nextOrderIndex = ($maxOrder === false || $maxOrder === null) ? 0 : $maxOrder + 1;

        $stmtQuestion->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
        $stmtQuestion->bindParam(':question_text', $questionText, PDO::PARAM_STR);
        $stmtQuestion->bindParam(':question_type', $questionType, PDO::PARAM_STR);
        $stmtQuestion->bindParam(':order_index', $nextOrderIndex, PDO::PARAM_INT);

        if (!$stmtQuestion->execute()) {
             throw new Exception("Error inserting question record: " . implode(":", $stmtQuestion->errorInfo()));
        }

        $newQuestionId = $pdo->lastInsertId(); // Get the ID of the question just inserted

        // --- Insert into `quiz_answers` table (only for relevant types like multiple_choice) ---
        if ($questionType == 'multiple_choice' && !empty($filteredAnswers)) { // Use filtered non-empty answers
            $sqlAnswer = "INSERT INTO quiz_answers (question_id, answer_text, is_correct)
                          VALUES (:question_id, :answer_text, :is_correct)";
            $stmtAnswer = $pdo->prepare($sqlAnswer);

            foreach ($filteredAnswers as $index => $answerText) {
                // *** FIX: Trim the value and store it in a new variable FIRST ***
                $trimmedAnswerText = trim($answerText);
                // ****************************************************************

                $isCorrect = ($index == $correctAnswerIndex) ? 1 : 0;

                $stmtAnswer->bindParam(':question_id', $newQuestionId, PDO::PARAM_INT);
                // *** FIX: Bind the trimmed variable ***
                $stmtAnswer->bindParam(':answer_text', $trimmedAnswerText, PDO::PARAM_STR);
                // ************************************
                $stmtAnswer->bindParam(':is_correct', $isCorrect, PDO::PARAM_INT);

                if (!$stmtAnswer->execute()) {
                    // Use the original $answerText in the error message if needed for context
                    throw new Exception("Error inserting answer option '{$answerText}': " . implode(":", $stmtAnswer->errorInfo()));
                }
            }
        } // End if multiple choice

        // --- Commit Transaction ---
        $pdo->commit();

        // 5. --- Success Feedback ---
        $_SESSION['question_success'] = "New question added successfully to the quiz.";
        header('Location: ' . $successRedirectTarget); // Redirect back to the question list for this quiz
        exit;

    } catch (Exception $e) {
        // --- Rollback Transaction on Error ---
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // --- Error Feedback ---
        $_SESSION['question_error'] = "An error occurred while adding the question: " . $e->getMessage();
        error_log("Question Add Error (Quiz ID: {$quizId}): " . $e->getMessage());

        header('Location: ' . $errorRedirectTarget); // Redirect back to the add question form
        exit;
    }

} else {
    // Not a POST request
    $_SESSION['quiz_error'] = "Invalid access method.";
    header('Location: manageQuiz.php'); // Redirect to main quiz list
    exit;
}

?>