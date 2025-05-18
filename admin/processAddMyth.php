<?php
session_start(); // Start session to store messages and get user ID

// 1. --- Essential Includes and Setup ---
require_once '../includes/db.php'; // Your database connection script
// require_once 'includes/auth_check.php'; // Your script to ensure the user is logged in AND is an admin

// Check if user is logged in and is an admin (Implement this!)


// CORRECTED CHECK: Make sure the admin ID exists in the session
if (!isset($_SESSION['adminId'])) { // Check if adminId is set
    // Redirect to login if adminId is NOT set
    // Optional: Set an error message for the login page
    // $_SESSION['login_error'] = "Please log in to add myths.";
    header("Location: adminLogin.php");
    exit();
}


// --- Configuration ---
// Define the target directory - IMPORTANT: Make sure this path is correct and writable!
$uploadDir = 'aklatanUploads/';
// Define allowed file extensions (example)
$allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
// Define maximum file size (example: 5MB)
$maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes

// --- Get Admin User ID (Assuming it's stored in session after login) ---
// !! Replace 'admin_user_id' with your actual session variable name !!
$adminUserId = isset($_SESSION['adminId']) ? $_SESSION['adminId'] : null;

if ($adminUserId === null) {
     // Handle case where admin user ID isn't found in session
     $_SESSION['upload_error'] = "Error: Admin user session not found. Please log in again.";
     header('Location: addMyth.php');
     exit;
}


// 2. --- Form Submission Check ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- CSRF Check (Optional but Recommended) ---
    /*
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['upload_error'] = "Invalid request (CSRF token mismatch). Please try again.";
        header('Location: addMyth.php');
        exit;
    }
    */

    // 3. --- Retrieve and Sanitize Text Input ---
    $mythTitle = trim($_POST['mythTitle'] ?? '');
    $mythContent = trim($_POST['mythContent'] ?? '');
    $fileDescription = trim($_POST['fileDescription'] ?? '');

    // --- Basic Validation ---
    if (empty($mythTitle)) {
        $_SESSION['upload_error'] = "Myth Title is required.";
        header('Location: addMyth.php');
        exit;
    }

    // 4. --- File Upload Handling ---
    $fileUploaded = false;
    $newMythId = null; // To store the ID of the inserted myth

    // Check if a file was actually uploaded without errors
    if (isset($_FILES['mythFile']) && $_FILES['mythFile']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['mythFile']['tmp_name'];
        $originalFilename = basename($_FILES['mythFile']['name']);
        $fileSize = $_FILES['mythFile']['size'];
        $fileType = $_FILES['mythFile']['type']; // MIME type
        $fileNameCmps = explode(".", $originalFilename);
        $fileExtension = strtolower(end($fileNameCmps));

        // --- File Validation ---
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['upload_error'] = "Invalid file type. Allowed types: " . implode(', ', $allowedExtensions);
            header('Location: addMyth.php');
            exit;
        }

        if ($fileSize > $maxFileSize) {
            $_SESSION['upload_error'] = "File is too large. Maximum size: " . ($maxFileSize / 1024 / 1024) . " MB.";
            header('Location: addMyth.php');
            exit;
        }

        // --- Generate a Unique and Secure Filename ---
        // Prevents overwriting files and potential security issues with user-provided names
        $storedFilename = uniqid('mythfile_', true) . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $storedFilename;

        // --- Move the Uploaded File ---
        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            $fileUploaded = true;
            // File moved successfully
        } else {
            // Failed to move file - check directory permissions!
            $_SESSION['upload_error'] = "Error moving uploaded file. Check server permissions for '$uploadDir'.";
            error_log("File upload error: Failed to move '{$fileTmpPath}' to '{$targetFilePath}'"); // Log for debugging
            header('Location: addMyth.php');
            exit;
        }

    } elseif (isset($_FILES['mythFile']) && $_FILES['mythFile']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors (e.g., UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE)
        $_SESSION['upload_error'] = "File upload error: Code " . $_FILES['mythFile']['error'];
        header('Location: addMyth.php');
        exit;
    }
    // Note: If UPLOAD_ERR_NO_FILE, we just proceed without file data.


    // 5. --- Database Operations ---
    $pdo = connect_db(); // Function from your database.php to get PDO connection
    if (!$pdo) {
        $_SESSION['upload_error'] = "Database connection error.";
        header('Location: addMyth.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // --- Insert into `myths` table ---
        $sqlMyth = "INSERT INTO myths (title, content, user_id, created_at) VALUES (:title, :content, :user_id, NOW())";
        $stmtMyth = $pdo->prepare($sqlMyth);
        $stmtMyth->bindParam(':title', $mythTitle, PDO::PARAM_STR);
        $stmtMyth->bindParam(':content', $mythContent, PDO::PARAM_STR);
        $stmtMyth->bindParam(':user_id', $adminUserId, PDO::PARAM_INT); // Use the admin's user ID

        if (!$stmtMyth->execute()) {
             throw new Exception("Error inserting myth: " . implode(":", $stmtMyth->errorInfo()));
        }

        $newMythId = $pdo->lastInsertId(); // Get the ID of the myth just inserted

        // --- Insert into `myth_files` table (only if a file was successfully uploaded) ---
        if ($fileUploaded && $newMythId) {
            $sqlFile = "INSERT INTO myth_files (myth_id, original_filename, stored_filename, file_path, file_size, description, upload_timestamp)
                        VALUES (:myth_id, :original_filename, :stored_filename, :file_path, :file_size, :description, NOW())";
            $stmtFile = $pdo->prepare($sqlFile);

            $filePathForDb = $targetFilePath; // Store the relative path used to move the file

            $stmtFile->bindParam(':myth_id', $newMythId, PDO::PARAM_INT);
            $stmtFile->bindParam(':original_filename', $originalFilename, PDO::PARAM_STR);
            $stmtFile->bindParam(':stored_filename', $storedFilename, PDO::PARAM_STR);
            $stmtFile->bindParam(':file_path', $filePathForDb, PDO::PARAM_STR);
            $stmtFile->bindParam(':file_size', $fileSize, PDO::PARAM_INT);
            $stmtFile->bindParam(':description', $fileDescription, PDO::PARAM_STR);

             if (!$stmtFile->execute()) {
                 throw new Exception("Error inserting myth file record: " . implode(":", $stmtFile->errorInfo()));
             }
        }

        // --- Commit Transaction ---
        $pdo->commit();

        // 6. --- Success Feedback ---
        $_SESSION['upload_success'] = "Myth '$mythTitle' added successfully" . ($fileUploaded ? " with file." : ".");
        header('Location: viewMyths.php'); // Redirect to the myth list
        exit;

    } catch (Exception $e) {
        // --- Rollback Transaction on Error ---
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // --- Error Feedback ---
        $_SESSION['upload_error'] = "An error occurred: " . $e->getMessage();
        error_log("Myth Add Error: " . $e->getMessage()); // Log detailed error

        // --- Clean up uploaded file if DB insertion failed ---
        if ($fileUploaded && isset($targetFilePath) && file_exists($targetFilePath)) {
            unlink($targetFilePath); // Delete the file that was moved but couldn't be recorded in DB
        }

        header('Location: addMyth.php'); // Redirect back to the form
        exit;
    }

} else {
    // Not a POST request, redirect away or show error
    $_SESSION['upload_error'] = "Invalid access method.";
    header('Location: addMyth.php');
    exit;
}

?>