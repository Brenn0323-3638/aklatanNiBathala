<?php
session_start(); // Start session for auth check and messages

// 1. --- Essential Includes and Setup ---
require_once 'includes/db.php'; // Adjust path if this file is not in root

// --- Define Redirect Targets ---
$submitPage = 'submitMyth.php';
$successRedirectTarget = 'myths.php'; // Redirect to myths list on success
$loginPage = 'login.php';

// 2. --- Authentication Check (CRUCIAL) ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Please log in to submit a myth.";
    header('Location: ' . $loginPage);
    exit();
}
$userId = $_SESSION['user_id'];

if ($userId === null || !filter_var($userId, FILTER_VALIDATE_INT)) {
     error_log("Submit Myth Error: User ID not found or invalid in session during submission process.");
     $_SESSION['submit_error'] = "Your session seems invalid. Please log out and log back in to submit.";
     header('Location: ' . $submitPage);
     exit();
}

// 3. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. --- Retrieve Input ---
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $sourceInfo = trim($_POST['source_info'] ?? '');
    $imageSuggestion = trim($_POST['image_suggestion'] ?? '');

    $_SESSION['old_submit_input'] = $_POST;

    // 5. --- Validation ---
    $errors = [];
    if (empty($title)) {
        $errors[] = "Myth Title is required.";
    }
    if (empty($content)) {
        $errors[] = "Myth Content/Story is required.";
    }

    if (!empty($errors)) {
        $_SESSION['submit_error'] = implode("<br>", $errors);
        header('Location: ' . $submitPage);
        exit;
    } else {
        // --- Validation Passed - Proceed ---
        $pdo = connect_db();
        if (!$pdo) {
            error_log("Submit Myth Error: Database connection failed.");
            $_SESSION['submit_error'] = "Submission failed due to a server issue. Please try again later.";
            header('Location: ' . $submitPage);
            exit;
        }

        try {
            // *** ADDED: Fetch Submitter's Name and Email ***
            $submitterName = null;
            $submitterEmail = null;

            $sqlUser = "SELECT first_name, last_name, email FROM users WHERE id = :userId";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtUser->execute();
            $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                // Combine first and last name, handle cases where one might be missing
                $firstName = trim($userData['first_name'] ?? '');
                $lastName = trim($userData['last_name'] ?? '');
                if ($firstName && $lastName) {
                    $submitterName = $firstName . ' ' . $lastName;
                } elseif ($firstName) {
                    $submitterName = $firstName;
                } elseif ($lastName) {
                    $submitterName = $lastName;
                } // If both are empty, $submitterName remains null

                $submitterEmail = $userData['email'] ?? null; // Get email
            } else {
                // User ID from session doesn't exist in users table? Should not happen if logged in.
                error_log("Submit Myth Warning: Could not find user data for logged-in user ID: {$userId}");
                // Decide if this is fatal - perhaps proceed without name/email?
                // For now, we'll allow it but log a warning.
            }
            // ***********************************************


            // *** MODIFIED: Include submitter_name and submitter_email ***
            $sqlInsert = "INSERT INTO pending_myths
                            (title, content, category, tags, source_info, image_suggestion, submitted_by_user_id, submitter_name, submitter_email)
                          VALUES
                            (:title, :content, :category, :tags, :source_info, :image_suggestion, :user_id, :submitter_name, :submitter_email)";
            $stmtInsert = $pdo->prepare($sqlInsert);

            // Bind parameters
            $stmtInsert->bindParam(':title', $title, PDO::PARAM_STR);
            $stmtInsert->bindParam(':content', $content, PDO::PARAM_STR);
            $stmtInsert->bindParam(':category', $category, !empty($category) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsert->bindParam(':tags', $tags, !empty($tags) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsert->bindParam(':source_info', $sourceInfo, !empty($sourceInfo) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsert->bindParam(':image_suggestion', $imageSuggestion, !empty($imageSuggestion) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsert->bindParam(':user_id', $userId, PDO::PARAM_INT);
            // *** ADDED Bindings ***
            $stmtInsert->bindParam(':submitter_name', $submitterName, $submitterName !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsert->bindParam(':submitter_email', $submitterEmail, $submitterEmail !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            // ********************

            if (!$stmtInsert->execute()) {
                 throw new Exception("Database error during submission: " . implode(":", $stmtInsert->errorInfo()));
            }

            unset($_SESSION['old_submit_input']);
            $_SESSION['submit_success'] = "Thank you! Your myth submission has been received and is pending review by an administrator.";
            header('Location: ' . $successRedirectTarget);
            exit;

        } catch (PDOException | Exception $e) {
            error_log("Submit Myth DB/General Error: " . $e->getMessage());
            $_SESSION['submit_error'] = "An error occurred while submitting your entry. Please try again.";
            header('Location: ' . $submitPage);
            exit;
        }
    }

} else {
    $_SESSION['submit_error'] = "Invalid access method. Please use the form.";
    header('Location: ' . $submitPage);
    exit;
}

?>