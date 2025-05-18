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

// 2. --- Get and Validate Quiz ID ---
$quizId = null;
if (isset($_GET['quiz_id']) && filter_var($_GET['quiz_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $quizId = (int)$_GET['quiz_id'];
} else {
    $_SESSION['quiz_error'] = "Invalid Quiz ID provided for deletion.";
    header('Location: manageQuiz.php');
    exit;
}

// 3. --- Database Operation ---
$pdo = connect_db();
if (!$pdo) {
    $_SESSION['quiz_error'] = "Database connection error during deletion.";
    header('Location: manageQuiz.php');
    exit;
}

$quizTitle = "Quiz ID " . $quizId; // Default title for messages

try {
    // --- Optional: Fetch title first for better success message ---
    $sqlFetch = "SELECT title FROM quizzes WHERE quiz_id = :quizId";
    $stmtFetch = $pdo->prepare($sqlFetch);
    $stmtFetch->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtFetch->execute();
    $quiz = $stmtFetch->fetch(PDO::FETCH_ASSOC);
    if ($quiz) {
        $quizTitle = $quiz['title'];
    }
    $stmtFetch->closeCursor();

    // --- Execute Deletion ---
    // Assuming ON DELETE CASCADE is set for quiz_questions and quiz_answers
    // Otherwise, you would need to delete answers, then questions, then the quiz within a transaction.
    $sqlDelete = "DELETE FROM quizzes WHERE quiz_id = :quizId";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->bindParam(':quizId', $quizId, PDO::PARAM_INT);

    if (!$stmtDelete->execute()) {
        throw new Exception("Error deleting quiz record: " . implode(":", $stmtDelete->errorInfo()));
    }

    $deletedCount = $stmtDelete->rowCount(); // Check if a row was actually deleted

    // --- Success Feedback ---
    if ($deletedCount > 0) {
         $_SESSION['quiz_success'] = "Quiz '" . htmlspecialchars($quizTitle) . "' and its associated data were successfully deleted.";
    } else {
         // Quiz ID was valid but not found during delete (maybe already deleted?)
         $_SESSION['quiz_error'] = "Quiz with ID {$quizId} was not found or already deleted.";
    }
    header('Location: manageQuiz.php');
    exit;

} catch (Exception $e) {
    // --- Error Feedback ---
    $_SESSION['quiz_error'] = "An error occurred during deletion: " . $e->getMessage();
    error_log("Quiz Delete Error (ID: {$quizId}): " . $e->getMessage());
    header('Location: manageQuiz.php');
    exit;
}

?>