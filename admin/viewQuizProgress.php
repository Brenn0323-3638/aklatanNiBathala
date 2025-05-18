<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Fetch Registered Users (excluding admins, for example) ---
$users = [];
$errorMessage = '';
$successMessage = ''; // For potential future use

try {
    $pdo = connect_db(); // Uses globals
    if ($pdo) {
        // Select users - you might want to exclude 'admin' role
        $sql = "SELECT id, username, first_name, last_name, email, created_at
                FROM users
                WHERE role = 'user' -- Only show regular users
                ORDER BY username ASC"; // Or by registration date, name, etc.

        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException | Exception $e) {
    // Check if the error is "table not found"
    if (strpos($e->getMessage(), 'Base table or view not found') !== false && strpos($e->getMessage(), 'users') !== false) {
         $errorMessage = "The 'users' table does not seem to exist.";
    } else {
        $errorMessage = "Database error fetching users: " . $e->getMessage();
    }
    error_log("View Quiz Progress (User List) Fetch Error: " . $e->getMessage());
}

// --- Handle Session Messages (if needed later) ---
// if (isset($_SESSION['quiz_progress_success'])) { ... }
// if (isset($_SESSION['quiz_progress_error'])) { ... }

?>

<!-- Main Content Area for Viewing Quiz Progress -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>User Quiz Progress</h3>
                    <p>Select a user to view their quiz attempts and scores.</p>
                </div>
            </div>
             <div>
                <!-- Optional: Add filters later (e.g., search user) -->
             </div>
        </div>

        <!-- Display Session Messages (if any) -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; // Might contain HTML ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- User Listing Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Registered Users</h5>
                 <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user):
                                    $fullName = trim(htmlspecialchars($user['first_name'] ?? '') . ' ' . htmlspecialchars($user['last_name'] ?? ''));
                                    $displayName = !empty($fullName) ? $fullName : '<em>N/A</em>';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo $displayName; ?></td>
                                        <td><?php echo htmlspecialchars($user['email'] ?? '<em>N/A</em>'); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?></td>
                                        <td>
                                            <a href="viewUserAttempts.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="View Attempts">
                                                <i class="fas fa-tasks"></i> <span class="d-none d-lg-inline">View Attempts</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No users found (excluding administrators).</td>
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