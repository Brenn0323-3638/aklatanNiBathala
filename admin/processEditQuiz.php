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
$successRedirectTarget = 'manageQuiz.php';
$errorRedirectTarget = 'manageQuiz.php'; // Default redirect on general failure
$quizId = null; // Initialize quizId

// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve and Validate Input ---
    // Get Quiz ID from hidden field
    if (isset($_POST['quizId']) && filter_var($_POST['quizId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $quizId = (int)$_POST['quizId'];
        $errorRedirectTarget = 'editQuiz.php?quiz_id=' . $quizId; // Specific redirect target on validation/update error
    } else {
        $_SESSION['quiz_error'] = "Invalid or missing Quiz ID for update.";
        header("Location: manageQuiz.php"); // Redirect to list if ID missing entirely
        exit;
    }

    $quizTitle = trim($_POST['quizTitle'] ?? '');
    $quizDescription = trim($_POST['quizDescription'] ?? '');
    // Handle checkbox: Default to 0 (false/inactive) if not submitted
    $isActive = (isset($_POST['isActive']) && $_POST['isActive'] == '1') ? 1 : 0;

    // --- Basic Validation ---
    if (empty($quizTitle)) {
        $_SESSION['quiz_error'] = "Quiz Title is required.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }
    // Add more validation if needed


    // 4. --- Database Operation ---
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['quiz_error'] = "Database connection error.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }

    try {
        // No transaction needed for single update, but doesn't hurt
        // $pdo->beginTransaction();

        // Add updated_at = NOW() to the SET clause
        $sql = "UPDATE quizzes SET
                    title = :title,
                    description = :description,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE quiz_id = :quizId";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':title', $quizTitle, PDO::PARAM_STR);
        $stmt->bindParam(':description', $quizDescription, PDO::PARAM_STR);
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT);
        $stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT); // Bind the quiz ID for the WHERE clause

        // Execute the statement
        if (!$stmt->execute()) {
             throw new Exception("Error updating quiz: " . implode(":", $stmt->errorInfo()));
        }

        // $pdo->commit(); // Commit if using transaction

        // 5. --- Success Feedback ---
        $_SESSION['quiz_success'] = "Quiz '" . htmlspecialchars($quizTitle) . "' updated successfully.";
        header('Location: ' . $successRedirectTarget); // Redirect to the quiz list
        exit;

    } catch (Exception $e) {
        // --- Rollback Transaction on Error (if using transaction) ---
        // if ($pdo->inTransaction()) { $pdo->rollBack(); }

        // --- Error Feedback ---
        $_SESSION['quiz_error'] = "An error occurred while updating the quiz: " . $e->getMessage();
        error_log("Quiz Update Error (ID: {$quizId}): " . $e->getMessage());

        header('Location: ' . $errorRedirectTarget); // Redirect back to the edit form
        exit;
    }

} else {
    // Not a POST request
    $_SESSION['quiz_error'] = "Invalid access method.";
    header('Location: manageQuiz.php'); // Redirect to list
    exit;
}

?>