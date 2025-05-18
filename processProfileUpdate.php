<?php
session_start();

require_once 'includes/db.php';
require_once 'includes/csrf.php';

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['profile_status_type'] = 'danger';
    $_SESSION['profile_status_message'] = 'Invalid security token. Please try again.';
    $_SESSION['profile_error_fields'] = ['form_token' => 'Security token mismatch.'];
    header('Location: profile.php');
    exit();
}
regenerate_csrf_token();

$profilePage = 'profile.php';
$loginPage = 'login.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Please log in to update your profile.";
    header('Location: ' . $loginPage);
    exit();
}
$userId = $_SESSION['user_id'];

$profileUploadDir = dirname(__FILE__) . '/assets/images/profiles/';
if (!is_dir($profileUploadDir)) { if (!mkdir($profileUploadDir, 0755, true)) { $_SESSION['profile_status_type'] = 'danger'; $_SESSION['profile_status_message'] = "Server configuration error (E001)."; header('Location: ' . $profilePage); exit; } }
if (!is_writable($profileUploadDir)) { $_SESSION['profile_status_type'] = 'danger'; $_SESSION['profile_status_message'] = "Server configuration error (E002)."; header('Location: ' . $profilePage); exit; }

$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 2 * 1024 * 1024;
$defaultProfilePicFilename = 'default.png';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_input = trim($_POST['email'] ?? '');
    $firstName_input = trim($_POST['firstName'] ?? '');
    $lastName_input = trim($_POST['lastName'] ?? '');
    $currentPassword_input = $_POST['currentPassword'] ?? ''; // Keep this to check if user *tried* to type current pass
    $newPassword_input = $_POST['newPassword'] ?? '';
    $confirmNewPassword_input = $_POST['confirmNewPassword'] ?? '';
    $removePicture = isset($_POST['removePicture']);
    $profilePicFile = $_FILES['profilePicture'] ?? null;

    $_SESSION['old_profile_input'] = $_POST;
    $field_errors = [];
    $updateClauses = [];
    $sqlParams = [':userId' => $userId];
    
    // Flags to track intended actions and outcomes
    $passwordChangeIntended = !empty($newPassword_input) || !empty($confirmNewPassword_input) || !empty($currentPassword_input); // User interacted with password fields
    $passwordSuccessfullyValidated = false; // Only true if all password change steps are valid
    $passwordActuallyChangedInDB = false;   // True if password UPDATE clause was executed
    $detailsActuallyChangedInDB = false;   // True if any non-password detail UPDATE clause was executed

    $pdo = connect_db();
    if (!$pdo) { /* ... DB connection error ... */ $_SESSION['profile_status_type'] = 'danger'; $_SESSION['profile_status_message'] = "Database connection failed."; header('Location: ' . $profilePage); exit(); }
    
    $stmtCurrent = $pdo->prepare("SELECT email, password, profile_picture FROM users WHERE id = :userId");
    $stmtCurrent->bindParam(':userId', $userId, PDO::PARAM_INT); $stmtCurrent->execute();
    $currentUserData = $stmtCurrent->fetch(PDO::FETCH_ASSOC); $stmtCurrent->closeCursor();
    if (!$currentUserData) { /* ... User not found error ... */ $_SESSION['login_error'] = "User data not found."; session_destroy(); header('Location: ' . $loginPage); exit(); }

    // --- 1. Field Validations (Email, Name, Picture) ---
    if (empty($email_input)) { $field_errors['email'] = "Email is required."; }
    elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) { $field_errors['email'] = "Invalid email format."; }
    elseif (strtolower($email_input) !== strtolower($currentUserData['email'])) {
        $stmtCheckEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :userId");
        $stmtCheckEmail->bindParam(':email', $email_input); $stmtCheckEmail->bindParam(':userId', $userId); $stmtCheckEmail->execute();
        if ($stmtCheckEmail->fetch()) { $field_errors['email'] = "Email already in use."; } $stmtCheckEmail->closeCursor();
    }
    if (empty($firstName_input)) { $field_errors['firstName'] = "First name is required."; }
    if (empty($lastName_input)) { $field_errors['lastName'] = "Last name is required."; }
    
    $finalPicFilenameForDB = $currentUserData['profile_picture'] ?? $defaultProfilePicFilename;
    $newPicServerPath = null; $newUploadedFilename = null; $deleteOldPicServerPath = null; $picUploadWillProceed = false; $picDbValueWillChange = false;

    if ($profilePicFile && $profilePicFile['error'] === UPLOAD_ERR_OK) {
        if (!in_array($profilePicFile['type'], $allowedImageTypes)) { $field_errors['profilePicture'] = "Invalid image type."; }
        elseif ($profilePicFile['size'] > $maxFileSize) { $field_errors['profilePicture'] = "Image too large (max 2MB)."; }
        else {
            $newUploadedFilename = "user_{$userId}_" . uniqid() . "." . strtolower(pathinfo($profilePicFile['name'], PATHINFO_EXTENSION));
            $newPicServerPath = $profileUploadDir . $newUploadedFilename;
            $picUploadWillProceed = true; 
        }
    } elseif ($profilePicFile && $profilePicFile['error'] !== UPLOAD_ERR_NO_FILE) { $field_errors['profilePicture'] = "Image upload error (code {$profilePicFile['error']})."; }

    if ($removePicture) {
        if (($currentUserData['profile_picture'] ?? $defaultProfilePicFilename) !== $defaultProfilePicFilename) $deleteOldPicServerPath = $profileUploadDir . ($currentUserData['profile_picture'] ?? '');
        $prospectivePicFilename = $defaultProfilePicFilename; 
        if ($picUploadWillProceed) $prospectivePicFilename = $newUploadedFilename; // New upload overrides remove
        if ($prospectivePicFilename !== ($currentUserData['profile_picture'] ?? $defaultProfilePicFilename)) {
            $finalPicFilenameForDB = $prospectivePicFilename; $picDbValueWillChange = true;
        }
    } elseif ($picUploadWillProceed) { 
        if (($currentUserData['profile_picture'] ?? $defaultProfilePicFilename) !== $defaultProfilePicFilename && ($currentUserData['profile_picture'] ?? '') !== $newUploadedFilename) $deleteOldPicServerPath = $profileUploadDir . ($currentUserData['profile_picture'] ?? '');
        $finalPicFilenameForDB = $newUploadedFilename;
        if ($finalPicFilenameForDB !== ($currentUserData['profile_picture'] ?? $defaultProfilePicFilename)) $picDbValueWillChange = true;
    }


    // --- 2. Password Validation (if intended) ---
    // $passwordChangeIntended is true if user typed in current password OR new password OR confirm new password.
    if ($passwordChangeIntended) {
        // Scenario 1: User wants to set a new password (new or confirm is filled)
        if (!empty($newPassword_input) || !empty($confirmNewPassword_input)) {
            if (empty($currentPassword_input)) { $field_errors['currentPassword'] = "Current password required to set a new password."; }
            else {
                if (password_verify($currentPassword_input, $currentUserData['password'])) {
                    if (empty($newPassword_input)) { $field_errors['newPassword'] = "New password cannot be empty."; }
                    elseif (strlen($newPassword_input) < 8) { $field_errors['newPassword'] = "New password too short (min 8 chars)."; }
                    elseif ($newPassword_input !== $confirmNewPassword_input) {
                        $field_errors['passwordMismatch'] = "New passwords do not match."; 
                        $field_errors['confirmNewPassword'] = "Passwords do not match."; 
                    } else {
                        $passwordSuccessfullyValidated = true; // All checks for a new password passed
                    }
                } else { $field_errors['currentPassword'] = "Incorrect current password."; }
            }
        } 
        // Scenario 2: User only filled current password, but left new/confirm empty.
        // This is an incomplete attempt if they intended to change password but forgot new ones.
        // Or, they might have just typed it by mistake without changing other fields.
        // If other fields *are* changing, we don't strictly need current password.
        // For now, if only currentPassword_input is filled and new/confirm are empty, it's not an error itself,
        // but $passwordSuccessfullyValidated will remain false, so no password change happens.
        // If other fields are valid, they will be updated.
        // If only current password was typed and it was INCORRECT, and no other fields change, this is an implicit error.
        elseif (empty($newPassword_input) && empty($confirmNewPassword_input) && !empty($currentPassword_input)) {
             if (!password_verify($currentPassword_input, $currentUserData['password'])) {
                 $field_errors['currentPassword'] = "Current password incorrect (no new password provided).";
             }
             // $passwordSuccessfullyValidated remains false
        }
    }

    // --- If any validation errors, redirect now ---
    if (!empty($field_errors)) {
        $_SESSION['profile_status_type'] = 'danger';
        // Construct top-level error message
        if (isset($field_errors['currentPassword'])) $_SESSION['profile_status_message'] = $field_errors['currentPassword'];
        elseif (isset($field_errors['passwordMismatch'])) $_SESSION['profile_status_message'] = $field_errors['passwordMismatch'];
        elseif (isset($field_errors['newPassword'])) $_SESSION['profile_status_message'] = $field_errors['newPassword'];
        // Add more prioritizations or a general message
        else $_SESSION['profile_status_message'] = 'Profile update failed. Please check the errors below.';
        
        $_SESSION['profile_error_fields'] = $field_errors;
        header('Location: ' . $profilePage);
        exit;
    }

    // --- No validation errors, Proceed to Build Update ---
    // Non-password details
    if (strtolower($email_input) !== strtolower($currentUserData['email'])) { $updateClauses[] = "email = :email"; $sqlParams[':email'] = $email_input; }
    if ($firstName_input !== $currentUserData['first_name']) { $updateClauses[] = "first_name = :first_name"; $sqlParams[':first_name'] = $firstName_input; }
    if ($lastName_input !== $currentUserData['last_name']) { $updateClauses[] = "last_name = :last_name"; $sqlParams[':last_name'] = $lastName_input; }
    if ($picDbValueWillChange) { $updateClauses[] = "profile_picture = :profile_picture"; $sqlParams[':profile_picture'] = $finalPicFilenameForDB; }
    
    // Password
    if ($passwordChangeIntended && $passwordSuccessfullyValidated) {
        $sqlParams[':password'] = password_hash($newPassword_input, PASSWORD_DEFAULT);
        $updateClauses[] = "password = :password";
    }

    // --- Perform Database Update (if there's anything to update) ---
    if (!empty($updateClauses)) {
        try {
            $pdo->beginTransaction();
            $sqlUpdate = "UPDATE users SET " . implode(", ", $updateClauses) . " WHERE id = :userId";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($sqlParams);
            $pdo->commit();

            // Check what was actually updated for success message
            if(isset($sqlParams[':email']) || isset($sqlParams[':first_name']) || isset($sqlParams[':last_name']) || isset($sqlParams[':profile_picture'])) $detailsActuallyChangedInDB = true;
            if(isset($sqlParams[':password'])) $passwordActuallyChangedInDB = true;

            // File system operations for profile picture
            if ($picDbValueWillChange) { // Corresponds to :profile_picture in sqlParams
                if ($picUploadWillProceed && $newPicServerPath && $finalPicFilenameForDB === $newUploadedFilename) {
                    if (!move_uploaded_file($profilePicFile['tmp_name'], $newPicServerPath)) { error_log("Failed to move {$profilePicFile['tmp_name']} to {$newPicServerPath}");}
                }
                if ($deleteOldPicServerPath && file_exists($deleteOldPicServerPath) && $deleteOldPicServerPath !== $newPicServerPath) {
                    unlink($deleteOldPicServerPath);
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($picUploadWillProceed && $newPicServerPath && file_exists($newPicServerPath) && $picDbValueWillChange) unlink($newPicServerPath);
            $_SESSION['profile_status_type'] = 'danger';
            $_SESSION['profile_status_message'] = "Database update failed. Error: " . $e->getMessage();
            error_log("Profile Update DB Exception UserID {$userId}: " . $e->getMessage());
            header('Location: ' . $profilePage);
            exit;
        }
    }

    // --- Construct Final Top-Level Message ---
    $successMessageParts = [];
    if ($detailsActuallyChangedInDB) $successMessageParts[] = "Profile details updated.";
    if ($passwordActuallyChangedInDB) $successMessageParts[] = "Password changed.";
    
    if (!empty($successMessageParts)) {
        $_SESSION['profile_status_type'] = 'success';
        $_SESSION['profile_status_message'] = implode(" ", $successMessageParts);
    } else if (empty($field_errors)) { 
        $_SESSION['profile_status_type'] = 'info'; 
        $_SESSION['profile_status_message'] = "No changes were made to your profile.";
    }
    // Note: If $field_errors was populated, script would have exited earlier.
    // This 'else if' handles the case where form was submitted, no errors, but no actual DB changes happened.

    // Update live session vars if needed for immediate display
    if ($detailsActuallyChangedInDB) {
      if (isset($_SESSION['user_first_name'])) $_SESSION['user_first_name'] = $firstName_input;
      if (isset($_SESSION['user_profile_picture']) && $picDbValueWillChange) $_SESSION['user_profile_picture'] = $finalPicFilenameForDB;
    }


    unset($_SESSION['old_profile_input']); 
    header('Location: ' . $profilePage);
    exit;

} else { 
    header('Location: ' . $profilePage);
    exit;
}
?>