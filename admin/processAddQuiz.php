<?php
session_start(); // Start session for messages and auth check

// 1. --- Essential Includes and Setup ---
require_once '../includes/db.php'; // Your database connection script

// --- Authentication Check (Consistent with other admin scripts) ---
if (!isset($_SESSION['adminId'])) {
    $_SESSION['login_error'] = "Please log in to manage quizzes."; // Optional message for login page
    header("Location: adminLogin.php");
    exit();
}

// --- Define Redirect Targets ---
$successRedirectTarget = 'manageQuiz.php';
$errorRedirectTarget = 'addQuiz.php';


// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- CSRF Check (Optional but Recommended) ---
    /*
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['quiz_error'] = "Invalid request (CSRF token mismatch). Please try again.";
        header('Location: ' . $errorRedirectTarget);
        exit;
    }
    */

    // 3. --- Retrieve and Sanitize Input ---
    $quizTitle = trim($_POST['quizTitle'] ?? '');
    $quizDescription = trim($_POST['quizDescription'] ?? '');
    // Handle checkbox: isset checks if it was submitted (checked), '1' is the value we assigned. Default to 0 (false/inactive) if not checked.
    $isActive = (isset($_POST['isActive']) && $_POST['isActive'] == '1') ? 1 : 0; // Store as 1 or 0 for boolean/tinyint

    // --- Basic Validation ---
    if (empty($quizTitle)) {
        $_SESSION['quiz_error'] = "Quiz Title is required.";
        header('Location: ' . $errorRedirectTarget);
        exit;
    }
    // Add more validation if needed (e.g., title length)


    // 4. --- Database Operation ---
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['quiz_error'] = "Database connection error.";
        header('Location: ' . $errorRedirectTarget);
        exit;
    }

    try {
        // No transaction needed for single insert, but doesn't hurt
        // $pdo->beginTransaction();

        $sql = "INSERT INTO quizzes (title, description, is_active, created_at, updated_at)
                VALUES (:title, :description, :is_active, NOW(), NOW())";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':title', $quizTitle, PDO::PARAM_STR);
        $stmt->bindParam(':description', $quizDescription, PDO::PARAM_STR);
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT); // Bind as integer (0 or 1)

        // Execute the statement
        if (!$stmt->execute()) {
             // Throw exception if execution fails
             throw new Exception("Error creating quiz: " . implode(":", $stmt->errorInfo()));
        }

        // $pdo->commit(); // Commit if using transaction

        // 5. --- Success Feedback ---
        $_SESSION['quiz_success'] = "Quiz '" . htmlspecialchars($quizTitle) . "' created successfully.";
        header('Location: ' . $successRedirectTarget); // Redirect to the quiz list
        exit;

    } catch (Exception $e) {
        // --- Rollback Transaction on Error (if using transaction) ---
        // if ($pdo->inTransaction()) {
        //     $pdo->rollBack();
        // }

        // --- Error Feedback ---
        $_SESSION['quiz_error'] = "An error occurred while creating the quiz: " . $e->getMessage();
        error_log("Quiz Add Error: " . $e->getMessage()); // Log detailed error

        header('Location: ' . $errorRedirectTarget); // Redirect back to the form
        exit;
    }

} else {
    // Not a POST request, redirect away
    $_SESSION['quiz_error'] = "Invalid access method.";
    header('Location: ' . $errorRedirectTarget); // Redirect to add form or list?
    exit;
}

?>