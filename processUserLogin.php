<?php
session_start(); // Start session to store user data and messages

// 1. --- Essential Includes and Setup ---
require_once 'includes/db.php'; // Adjust path if this file is not in root

// --- Define Redirect Targets ---
$successRedirectTarget = 'index.php'; // Redirect to homepage on successful login
$loginPage = 'login.php';

// --- Redirect if already logged in as user ---
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $successRedirectTarget);
    exit();
}

// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve and Sanitize Input ---
    // *** RENAME variables holding user input ***
    $submittedUsername = trim($_POST['username'] ?? ''); // <<< RENAMED
    $submittedPassword = $_POST['password'] ?? '';     // <<< RENAMED
    // *********************************************

    // --- Basic Validation ---
    if (empty($submittedUsername) || empty($submittedPassword)) { // <<< USE RENAMED VARS
        $_SESSION['login_error'] = "Username and Password are required.";
        header('Location: ' . $loginPage);
        exit;
    }

    // 4. --- Database Operation ---
    // *** connect_db() will now correctly use the global variables from db.php ***
    $pdo = connect_db();
    if (!$pdo) {
        // Log detailed error, show generic message to user
        error_log("User Login Error: Database connection failed.");
        $_SESSION['login_error'] = "Login failed due to a server issue. Please try again later.";
        header('Location: ' . $loginPage);
        exit;
    }

    try {
        // Prepare statement to find user by the SUBMITTED username
        // *** MODIFIED: Removed is_active from SELECT list ***
        $query = "SELECT id, username, password, role FROM users WHERE username = :submittedUsername"; // Use placeholder
        $stmt = $pdo->prepare($query);
        // Bind the SUBMITTED username variable
        $stmt->bindParam(':submittedUsername', $submittedUsername, PDO::PARAM_STR); // <<< USE RENAMED VAR
        $stmt->execute();

        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // 5. --- Verify User and Password ---
        if ($userData) {
            // User found, now verify the SUBMITTED password against the hash
            if (password_verify($submittedPassword, $userData['password'])) { // <<< USE RENAMED VAR
                // Password is correct!

                // *** REMOVED: Optional check for is_active is now removed/commented out ***
                /*
                // Optional: Check if account is active
                if (!$userData['is_active']) { // THIS WOULD CAUSE AN ERROR NOW
                    $_SESSION['login_error'] = "Your account is not yet active.";
                    header('Location: ' . $loginPage);
                    exit;
                }
                */

                // --- Login Successful - Set Session Variables ---
                session_regenerate_id(true);

                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['user_username'] = $userData['username']; // Store actual username from DB
                $_SESSION['user_role'] = $userData['role'];

                header('Location: ' . $successRedirectTarget);
                exit;

            } else {
                // Password incorrect
                $_SESSION['login_error'] = "Invalid username or password.";
                error_log("User Login Failed: Incorrect password for username: " . htmlspecialchars($submittedUsername)); // Log renamed var
                header('Location: ' . $loginPage);
                exit;
            }
        } else {
            // User not found
            $_SESSION['login_error'] = "Invalid username or password.";
             error_log("User Login Failed: User not found: " . htmlspecialchars($submittedUsername)); // Log renamed var
            header('Location: ' . $loginPage);
            exit;
        }

    } catch (PDOException $e) {
        error_log("User Login PDO Error for username " . htmlspecialchars($submittedUsername) . ": " . $e->getMessage()); // Log renamed var
        $_SESSION['login_error'] = "Login failed due to a database issue. Please try again later."; // This is the message you saw
        header('Location: ' . $loginPage);
        exit;
    } catch (Exception $e) {
         error_log("User Login General Error for username " . htmlspecialchars($submittedUsername) . ": " . $e->getMessage()); // Log renamed var
         $_SESSION['login_error'] = "An unexpected error occurred during login.";
         header('Location: ' . $loginPage);
         exit;
    }

} else {
    // Not a POST request
    header('Location: ' . $loginPage);
    exit;
}

?>