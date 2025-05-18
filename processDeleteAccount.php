<?php
session_start();

require_once 'includes/db.php';
require_once 'includes/csrf.php'; // For CSRF token verification

// --- 1. Authentication and Request Method Check ---
if (!isset($_SESSION['user_id'])) {
    // Not logged in, should not happen if form is on profile page
    $_SESSION['login_error'] = "Please log in to perform this action.";
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Allow only POST requests
    $_SESSION['delete_account_error'] = "Invalid request method.";
    header('Location: profile.php');
    exit();
}

// --- 2. CSRF Token Verification ---
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['delete_account_error'] = "Invalid security token. Please try again.";
    header('Location: profile.php');
    exit();
}
// Regenerate CSRF token after successful POST to prevent reuse on next form
regenerate_csrf_token();


// --- 3. Input Validation ---
$current_password_confirm = trim($_POST['current_password_confirm'] ?? '');

if (empty($current_password_confirm)) {
    $_SESSION['delete_account_error'] = "Please enter your current password to confirm deletion.";
    header('Location: profile.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userProfilePicture = null; // To store the filename if it needs to be deleted

try {
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['delete_account_error'] = "Database connection failed. Please try again later.";
        error_log("processDeleteAccount: Database connection failed for user_id {$userId}.");
        header('Location: profile.php');
        exit();
    }

    // --- 4. Verify Current Password ---
    $stmt_user = $pdo->prepare("SELECT password, profile_picture FROM users WHERE id = :user_id");
    $stmt_user->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt_user->execute();
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $stmt_user->closeCursor();

    if (!$user) {
        // Should not happen if user is logged in and session is valid
        $_SESSION['delete_account_error'] = "User account not found. Please contact support.";
        error_log("processDeleteAccount: User not found in DB for logged-in user_id {$userId}.");
        // Log out the user as a precaution
        unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_email'], $_SESSION['user_profile_picture'], $_SESSION['csrf_token']);
        if (session_status() == PHP_SESSION_ACTIVE) { session_destroy(); }
        header('Location: login.php?message=session_error');
        exit();
    }

    if (!password_verify($current_password_confirm, $user['password'])) {
        $_SESSION['delete_account_error'] = "Incorrect password. Account deletion cancelled.";
        header('Location: profile.php');
        exit();
    }

    // Store profile picture filename for later deletion if it's not the default
    if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.png') {
        $userProfilePicture = $user['profile_picture'];
    }


    // --- 5. Perform Deletion (within a transaction) ---
    $pdo->beginTransaction();

    try {
        // a. Delete quiz attempts (FOREIGN KEY ON DELETE CASCADE in your DB should handle this for users table, but explicit is safer or if FK is not set)
        // Your 'quiz_attempts' table has ON DELETE CASCADE for 'user_id', so this is redundant if the user is deleted from 'users'.
        // However, if you wanted to delete attempts without deleting the user (not the case here), you'd do:
        // $stmt_del_attempts = $pdo->prepare("DELETE FROM quiz_attempts WHERE user_id = :user_id");
        // $stmt_del_attempts->bindParam(':user_id', $userId, PDO::PARAM_INT);
        // $stmt_del_attempts->execute();

        // b. Delete myths created by the user (FOREIGN KEY ON DELETE CASCADE should handle this for users table)
        // Your 'myths' table has ON DELETE CASCADE for 'user_id'.
        // When a myth is deleted, 'myth_files' associated with that myth are also deleted due to ON DELETE CASCADE on myth_id.

        // c. Anonymize pending myths (FOREIGN KEY ON DELETE SET NULL is already set in your DB)
        // Your 'pending_myths' table has ON DELETE SET NULL for 'submitted_by_user_id'.

        // d. Add any other related data deletions here if necessary for tables not covered by CASCADE.
        // Example: DELETE FROM user_comments WHERE user_id = :user_id;
        // Example: DELETE FROM user_favorites WHERE user_id = :user_id;

        // e. Delete the user record from the 'users' table
        // This will trigger the ON DELETE CASCADE / ON DELETE SET NULL constraints on other tables.
        $stmt_del_user = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt_del_user->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt_del_user->execute();

        // If all DB operations are successful:
        $pdo->commit();

        // --- 6. Delete Profile Picture File (after successful DB commit) ---
        if ($userProfilePicture) {
            // Construct the server path to the image
            // Assuming profile.php (and this script) are in the root.
            // $base_asset_url in header.php helps determine this, but we need server path.
            $profilePicPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/profiles/' . $userProfilePicture;
            // A more robust way to get base path if script isn't in root:
            // $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'); // e.g., /aklatan
            // $profilePicPath = $_SERVER['DOCUMENT_ROOT'] . $basePath . '/assets/images/profiles/' . $userProfilePicture;
            // For your current structure (profile.php in root), the first option is likely correct.
            // Make sure 'assets' is in the web root or adjust the path.

            // More reliable path construction (assuming current script in root, and assets is a sibling of root or in root)
            // If your project structure is localhost/aklatan_ni_bathala/profile.php
            // and images are in localhost/aklatan_ni_bathala/assets/images/profiles/
            $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
            $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); //  If script is in root, this might be empty or /
            $baseProjectDir = $documentRoot . $scriptDir; // Path to your project's root folder on the server

            // Adjust this path based on your actual file structure:
            $profilePicFullPath = $baseProjectDir . '/assets/images/profiles/' . $userProfilePicture;
            
            // Check if file exists and delete
            if (file_exists($profilePicFullPath)) {
                if (!unlink($profilePicFullPath)) {
                    // Log error if unlink fails, but don't necessarily stop user logout
                    error_log("processDeleteAccount: Failed to delete profile picture {$profilePicFullPath} for user_id {$userId}. Check permissions.");
                } else {
                    error_log("processDeleteAccount: Successfully deleted profile picture {$profilePicFullPath} for user_id {$userId}.");
                }
            } else {
                error_log("processDeleteAccount: Profile picture {$profilePicFullPath} not found for deletion for user_id {$userId}.");
            }
        }

        // --- 7. Log Out User and Redirect ---
        // Clear all session data related to the user
        unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_email'], $_SESSION['user_profile_picture'], $_SESSION['user_role'], $_SESSION['csrf_token']);
        // Destroy the session completely
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        // Start a new session just to pass a success message (optional)
        session_start();
        $_SESSION['account_deleted_success'] = "Your account and all associated data have been permanently deleted. We're sorry to see you go.";
        header('Location: login.php?status=account_deleted'); // Redirect to login or homepage
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['delete_account_error'] = "A database error occurred during account deletion. Please try again or contact support.";
        error_log("processDeleteAccount PDOException for user_id {$userId}: " . $e->getMessage());
        header('Location: profile.php');
        exit();
    }

} catch (Exception $e) {
    // Catch any other general errors
    $_SESSION['delete_account_error'] = "An unexpected error occurred. Please try again.";
    error_log("processDeleteAccount Exception for user_id {$userId}: " . $e->getMessage());
    header('Location: profile.php');
    exit();
}