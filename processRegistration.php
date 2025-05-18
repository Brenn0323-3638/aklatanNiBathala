<?php
session_start(); // Start session for messages

// 1. --- Essential Includes and Setup ---
require_once 'includes/db.php'; // Adjust path if not in root

// --- Define Redirect Targets ---
$registrationPage = 'register.php';
$loginPage = 'login.php'; // Redirect here on success

// --- Redirect if already logged in ---
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect logged-in users away
    exit();
}

// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve Input ---
    $submittedFirstName = trim($_POST['firstName'] ?? '');
    $submittedLastName = trim($_POST['lastName'] ?? '');
    $submittedUsername = trim($_POST['username'] ?? '');
    $submittedEmail = trim($_POST['email'] ?? '');
    $submittedPassword = $_POST['password'] ?? '';
    $submittedConfirmPassword = $_POST['confirmPassword'] ?? '';

    // Store input in session in case of error for repopulation
    $_SESSION['old_register_input'] = $_POST;

    // 4. --- Validation ---
    $errors = []; // Array to hold validation errors

    // First Name Validation (NOW REQUIRED)
    if (empty($submittedFirstName)) {
        $errors[] = "First name is required.";
    } elseif (strlen($submittedFirstName) > 50) { // Max length, adjust as needed
        $errors[] = "First name cannot exceed 50 characters.";
    }

    // Last Name Validation (NOW REQUIRED)
    if (empty($submittedLastName)) {
        $errors[] = "Last name is required.";
    } elseif (strlen($submittedLastName) > 50) { // Max length, adjust as needed
        $errors[] = "Last name cannot exceed 50 characters.";
    }

    // Username Validation
    if (empty($submittedUsername)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $submittedUsername)) {
        $errors[] = "Username must be 3-30 characters long and contain only letters, numbers, and underscores.";
    }

    // Email Validation
    if (empty($submittedEmail)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password Validation
    if (empty($submittedPassword)) {
        $errors[] = "Password is required.";
    } elseif (strlen($submittedPassword) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif ($submittedPassword !== $submittedConfirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // --- Database Checks (Username/Email Uniqueness) ---
    if (empty($errors)) { // Only proceed with DB checks if basic validation passed
        $pdo = connect_db(); // Ensure this function sets PDO error mode to Exception
        if (!$pdo) {
            error_log("Registration Error: Database connection failed.");
            $errors[] = "Registration failed due to a server issue. Please try again later.";
        } else {
            // Check if username exists
            try {
                $sqlCheckUser = "SELECT id FROM users WHERE username = :username";
                $stmtCheckUser = $pdo->prepare($sqlCheckUser);
                $stmtCheckUser->bindParam(':username', $submittedUsername, PDO::PARAM_STR);
                $stmtCheckUser->execute();
                if ($stmtCheckUser->fetch()) {
                    $errors[] = "Username is already taken. Please choose another.";
                }
            } catch (PDOException $e) {
                 error_log("Registration Check Error (Username: {$submittedUsername}): " . $e->getMessage());
                 $errors[] = "Could not verify username uniqueness. Please try again.";
            }

            // Check if email exists
            try {
                $sqlCheckEmail = "SELECT id FROM users WHERE email = :email";
                $stmtCheckEmail = $pdo->prepare($sqlCheckEmail);
                $stmtCheckEmail->bindParam(':email', $submittedEmail, PDO::PARAM_STR);
                $stmtCheckEmail->execute();
                if ($stmtCheckEmail->fetch()) {
                    $errors[] = "Email address is already registered. Please use a different email or login.";
                }
            } catch (PDOException $e) {
                 error_log("Registration Check Error (Email: {$submittedEmail}): " . $e->getMessage());
                 $errors[] = "Could not verify email uniqueness. Please try again.";
            }
        }
    }


    // 5. --- Process Registration or Redirect with Errors ---
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        // $_SESSION['old_register_input'] is already set
        header('Location: ' . $registrationPage);
        exit;
    } else {
        // --- Validation Passed - Proceed with Registration ---
        try {
            // Hash the password securely
            $hashedPassword = password_hash($submittedPassword, PASSWORD_BCRYPT);
            if ($hashedPassword === false) {
                 throw new Exception("Password hashing failed.");
            }

            // Prepare SQL INSERT statement
            $sqlInsert = "INSERT INTO users (username, email, password, role, first_name, last_name, created_at)
                          VALUES (:username, :email, :password, :role, :first_name, :last_name, NOW())";
            $stmtInsert = $pdo->prepare($sqlInsert);

            $userRole = 'user'; // Set default role

            // Bind parameters
            $stmtInsert->bindParam(':username', $submittedUsername, PDO::PARAM_STR);
            $stmtInsert->bindParam(':email', $submittedEmail, PDO::PARAM_STR);
            $stmtInsert->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmtInsert->bindParam(':role', $userRole, PDO::PARAM_STR);
            $stmtInsert->bindParam(':first_name', $submittedFirstName, PDO::PARAM_STR);
            $stmtInsert->bindParam(':last_name', $submittedLastName, PDO::PARAM_STR);

            if (!$stmtInsert->execute()) {
                 $dbError = $stmtInsert->errorInfo();
                 error_log("Database INSERT failed for user '{$submittedUsername}': " . implode(":", $dbError));
                 throw new Exception("Database error during registration. Error: " . $dbError[2]);
            }

            unset($_SESSION['old_register_input']);
            $_SESSION['register_success'] = "Registration successful! Please log in.";
            header('Location: ' . $loginPage);
            exit;

        } catch (PDOException $e) {
            error_log("Registration DB PDOException for user '{$submittedUsername}': " . $e->getMessage());
            $_SESSION['register_errors'] = ["Database operation failed during registration. Please try again."];
            header('Location: ' . $registrationPage);
            exit;
        } catch (Exception $e) {
             error_log("Registration General Exception for user '{$submittedUsername}': " . $e->getMessage());
             $_SESSION['register_errors'] = ["An unexpected error occurred during registration: " . $e->getMessage()];
             header('Location: ' . $registrationPage);
             exit;
        }
    }

} else {
    header('Location: ' . $registrationPage);
    exit;
}
?>
