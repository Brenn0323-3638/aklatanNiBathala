<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Get and Validate Pending ID from URL ---
$pendingId = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $pendingId = (int)$_GET['id'];
} else {
    $_SESSION['pending_myth_error'] = "Invalid Submission ID provided.";
    header('Location: pendingMyths.php'); // Redirect back to list
    exit;
}

// --- Fetch Full Pending Submission Details ---
$submission = null;
$errorMessage = '';

try {
    $pdo = connect_db(); // Uses globals
    if ($pdo) {
        // Select all relevant columns for the specific pending entry
        // Also join with users table again
        $sql = "SELECT
                    pm.*, -- Select all columns from pending_myths
                    u.username AS submitter_username
                FROM
                    pending_myths pm
                LEFT JOIN
                    users u ON pm.submitted_by_user_id = u.id
                WHERE
                    pm.pending_id = :pendingId";
                    // Optional: AND pm.status = 'pending' (to ensure it hasn't been processed)
                    // However, viewing already processed might be useful, so let's allow it.

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':pendingId', $pendingId, PDO::PARAM_INT);
        $stmt->execute();
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$submission) {
            $_SESSION['pending_myth_error'] = "Submission not found for ID: " . $pendingId;
            header('Location: pendingMyths.php');
            exit;
        }
    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException | Exception $e) {
    $errorMessage = "Database error fetching submission details: " . $e->getMessage();
    error_log("View Pending Details Fetch Error (ID: {$pendingId}): " . $e->getMessage());
}

// --- Handle Session Messages (e.g., if redirected after failed approval) ---
if (isset($_SESSION['pending_myth_error'])) {
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['pending_myth_error'] : $_SESSION['pending_myth_error'];
    unset($_SESSION['pending_myth_error']);
}

?>

<!-- Main Content Area for Viewing Pending Details -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Review Myth Submission</h3>
                    <p>Details for Pending Entry #<?php echo $pendingId; ?></p>
                </div>
            </div>
             <div>
                 <a href="pendingMyths.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Pending List</a>
            </div>
        </div>

        <!-- Display Error Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <?php if ($submission && empty($errorMessage)): // Only show details if found and no error ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                 <h5 class="mb-0">Submission Details</h5>
                 <!-- Show current status -->
                 <span>Status:
                    <span class="badge bg-<?php
                        switch ($submission['status']) {
                            case 'pending': echo 'warning text-dark'; break;
                            case 'approved': echo 'success'; break;
                            case 'rejected': echo 'danger'; break;
                            default: echo 'secondary';
                        }
                    ?>"><?php echo ucfirst($submission['status']); ?></span>
                 </span>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Submitted Title:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($submission['title']); ?></dd>

                    <dt class="col-sm-3">Submitted Content:</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($submission['content'])); ?></dd>

                    <dt class="col-sm-3">Suggested Category:</dt>
                    <dd class="col-sm-9"><?php echo !empty($submission['category']) ? htmlspecialchars($submission['category']) : '<em>None provided</em>'; ?></dd>

                    <dt class="col-sm-3">Suggested Tags:</dt>
                    <dd class="col-sm-9"><?php echo !empty($submission['tags']) ? htmlspecialchars($submission['tags']) : '<em>None provided</em>'; ?></dd>

                    <dt class="col-sm-3">Source Info:</dt>
                    <dd class="col-sm-9"><?php echo !empty($submission['source_info']) ? nl2br(htmlspecialchars($submission['source_info'])) : '<em>None provided</em>'; ?></dd>

                    <dt class="col-sm-3">Image Suggestion:</dt>
                    <dd class="col-sm-9"><?php echo !empty($submission['image_suggestion']) ? htmlspecialchars($submission['image_suggestion']) : '<em>None provided</em>'; ?></dd>

                    <hr class="my-3">

                     <dt class="col-sm-3">Submitted By:</dt>
                    <dd class="col-sm-9">
                        <?php
                            if (!empty($submission['submitter_username'])) {
                                echo htmlspecialchars($submission['submitter_username']) . " (User ID: " . $submission['submitted_by_user_id'] . ")";
                            } elseif (!empty($submission['submitter_name'])) {
                                echo htmlspecialchars($submission['submitter_name']) . " (Guest)";
                                if (!empty($submission['submitter_email'])) {
                                    echo " - " . htmlspecialchars($submission['submitter_email']);
                                }
                            } else {
                                echo '<i>Anonymous</i>';
                            }
                        ?>
                    </dd>

                    <dt class="col-sm-3">Submission Date:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars(date('M d, Y \a\t H:i:s', strtotime($submission['submission_timestamp']))); ?></dd>

                    <?php if ($submission['status'] != 'pending'): // Show review info if not pending ?>
                         <hr class="my-3">
                         <dt class="col-sm-3">Reviewed By Admin ID:</dt>
                         <dd class="col-sm-9"><?php echo $submission['reviewed_by_admin_id'] ?? '<em>N/A</em>'; ?></dd>
                         <dt class="col-sm-3">Review Date:</dt>
                         <dd class="col-sm-9"><?php echo $submission['review_timestamp'] ? htmlspecialchars(date('M d, Y \a\t H:i:s', strtotime($submission['review_timestamp']))) : '<em>N/A</em>'; ?></dd>
                         <dt class="col-sm-3">Admin Notes:</dt>
                         <dd class="col-sm-9"><?php echo !empty($submission['admin_notes']) ? nl2br(htmlspecialchars($submission['admin_notes'])) : '<em>None</em>'; ?></dd>
                    <?php endif; ?>

                </dl>

                <?php if ($submission['status'] == 'pending'): // Show action buttons only if pending ?>
                    <hr class="my-4">
                    <div class="text-center">
                         <a href="processApproval.php?action=approve&id=<?php echo $submission['pending_id']; ?>" class="btn btn-success me-2" onclick="return confirm('APPROVE this entry and add it to the main Aklatan?');">
                            <i class="fas fa-check me-1"></i> Approve Submission
                         </a>
                         <a href="processApproval.php?action=reject&id=<?php echo $submission['pending_id']; ?>" class="btn btn-danger" onclick="return confirm('REJECT this entry? You can add notes later if needed.');">
                             <i class="fas fa-times me-1"></i> Reject Submission
                         </a>
                         <!-- Optional: Add Edit button that takes admin to a pre-filled addMyth.php form? -->
                         <!-- <a href="editPendingMyth.php?id=..." class="btn btn-warning ms-2">Edit Before Approving</a> -->
                    </div>
                <?php endif; ?>

            </div> <!-- End card-body -->
        </div> <!-- End card -->
        <?php endif; ?>

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>