<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// --- Initialize Variables ---
$quizzes = [];
$errorMessage = '';
$successMessage = '';

// --- Handle Sorting ---
$sortOptions = [
    'newest' => 'Date Created (Newest First)',
    'oldest' => 'Date Created (Oldest First)',
    'title_asc' => 'Title (A-Z)',
    'title_desc' => 'Title (Z-A)',
    'active_first' => 'Status (Active First)',
    'inactive_first' => 'Status (Inactive First)'
];
$currentSort = 'newest'; // Default sort order
$orderByClause = 'ORDER BY created_at DESC'; // Default SQL ORDER BY

if (isset($_GET['sort']) && array_key_exists($_GET['sort'], $sortOptions)) {
    $currentSort = $_GET['sort'];
    switch ($currentSort) {
        case 'title_asc':
            $orderByClause = 'ORDER BY title ASC';
            break;
        case 'title_desc':
            $orderByClause = 'ORDER BY title DESC';
            break;
        case 'oldest':
             $orderByClause = 'ORDER BY created_at ASC';
            break;
        case 'active_first':
            $orderByClause = 'ORDER BY is_active DESC, title ASC'; // DESC because 1 (active) > 0 (inactive)
            break;
        case 'inactive_first':
            $orderByClause = 'ORDER BY is_active ASC, title ASC'; // ASC because 0 (inactive) < 1 (active)
            break;
        case 'newest':
        default:
            $orderByClause = 'ORDER BY created_at DESC'; // Default fallback
            break;
    }
}
// --- End Sorting Handling ---


// --- Fetch Existing Quizzes ---
try {
    $pdo = connect_db();
    if ($pdo) {
        // Select necessary columns from the quizzes table
        // MODIFIED: Appended $orderByClause
        $sql = "SELECT quiz_id, title, description, is_active, created_at
                FROM quizzes
                {$orderByClause}"; // Use the dynamic ORDER BY clause

        $stmt = $pdo->query($sql); // Simple query, no parameters needed here
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException $e) {
    // Check if the error is "table not found"
    if (strpos($e->getMessage(), 'Base table or view not found') !== false && strpos($e->getMessage(), 'quizzes') !== false) {
         $errorMessage = "The 'quizzes' table does not seem to exist. Please create it first.";
    } else {
        $errorMessage = "Database error fetching quizzes: " . $e->getMessage();
    }
    error_log("Quiz Fetch Error: " . $e->getMessage());
}

// --- Handle Session Messages (Use distinct keys for quiz messages) ---
if (isset($_SESSION['quiz_success'])) {
    $successMessage = $_SESSION['quiz_success'];
    unset($_SESSION['quiz_success']);
}
if (isset($_SESSION['quiz_error'])) {
    $errorMessage = $errorMessage ? $errorMessage . "<br>" . $_SESSION['quiz_error'] : $_SESSION['quiz_error']; // Append or set
    unset($_SESSION['quiz_error']);
}

?>

<!-- Main Content Area for Managing Quizzes -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Manage Quizzes</h3>
                    <p>Create, edit, and manage quizzes for users.</p>
                </div>
            </div>
             <div>
                 <a href="addQuiz.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Quiz</a>
            </div>
        </div>

        <!-- Display Session Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; // Don't escape HTML if adding line breaks ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Quiz Listing Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Existing Quizzes</h5>
                    <!-- Sorting Controls -->
                    <form action="manageQuiz.php" method="GET" class="d-flex align-items-center" style="max-width: 320px;">
                        <label for="sortSelect" class="form-label me-2 mb-0 text-nowrap small">Sort by:</label>
                        <select class="form-select form-select-sm" id="sortSelect" name="sort" onchange="this.form.submit()">
                            <?php foreach ($sortOptions as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php if ($currentSort === $key) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($value); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn-sm btn-secondary ms-2">Sort</button></noscript>
                    </form>
                    <!-- End Sorting Controls -->
                </div>

                 <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($quizzes)): ?>
                                <?php foreach ($quizzes as $quiz): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($quiz['description'] ?? '', 0, 100)) . (strlen($quiz['description'] ?? '') > 100 ? '...' : ''); ?></td>
                                        <td>
                                            <?php if ($quiz['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($quiz['created_at']))); ?></td>
                                        <td>
                                            <a href="manageQuestions.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-info" title="Manage Questions">
                                                <i class="fas fa-list-ol"></i> <span class="d-none d-md-inline">Questions</span>
                                            </a>
                                            <a href="editQuiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-warning" title="Edit Quiz Settings">
                                                <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                                            </a>
                                            <a href="deleteQuiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-danger" title="Delete Quiz" onclick="return confirm('Are you sure you want to delete this quiz and ALL its questions/answers? This cannot be undone.');">
                                                <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                     <td colspan="5" class="text-center">
                                        <?php
                                        if (!empty($_GET['sort']) && empty($quizzes)) {
                                            echo 'No quizzes found for the selected sort option.';
                                        } else {
                                            echo 'No quizzes found. Click "Add New Quiz" to create one.';
                                        }
                                        ?>
                                    </td>
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