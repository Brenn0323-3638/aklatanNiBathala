<?php
session_start(); // Start session to store messages and get user ID

// 1. --- Essential Includes and Setup ---
require_once '../includes/db.php'; // Your database connection script

// --- Authentication Check (Copy from processAddMyth.php) ---
if (!isset($_SESSION['adminId'])) { // Checking for adminId now based on previous fix
    // Redirect to login if adminId is NOT set
    $_SESSION['error_message'] = "Authentication required."; // Use a consistent key
    header("Location: adminLogin.php");
    exit();
}
$adminUserId = $_SESSION['adminId']; // Get the admin ID for potential logging/auditing (not used in UPDATE here)

// --- Configuration (Copy from processAddMyth.php) ---
$uploadDir = 'aklatanUploads/';
$allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// --- Store redirect target in case of error ---
$errorRedirectTarget = 'viewMyths.php'; // Default redirect on general failure
$mythId = null; // Initialize mythId


// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. --- Retrieve and Validate Input ---
    if (isset($_POST['mythId']) && filter_var($_POST['mythId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $mythId = (int)$_POST['mythId'];
        $errorRedirectTarget = "editMyth.php?id=" . $mythId; // Set specific redirect target on error
    } else {
        $_SESSION['upload_error'] = "Invalid or missing Myth ID.";
        header("Location: " . $errorRedirectTarget); // Redirect to viewMyths if ID missing entirely
        exit;
    }

    $mythTitle = trim($_POST['mythTitle'] ?? '');
    $mythContent = trim($_POST['mythContent'] ?? '');
    $fileDescription = trim($_POST['fileDescription'] ?? '');

    // --- Basic Validation ---
    if (empty($mythTitle)) {
        $_SESSION['upload_error'] = "Myth Title is required.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }

    // 4. --- File Upload Handling (If new file submitted) ---
    $newFileUploaded = false;
    $newOriginalFilename = null;
    $newStoredFilename = null;
    $newTargetFilePath = null;
    $newFileSize = null;
    $oldFilePath = null; // To store the path of the file being replaced

    // Check if a *new* file was uploaded
    if (isset($_FILES['mythFile']) && $_FILES['mythFile']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['mythFile']['tmp_name'];
        $newOriginalFilename = basename($_FILES['mythFile']['name']);
        $newFileSize = $_FILES['mythFile']['size'];
        $fileNameCmps = explode(".", $newOriginalFilename);
        $fileExtension = strtolower(end($fileNameCmps));

        // --- File Validation (like in add) ---
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['upload_error'] = "Invalid file type. Allowed types: " . implode(', ', $allowedExtensions);
            header("Location: " . $errorRedirectTarget);
            exit;
        }
        if ($newFileSize > $maxFileSize) {
            $_SESSION['upload_error'] = "File is too large. Maximum size: " . ($maxFileSize / 1024 / 1024) . " MB.";
            header("Location: " . $errorRedirectTarget);
            exit;
        }

        // --- Generate a new Unique Filename ---
        $newStoredFilename = uniqid('mythfile_', true) . '.' . $fileExtension;
        $newTargetFilePath = $uploadDir . $newStoredFilename;

        // --- Try to move the *new* uploaded file ---
        if (move_uploaded_file($fileTmpPath, $newTargetFilePath)) {
            $newFileUploaded = true;
            // New file is successfully saved. We'll handle DB update and old file deletion in the transaction.
        } else {
            $_SESSION['upload_error'] = "Error moving uploaded file. Check server permissions for '$uploadDir'.";
            error_log("File upload error (Edit): Failed to move '{$fileTmpPath}' to '{$newTargetFilePath}' for myth ID {$mythId}");
            header("Location: " . $errorRedirectTarget);
            exit;
        }

    } elseif (isset($_FILES['mythFile']) && $_FILES['mythFile']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        $_SESSION['upload_error'] = "File upload error: Code " . $_FILES['mythFile']['error'];
        header("Location: " . $errorRedirectTarget);
        exit;
    }
    // Note: If NO new file was uploaded ($newFileUploaded remains false), we just update text fields and maybe file description.


    // 5. --- Database Operations ---
    $pdo = connect_db();
    if (!$pdo) {
        $_SESSION['upload_error'] = "Database connection error.";
        header("Location: " . $errorRedirectTarget);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // --- Update `myths` table ---
        // Consider adding an updated_at column later: , updated_at = NOW()
        $sqlMythUpdate = "UPDATE myths SET title = :title, content = :content WHERE id = :mythId";
        $stmtMyth = $pdo->prepare($sqlMythUpdate);
        $stmtMyth->bindParam(':title', $mythTitle, PDO::PARAM_STR);
        $stmtMyth->bindParam(':content', $mythContent, PDO::PARAM_STR);
        $stmtMyth->bindParam(':mythId', $mythId, PDO::PARAM_INT);

        if (!$stmtMyth->execute()) {
             throw new Exception("Error updating myth record: " . implode(":", $stmtMyth->errorInfo()));
        }

        // --- Handle File Record Update/Insert/Delete ---
        $oldFilePath = null; // Fetch old path ONLY if we need to delete it

        // Check if a new file was uploaded successfully
        if ($newFileUploaded) {
            // Need to update or insert into myth_files, and potentially delete old file

            // First, get the old file path (if exists) to delete it later
            $sqlGetOldFile = "SELECT file_path FROM myth_files WHERE myth_id = :mythId";
            $stmtOldFile = $pdo->prepare($sqlGetOldFile);
            $stmtOldFile->bindParam(':mythId', $mythId, PDO::PARAM_INT);
            $stmtOldFile->execute();
            $oldFileData = $stmtOldFile->fetch(PDO::FETCH_ASSOC);
            if ($oldFileData && !empty($oldFileData['file_path'])) {
                $oldFilePath = $oldFileData['file_path'];
            }
            $stmtOldFile->closeCursor(); // Close cursor

            // Check if a file record *already exists* for this myth to decide between UPDATE and INSERT
            $sqlCheckFileExists = "SELECT file_id FROM myth_files WHERE myth_id = :mythId";
            $stmtCheck = $pdo->prepare($sqlCheckFileExists);
            $stmtCheck->bindParam(':mythId', $mythId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $fileExists = $stmtCheck->fetch();
            $stmtCheck->closeCursor();

            if ($fileExists) {
                // Record exists, UPDATE it
                $sqlFileUpdate = "UPDATE myth_files SET
                                    original_filename = :orig_name,
                                    stored_filename = :stored_name,
                                    file_path = :path,
                                    file_size = :size,
                                    description = :desc,
                                    upload_timestamp = NOW()
                                WHERE myth_id = :mythId";
                $stmtFile = $pdo->prepare($sqlFileUpdate);
            } else {
                // No record exists, INSERT new one
                $sqlFileInsert = "INSERT INTO myth_files
                                    (myth_id, original_filename, stored_filename, file_path, file_size, description, upload_timestamp)
                                VALUES
                                    (:mythId, :orig_name, :stored_name, :path, :size, :desc, NOW())";
                $stmtFile = $pdo->prepare($sqlFileInsert);
            }

            // Bind parameters for either UPDATE or INSERT
            $stmtFile->bindParam(':mythId', $mythId, PDO::PARAM_INT);
            $stmtFile->bindParam(':orig_name', $newOriginalFilename, PDO::PARAM_STR);
            $stmtFile->bindParam(':stored_name', $newStoredFilename, PDO::PARAM_STR);
            $stmtFile->bindParam(':path', $newTargetFilePath, PDO::PARAM_STR);
            $stmtFile->bindParam(':size', $newFileSize, PDO::PARAM_INT);
            $stmtFile->bindParam(':desc', $fileDescription, PDO::PARAM_STR);

            if (!$stmtFile->execute()) {
                throw new Exception("Error saving file record: " . implode(":", $stmtFile->errorInfo()));
            }

        } else {
             // No *new* file uploaded. Only update description if file record exists.
             $sqlDescUpdate = "UPDATE myth_files SET description = :desc WHERE myth_id = :mythId";
             $stmtDesc = $pdo->prepare($sqlDescUpdate);
             $stmtDesc->bindParam(':desc', $fileDescription, PDO::PARAM_STR);
             $stmtDesc->bindParam(':mythId', $mythId, PDO::PARAM_INT);
             // Execute this update. It's okay if it affects 0 rows if no file record exists.
             if (!$stmtDesc->execute()) {
                  // Log this error but maybe don't throw fatal exception? Or handle differently.
                  error_log("Non-fatal error updating file description for myth ID {$mythId}: " . implode(":", $stmtDesc->errorInfo()));
                  // Decide if this failure should stop the whole process or not.
                  // For now, we let it proceed.
             }
        }

        // --- Commit Transaction ---
        $pdo->commit();

        // --- Delete old file (if applicable) AFTER successful commit ---
        if ($newFileUploaded && $oldFilePath && file_exists($oldFilePath)) {
             if (!unlink($oldFilePath)) {
                 // Log failure to delete old file, but don't necessarily fail the whole operation
                 error_log("Warning: Failed to delete old file '{$oldFilePath}' for myth ID {$mythId} after update.");
                 $_SESSION['upload_warning'] = "Myth updated, but failed to delete the old file."; // Optional warning message
             } else {
                 error_log("Successfully deleted old file '{$oldFilePath}' for myth ID {$mythId}.");
             }
        }

        // 6. --- Success Feedback ---
        $_SESSION['upload_success'] = "Myth '" . htmlspecialchars($mythTitle) . "' updated successfully" . ($newFileUploaded ? " with new file." : ".");
        header('Location: viewMyths.php'); // Redirect to the list on success
        exit;

    } catch (Exception $e) {
        // --- Rollback Transaction on Error ---
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // --- Error Feedback ---
        $_SESSION['upload_error'] = "An error occurred during update: " . $e->getMessage();
        error_log("Myth Update Error (ID: {$mythId}): " . $e->getMessage());

        // --- Clean up NEWLY uploaded file if DB operation failed ---
        // Check $newFileUploaded flag and if the target file path was set and exists
        if ($newFileUploaded && isset($newTargetFilePath) && file_exists($newTargetFilePath)) {
            unlink($newTargetFilePath); // Delete the newly uploaded file that couldn't be recorded in DB
            error_log("Cleaned up newly uploaded file '{$newTargetFilePath}' after DB error for myth ID {$mythId}.");
        }

        header("Location: " . $errorRedirectTarget); // Redirect back to the edit form
        exit;
    }

} else {
    // Not a POST request
    $_SESSION['upload_error'] = "Invalid access method.";
    header('Location: viewMyths.php'); // Redirect to list
    exit;
}

?>