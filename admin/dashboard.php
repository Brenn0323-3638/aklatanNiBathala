<?php
// No session check needed here, it's in the header
require_once 'adminHeader.php'; // Includes session check, doctype, head, sidebar
require_once '../includes/db.php'; // <<<--- ADDED: Include DB connection

// --- Initialize Counts ---
$mythCount = 0;
$userCount = 0;
$activeQuizCount = 0;
$dbErrorMessage = ''; // To store potential DB errors

// --- Fetch Counts from Database ---
try {
    $pdo = connect_db();
    if ($pdo) {
        // 1. Count Mythical Entries
        $sqlMyth = "SELECT COUNT(*) FROM myths";
        $stmtMyth = $pdo->query($sqlMyth);
        $mythCount = $stmtMyth->fetchColumn(); // Fetch the single count value

        // 2. Count Registered Users (Assuming all users in 'users' table count)
        $sqlUser = "SELECT COUNT(*) FROM users";
        $stmtUser = $pdo->query($sqlUser);
        $userCount = $stmtUser->fetchColumn();

        // 3. Count Active Quizzes
        $sqlQuiz = "SELECT COUNT(*) FROM quizzes WHERE is_active = 1"; // Count only where is_active is true (1)
        $stmtQuiz = $pdo->query($sqlQuiz);
        $activeQuizCount = $stmtQuiz->fetchColumn();

    } else {
        $dbErrorMessage = "Database connection failed.";
    }
} catch (PDOException $e) {
    // Log the error and set a message
    error_log("Dashboard Count Fetch Error: " . $e->getMessage());
    $dbErrorMessage = "Could not fetch dashboard data.";
    // Optionally set counts to 'Error' or keep them at 0
    $mythCount = 'N/A';
    $userCount = 'N/A';
    $activeQuizCount = 'N/A';
}

?>

<!-- Main Content Area Specific to Dashboard Home -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Welcome, Admin</h3>
                    <p>Here to manage our aklatan?</p>
                </div>
            </div>
            <div>
                <p id="clock" class="fs-5 text-muted mb-0"></p> <!-- Clock JS will populate this -->
            </div>
        </div>

        <!-- Display DB Error if any -->
        <?php if (!empty($dbErrorMessage)): ?>
            <div class="alert alert-warning" role="alert">
                <?php echo htmlspecialchars($dbErrorMessage); ?>
            </div>
        <?php endif; ?>


        <!-- Stats Cards -->
        <div class="row g-4">
            <!-- Card 1: Mythical Entries -->
            <div class="col-lg-4 col-md-6"> <!-- Adjusted grid for better spacing -->
                <div class="card text-center h-100"> <!-- Use h-100 for equal height cards -->
                    <div class="card-body">
                        <i class="fas fa-book fa-2x mb-3 text-primary"></i>
                        <h5 class="card-title">Mythical Entries</h5>
                        <!-- Display actual count -->
                        <p class="card-text display-4 fw-bold"><?php echo htmlspecialchars($mythCount); ?></p>
                    </div>
                </div>
            </div>
            <!-- Card 2: Registered Users -->
            <div class="col-lg-4 col-md-6">
                <div class="card text-center h-100">
                     <div class="card-body">
                       <i class="fas fa-users fa-2x mb-3 text-success"></i>
                       <h5 class="card-title">Registered Users</h5>
                       <!-- Display actual count -->
                       <p class="card-text display-4 fw-bold"><?php echo htmlspecialchars($userCount); ?></p>
                    </div>
                </div>
            </div>
            <!-- Card 3: Active Quizzes (MODIFIED) -->
            <div class="col-lg-4 col-md-6">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <!-- Changed Icon, Title, and Text Color -->
                        <i class="fas fa-clipboard-check fa-2x mb-3 text-info"></i> <!-- Example: clipboard-check icon, info color -->
                        <h5 class="card-title">Active Quizzes</h5>
                        <!-- Display actual count -->
                        <p class="card-text display-4 fw-bold"><?php echo htmlspecialchars($activeQuizCount); ?></p>
                    </div>
                </div>
            </div>
        </div> <!-- End row -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->
<!-- NOTE: The closing div for main-content is now in admin_footer.php -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>