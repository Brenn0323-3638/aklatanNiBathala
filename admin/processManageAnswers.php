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
// We need both questionId and quizId for redirecting
$questionId = null;
$quizId = null;
$errorRedirectTarget = 'manageQuiz.php'; // Fallback redirect
$successRedirectTarget = 'manageQuiz.php'; // Fallback redirect


// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve and Validate Input ---
    // Get IDs from hidden fields
    if (isset($_POST['questionId']) && filter_var($_POST['questionId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $questionId = (int)$_POST['questionId'];
    } else {
        $_SESSION['answer_error'] = "Invalid or missing Question ID.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }
    if (isset($_POST['quizId']) && filter_var($_POST['quizId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $quizId = (int)$_POST['quizId'];
        // Now set specific redirect targets
        $errorRedirectTarget = 'manageAnswers.php?question_id=' . $questionId . '&quiz_id=' . $quizId;
        $successRedirectTarget = 'manageQuestions.php?quiz_id=' . $quizId; // Go back to question list on success
    } else {
         $_SESSION['answer_error'] = "Invalid or missing Quiz ID.";
         // If quizId missing, can't redirect to specific pages, go to main list
         header("Location: manageQuiz.php");
         exit;
    }

    // Get the ID of the selected correct answer from the radio button
    $correctAnswerId = $_POST['isCorrect'] ?? null;
    // Get the array of submitted answer texts (key is answer_id)
    $answerTexts = $_POST['answer_text'] ?? [];

    // --- Validation ---
    if ($correctAnswerId === null || !filter_var($correctAnswerId, FILTER_VALIDATE_INT)) {
         $_SESSION['answer_error'] = "You must select one correct answer.";
         header("Location: " . $errorRedirectTarget);
         exit;
    }
     if (empty($answerTexts) || !is_array($answerTexts)) {
         $_SESSION['answer_error'] = "Answer text data is missing or invalid.";
         header("Location: " . $errorRedirectTarget);
         exit;
    }
    // Check if the selected correct answer ID exists in the submitted texts
     if (!isset($answerTexts[$correctAnswerId])) {
         $_SESSION['answer_error'] = "The selected correct answer ID does not match submitted answers.";
         header("Location: " . $errorRedirectTarget);
         exit;
     }


    // 4. --- Database Operation ---
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['answer_error'] = "Database connection error.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }

    try {
        // Start Transaction - Essential for multiple updates
        $pdo->beginTransaction();

        // Loop through each submitted answer text
        foreach ($answerTexts as $answerId => $newText) {
            // Validate answer ID within the loop
             if (!filter_var($answerId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                 throw new Exception("Invalid Answer ID format encountered: " . htmlspecialchars($answerId));
             }

            // Trim and validate the new answer text
            $trimmedText = trim($newText);
            if (empty($trimmedText)) {
                 throw new Exception("Answer text for option ID " . (int)$answerId . " cannot be empty.");
            }

            // Determine if this is the correct answer
            $isCorrectFlag = ($answerId == $correctAnswerId) ? 1 : 0;

            // Prepare the UPDATE statement
            $sqlAnswerUpdate = "UPDATE quiz_answers SET
                                    answer_text = :answer_text,
                                    is_correct = :is_correct
                                WHERE answer_id = :answer_id AND question_id = :question_id"; // Ensure we only update answers for THIS question
            $stmtAnswer = $pdo->prepare($sqlAnswerUpdate);

            // Bind parameters
            $stmtAnswer->bindParam(':answer_text', $trimmedText, PDO::PARAM_STR);
            $stmtAnswer->bindParam(':is_correct', $isCorrectFlag, PDO::PARAM_INT);
            $stmtAnswer->bindParam(':answer_id', $answerId, PDO::PARAM_INT);
            $stmtAnswer->bindParam(':question_id', $questionId, PDO::PARAM_INT); // Safety check

            // Execute the update for this answer
            if (!$stmtAnswer->execute()) {
                throw new Exception("Error updating answer ID {$answerId}: " . implode(":", $stmtAnswer->errorInfo()));
            }
        } // End foreach loop

        // --- Commit Transaction ---
        $pdo->commit();

        // 5. --- Success Feedback ---
        $_SESSION['answer_success'] = "Answer options updated successfully.";
        header('Location: ' . $successRedirectTarget); // Redirect back to the question list for this quiz
        exit;

    } catch (Exception $e) {
        // --- Rollback Transaction on Error ---
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // --- Error Feedback ---
        $_SESSION['answer_error'] = "An error occurred while updating answers: " . $e->getMessage();
        error_log("Manage Answers Update Error (Question ID: {$questionId}): " . $e->getMessage());

        header('Location: ' . $errorRedirectTarget); // Redirect back to the manage answers form
        exit;
    }

} else {
    // Not a POST request
    $_SESSION['quiz_error'] = "Invalid access method."; // Use quiz_error if redirecting to quiz list
    header('Location: manageQuiz.php'); // Redirect to main quiz list
    exit;
}

?>