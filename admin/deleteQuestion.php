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

// --- Get and Validate IDs ---
$questionId = null;
$quizId = null; // Need this to redirect back correctly

if (isset($_GET['question_id']) && filter_var($_GET['question_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $questionId = (int)$_GET['question_id'];
}
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
}

// Redirect if IDs are invalid or missing
if ($questionId === null || $quizId === null) {
    $_SESSION['question_error'] = "Invalid IDs provided for deleting question.";
    header('Location: ' . ($quizId ? 'manageQuestions.php?quiz_id='.$quizId : 'manageQuiz.php'));
    exit;
}

// Define redirect target (back to the question list for this quiz)
$redirectTarget = 'manageQuestions.php?quiz_id=' . $quizId;


// 3. --- Database Operation ---
$pdo = connect_db();
if (!$pdo) {
    $_SESSION['question_error'] = "Database connection error during deletion.";
    header('Location: ' . $redirectTarget);
    exit;
}

try {
    // --- Execute Deletion ---
    // Relies on ON DELETE CASCADE set on quiz_answers table's foreign key.
    $sqlDelete = "DELETE FROM quiz_questions WHERE question_id = :questionId AND quiz_id = :quizId"; // Match both IDs
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->bindParam(':questionId', $questionId, PDO::PARAM_INT);
     $stmtDelete->bindParam(':quizId', $quizId, PDO::PARAM_INT); // Ensure deleting from correct quiz

    if (!$stmtDelete->execute()) {
        throw new Exception("Error deleting question record: " . implode(":", $stmtDelete->errorInfo()));
    }

    $deletedCount = $stmtDelete->rowCount(); // Check if a row was actually deleted

    // --- Success Feedback ---
    if ($deletedCount > 0) {
         $_SESSION['question_success'] = "Question (ID: {$questionId}) and its associated answers were successfully deleted.";
    } else {
         // Question ID was valid but not found during delete (maybe already deleted or wrong quizId?)
         $_SESSION['question_error'] = "Question with ID {$questionId} was not found in quiz ID {$quizId} or already deleted.";
    }
    header('Location: ' . $redirectTarget);
    exit;

} catch (Exception $e) {
    // --- Error Feedback ---
    $_SESSION['question_error'] = "An error occurred during question deletion: " . $e->getMessage();
    error_log("Question Delete Error (ID: {$questionId}, QuizID: {$quizId}): " . $e->getMessage());
    header('Location: ' . $redirectTarget);
    exit;
}

?>