<?php
session_start(); // Start session for messages and auth check

// 1. --- Essential Includes and Setup ---
require_once '../includes/db.php'; // Your database connection script

// --- Authentication Check ---
if (!isset($_SESSION['adminId'])) {
    $_SESSION['error_message'] = "Authentication required.";
    header("Location: adminLogin.php");
    exit();
}

// --- Configuration (need upload dir path if deleting files) ---
// Ensure this matches the path used in add/edit process scripts RELATIVE TO THIS SCRIPT
// Since deleteMyth.php is likely in 'admin/', and uploads in 'admin/aklatanUploads/', the relative path is correct.
$uploadDir = 'aklatanUploads/';

// 2. --- Get and Validate Myth ID ---
$mythId = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $mythId = (int)$_GET['id'];
} else {
    // Invalid or missing ID
    $_SESSION['upload_error'] = "Invalid Myth ID provided for deletion."; // Using 'upload_error' key for consistency
    header('Location: viewMyths.php');
    exit;
}

// 3. --- Database Operation ---
$pdo = connect_db();
if (!$pdo) {
    $_SESSION['upload_error'] = "Database connection error during deletion.";
    header('Location: viewMyths.php');
    exit;
}

$filePathToDelete = null; // Variable to store the path of the file to be deleted
$mythTitle = "Myth ID " . $mythId; // Default title for messages

try {
    // --- Step A: Fetch associated file path and myth title BEFORE deleting ---
    // We need the file path to delete the file later, and title for messages.
    // Using LEFT JOIN in case there's no associated file.
    $sqlFetch = "SELECT m.title, mf.file_path
                 FROM myths m
                 LEFT JOIN myth_files mf ON m.id = mf.myth_id
                 WHERE m.id = :mythId";
    $stmtFetch = $pdo->prepare($sqlFetch);
    $stmtFetch->bindParam(':mythId', $mythId, PDO::PARAM_INT);
    $stmtFetch->execute();
    $data = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        // Myth not found in DB, maybe already deleted?
        throw new Exception("Myth with ID {$mythId} not found.");
    }

    // Store data if found
    $mythTitle = $data['title'] ?? $mythTitle; // Update title if fetched
    if (!empty($data['file_path'])) {
        $filePathToDelete = $data['file_path'];
         // IMPORTANT: Construct the full server path if $filePathToDelete only stores relative path
         // If $filePathToDelete is like 'aklatanUploads/file.jpg' and deleteMyth.php is in the parent 'admin' folder,
         // the path might be correct as is. Double check based on your structure.
         // Example: $fullServerPath = __DIR__ . '/' . $filePathToDelete; // If relative to current script dir
         // Make sure $filePathToDelete points correctly to the file location on the server's filesystem.
    }
    $stmtFetch->closeCursor();


    // --- Step B: Start Transaction ---
    $pdo->beginTransaction();

    // --- Step C: Delete from `myth_files` table ---
    // It's safe to delete even if no record exists for this mythId
    $sqlDeleteFile = "DELETE FROM myth_files WHERE myth_id = :mythId";
    $stmtDeleteFile = $pdo->prepare($sqlDeleteFile);
    $stmtDeleteFile->bindParam(':mythId', $mythId, PDO::PARAM_INT);
    if (!$stmtDeleteFile->execute()) {
        throw new Exception("Error deleting associated file record: " . implode(":", $stmtDeleteFile->errorInfo()));
    }
    $filesDeletedCount = $stmtDeleteFile->rowCount(); // Number of file records deleted

    // --- Step D: Delete from `myths` table ---
    $sqlDeleteMyth = "DELETE FROM myths WHERE id = :mythId";
    $stmtDeleteMyth = $pdo->prepare($sqlDeleteMyth);
    $stmtDeleteMyth->bindParam(':mythId', $mythId, PDO::PARAM_INT);
    if (!$stmtDeleteMyth->execute()) {
        throw new Exception("Error deleting myth record: " . implode(":", $stmtDeleteMyth->errorInfo()));
    }
    $mythDeletedCount = $stmtDeleteMyth->rowCount(); // Should be 1 if successful

    // --- Step E: Commit Transaction ---
    if ($mythDeletedCount > 0) { // Only commit if the main myth record was actually deleted
        $pdo->commit();
        $dbSuccess = true;
    } else {
        // Myth wasn't found during delete attempt (maybe deleted between fetch and now?)
        // Rollback just in case something happened with myth_files delete
        $pdo->rollBack();
        throw new Exception("Myth with ID {$mythId} could not be deleted (might be already gone).");
    }

    // --- Step F: Delete Physical File (AFTER successful DB commit) ---
    $fileUnlinkSuccess = true; // Assume success if no file needed deletion
    if ($dbSuccess && $filePathToDelete) {
        if (file_exists($filePathToDelete)) {
            if (!unlink($filePathToDelete)) {
                // Failed to delete the file
                $fileUnlinkSuccess = false;
                error_log("Warning: Failed to delete file '{$filePathToDelete}' for deleted myth ID {$mythId}. Check permissions.");
                $_SESSION['upload_warning'] = "Myth deleted from database, but failed to delete the associated file '{$filePathToDelete}' from the server.";
            } else {
                 error_log("Successfully deleted file '{$filePathToDelete}' for deleted myth ID {$mythId}.");
            }
        } else {
            // File path was in DB, but file didn't exist on server (already deleted? or inconsistency?)
            error_log("Warning: File '{$filePathToDelete}' for deleted myth ID {$mythId} not found on server for unlinking.");
            // Optionally set a warning message
            // $_SESSION['upload_warning'] = "Myth deleted. Associated file was listed but not found on server.";
        }
    }

    // --- Step G: Success Feedback ---
    if ($dbSuccess && $fileUnlinkSuccess) {
        $_SESSION['upload_success'] = "Myth '" . htmlspecialchars($mythTitle) . "' and its associated file (if any) were successfully deleted.";
    } elseif ($dbSuccess && !$fileUnlinkSuccess) {
        // DB deleted, but file failed - warning already set
        $_SESSION['upload_success'] = "Myth '" . htmlspecialchars($mythTitle) . "' deleted from database, but there was an issue deleting the file."; // Modify success message slightly
    }
    // Redirect on success (or success with warning)
    header('Location: viewMyths.php');
    exit;

} catch (Exception $e) {
    // --- Rollback Transaction on Error during DB operations ---
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // --- Error Feedback ---
    $_SESSION['upload_error'] = "An error occurred during deletion: " . $e->getMessage();
    error_log("Myth Delete Error (ID: {$mythId}): " . $e->getMessage());

    // Redirect back to the list view on error
    header('Location: viewMyths.php');
    exit;
}
?>