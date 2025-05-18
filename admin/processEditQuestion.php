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
$questionId = null;
$quizId = null;
$errorRedirectTarget = 'manageQuiz.php'; // Fallback
$successRedirectTarget = 'manageQuiz.php'; // Fallback

// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve and Validate Input ---
    if (isset($_POST['questionId']) && filter_var($_POST['questionId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $questionId = (int)$_POST['questionId'];
    } else {
        $_SESSION['question_error'] = "Invalid or missing Question ID for update.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }
     if (isset($_POST['quizId']) && filter_var($_POST['quizId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $quizId = (int)$_POST['quizId'];
        // Set specific redirect targets
        $errorRedirectTarget = 'editQuestion.php?question_id=' . $questionId . '&quiz_id=' . $quizId;
        $successRedirectTarget = 'manageQuestions.php?quiz_id=' . $quizId; // Go back to question list on success
    } else {
         $_SESSION['question_error'] = "Invalid or missing Quiz ID for update.";
         header("Location: manageQuiz.php"); // Redirect to main list if quizId missing
         exit;
    }

    $questionText = trim($_POST['questionText'] ?? '');
    // $questionType = trim($_POST['questionType'] ?? ''); // Get type if allowing edits later

    // --- Basic Validation ---
    if (empty($questionText)) {
        $_SESSION['question_error'] = "Question Text cannot be empty.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }
    // Add more validation if needed


    // 4. --- Database Operation ---
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['question_error'] = "Database connection error.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }

    try {
        // Update only the question text for now (add updated_at)
        $sql = "UPDATE quiz_questions SET
                    question_text = :question_text,
                    updated_at = NOW()
                    -- Add question_type = :question_type etc. if editing those later
                WHERE question_id = :questionId AND quiz_id = :quizId"; // Match both IDs

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':question_text', $questionText, PDO::PARAM_STR);
        $stmt->bindParam(':questionId', $questionId, PDO::PARAM_INT);
        $stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT); // Ensure it belongs to the correct quiz

        // Execute the statement
        if (!$stmt->execute()) {
             throw new Exception("Error updating question: " . implode(":", $stmt->errorInfo()));
        }

        // 5. --- Success Feedback ---
        $_SESSION['question_success'] = "Question updated successfully.";
        header('Location: ' . $successRedirectTarget); // Redirect back to the question list
        exit;

    } catch (Exception $e) {
        // --- Error Feedback ---
        $_SESSION['question_error'] = "An error occurred while updating the question: " . $e->getMessage();
        error_log("Question Update Error (ID: {$questionId}): " . $e->getMessage());

        header('Location: ' . $errorRedirectTarget); // Redirect back to the edit question form
        exit;
    }

} else {
    // Not a POST request
    $_SESSION['quiz_error'] = "Invalid access method."; // Use quiz_error if going to quiz list
    header('Location: manageQuiz.php'); // Redirect to main list
    exit;
}

?>