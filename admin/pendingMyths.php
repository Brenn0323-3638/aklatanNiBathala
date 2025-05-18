<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Fetch Pending Myth Submissions ---
// ** Changed variable name to match loop below **
$pendingMyths = []; // Initialize an empty array
$errorMessage = '';
$successMessage = '';

try {
    $pdo = connect_db(); // Uses globals from db.php
    if ($pdo) {
        // Select relevant columns from pending_myths where status is 'pending'
        // Join with users table to get submitter's username if available
        $sql = "SELECT
                    pm.pending_id,
                    pm.title,
                    pm.content, -- ** Added content to fetch for excerpt **
                    pm.submission_timestamp,
                    pm.submitted_by_user_id,
                    pm.submitter_name,
                    u.username AS submitter_username
                FROM
                    pending_myths pm
                LEFT JOIN
                    users u ON pm.submitted_by_user_id = u.id -- Assuming users table PK is 'id'
                WHERE
                    pm.status = 'pending' -- Only fetch pending ones
                ORDER BY
                    pm.submission_timestamp ASC"; // Show oldest first

        $stmt = $pdo->query($sql);
        $pendingMyths = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException $e) {
    // Check if the error is "table not found"
    if (strpos($e->getMessage(), 'Base table or view not found') !== false && strpos($e->getMessage(), 'pending_myths') !== false) {
         $errorMessage = "The 'pending_myths' table does not seem to exist. Please create it first.";
    } else {
        $errorMessage = "Database error fetching pending myths: " . $e->getMessage();
    }
    error_log("Pending Myth Fetch Error: " . $e->getMessage());
}

// --- Handle Session Messages (Use distinct keys) ---
// ** Ensure these keys match what processApproval.php will set **
if (isset($_SESSION['pending_myth_success'])) {
    $successMessage = $_SESSION['pending_myth_success'];
    unset($_SESSION['pending_myth_success']);
}
if (isset($_SESSION['pending_myth_error'])) {
    // Append or set error message
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['pending_myth_error'] : $_SESSION['pending_myth_error'];
    unset($_SESSION['pending_myth_error']);
}

?>

<!-- Main Content Area for Pending Myths -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Myth Entry Requests</h3>
                    <p>Review user-submitted myth entries.</p>
                </div>
            </div>
             <div>
                <!-- Maybe add filter options later? -->
             </div>
        </div>

        <!-- *** CORRECTED Display Session Messages Block *** -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; // Display the error message (might contain HTML like <br> if table missing) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
         <!-- ******************************************** -->


        <!-- Pending Myths Listing Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pending Submissions</h5>
                 <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Content Excerpt</th> <!-- Added missing header from previous display logic -->
                                <th>Submitted By</th>
                                <th>Date Submitted</th>
                                <th style="width: 20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                             <!-- ** Ensure loop variable matches fetched variable ** -->
                            <?php if (!empty($pendingMyths)): ?>
                                <?php foreach ($pendingMyths as $myth): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($myth['title']); ?></td>
                                        <!-- ** Added content excerpt display ** -->
                                        <td><?php echo htmlspecialchars(substr($myth['content'] ?? '', 0, 100)) . (strlen($myth['content'] ?? '') > 100 ? '...' : ''); ?></td>
                                        <td>
                                            <?php
                                                // Display username if available, otherwise stored name, else 'Anonymous'
                                                if (!empty($myth['submitter_username'])) {
                                                    echo htmlspecialchars($myth['submitter_username']);
                                                } elseif (!empty($myth['submitter_name'])) {
                                                    echo htmlspecialchars($myth['submitter_name']) . " (Guest)";
                                                } else {
                                                    echo '<i>Anonymous</i>';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($myth['submission_timestamp']))); ?></td>
                                        <td>
                                            <!-- Link to a page/modal to view full details -->
                                            <a href="viewPendingDetails.php?id=<?php echo $myth['pending_id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i> <span class="d-none d-lg-inline">View</span>
                                            </a>
                                            <!-- Link to process approval -->
                                            <a href="processApproval.php?action=approve&id=<?php echo $myth['pending_id']; ?>" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Are you sure you want to APPROVE this entry and add it to the main Aklatan?');">
                                                <i class="fas fa-check"></i> <span class="d-none d-lg-inline">Approve</span>
                                            </a>
                                            <!-- Link to process rejection -->
                                            <a href="processApproval.php?action=reject&id=<?php echo $myth['pending_id']; ?>" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Are you sure you want to REJECT this entry?');">
                                                <i class="fas fa-times"></i> <span class="d-none d-lg-inline">Reject</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No pending myth submissions found.</td> <!-- Updated colspan -->
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                 </div> <!-- End table-responsive -->
            </div> <!-- End card-body -->
        </div> <!-- End card -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>