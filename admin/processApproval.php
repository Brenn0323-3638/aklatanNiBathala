<?php
session_start(); // Start session for messages and auth check

// 1. --- Essential Includes and Setup ---
require_once '../includes/db.php'; // Include DB connection

// --- Authentication Check ---
if (!isset($_SESSION['adminId'])) {
    // Use a generic error key if redirecting to login
    $_SESSION['login_error'] = "Please log in to manage submissions.";
    header("Location: adminLogin.php");
    exit();
}
// Get the ID of the admin performing the action
$adminId = $_SESSION['adminId'];

// --- Define Redirect Target ---
$redirectPage = 'pendingMyths.php'; // Go back to the pending list

// 2. --- Get and Validate Input from URL ---
$pendingId = null;
$action = null;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $pendingId = (int)$_GET['id'];
}
if (isset($_GET['action']) && in_array($_GET['action'], ['approve', 'reject'])) {
    $action = $_GET['action'];
}

// Redirect if ID or Action is invalid/missing
if ($pendingId === null || $action === null) {
    $_SESSION['pending_myth_error'] = "Invalid action or submission ID provided.";
    header('Location: ' . $redirectPage);
    exit;
}

// 3. --- Database Operation ---
$pdo = connect_db(); // Uses globals
if (!$pdo) {
    $_SESSION['pending_myth_error'] = "Database connection failed.";
    header('Location: ' . $redirectPage);
    exit;
}

// ==============================================
// --- APPROVE ACTION ---
// ==============================================
if ($action === 'approve') {
    try {
        // Start Transaction: Insert into myths AND update pending_myths
        $pdo->beginTransaction();

        // 1. Fetch the full details of the pending myth to be approved
        $sqlFetch = "SELECT * FROM pending_myths WHERE pending_id = :pendingId AND status = 'pending'"; // Ensure it's still pending
        $stmtFetch = $pdo->prepare($sqlFetch);
        $stmtFetch->bindParam(':pendingId', $pendingId, PDO::PARAM_INT);
        $stmtFetch->execute();
        $pendingData = $stmtFetch->fetch(PDO::FETCH_ASSOC);

        if (!$pendingData) {
            // Not found or already processed
            throw new Exception("Submission #{$pendingId} not found or is not pending approval.");
        }

        // 2. Insert the approved data into the main `myths` table
        // *** MODIFIED: REMOVED updated_at column and corresponding value (NOW()) ***
        $sqlInsertMyth = "INSERT INTO myths (title, content, user_id, created_at)
                          VALUES (:title, :content, :admin_user_id, :submission_time)"; // Removed updated_at and NOW()
        $stmtInsert = $pdo->prepare($sqlInsertMyth);

        $stmtInsert->bindParam(':title', $pendingData['title'], PDO::PARAM_STR);
        $stmtInsert->bindParam(':content', $pendingData['content'], PDO::PARAM_STR);
        $stmtInsert->bindParam(':admin_user_id', $adminId, PDO::PARAM_INT); // ID of admin approving
        $stmtInsert->bindParam(':submission_time', $pendingData['submission_timestamp'], PDO::PARAM_STR); // Use original submission time as creation time

        // No binding needed for NOW() or updated_at

        if (!$stmtInsert->execute()) {
            throw new Exception("Failed to insert approved myth into main table: " . implode(":", $stmtInsert->errorInfo()));
        }
        // $newMythId = $pdo->lastInsertId();

        // Optional TODO: Handle image_url if your myths table has it
        // if (!empty($pendingData['image_suggestion'])) {
        //    $sqlUpdateImage = "UPDATE myths SET image_url = :image_url WHERE id = :new_myth_id";
        //    // prepare, bind, execute
        // }


        // 3. Update the status of the entry in `pending_myths`
        $sqlUpdatePending = "UPDATE pending_myths SET
                                status = 'approved',
                                reviewed_by_admin_id = :adminId,
                                review_timestamp = NOW()
                             WHERE pending_id = :pendingId";
        $stmtUpdate = $pdo->prepare($sqlUpdatePending);
        $stmtUpdate->bindParam(':adminId', $adminId, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':pendingId', $pendingId, PDO::PARAM_INT);

        if (!$stmtUpdate->execute()) {
            throw new Exception("Failed to update pending myth status after approval: " . implode(":", $stmtUpdate->errorInfo()));
        }

        // 4. Commit Transaction
        $pdo->commit();
        $_SESSION['pending_myth_success'] = "Submission #" . $pendingId . " ('" . htmlspecialchars($pendingData['title']) . "') approved and added to the Aklatan.";

    } catch (Exception $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['pending_myth_error'] = "Error approving submission #{$pendingId}: " . $e->getMessage();
        error_log("Approve Myth Error (Pending ID: {$pendingId}, Admin ID: {$adminId}): " . $e->getMessage());
    }

// ==============================================
// --- REJECT ACTION ---
// ==============================================
} elseif ($action === 'reject') {
    try {
        // $pdo->beginTransaction(); // Optional transaction for single update

        $sqlUpdatePending = "UPDATE pending_myths SET
                                status = 'rejected',
                                reviewed_by_admin_id = :adminId,
                                review_timestamp = NOW()
                             WHERE pending_id = :pendingId AND status = 'pending'";
        $stmtUpdate = $pdo->prepare($sqlUpdatePending);
        $stmtUpdate->bindParam(':adminId', $adminId, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':pendingId', $pendingId, PDO::PARAM_INT);

        if (!$stmtUpdate->execute()) {
             throw new Exception("Error updating pending myth status for rejection: " . implode(":", $stmtUpdate->errorInfo()));
        }

        $affectedRows = $stmtUpdate->rowCount();

        // $pdo->commit(); // if using transaction

        if ($affectedRows > 0) {
            $_SESSION['pending_myth_success'] = "Submission #" . $pendingId . " was rejected.";
        } else {
             $_SESSION['pending_myth_error'] = "Submission #" . $pendingId . " could not be rejected (might be already processed or not found).";
        }

    } catch (Exception $e) {
        // if ($pdo->inTransaction()) { $pdo->rollBack(); } // If using transaction
        $_SESSION['pending_myth_error'] = "Error rejecting submission #{$pendingId}: " . $e->getMessage();
        error_log("Reject Myth Error (Pending ID: {$pendingId}, Admin ID: {$adminId}): " . $e->getMessage());
    }
}

// --- Redirect back to the pending list ---
header('Location: ' . $redirectPage);
exit;

?>