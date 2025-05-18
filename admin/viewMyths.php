<?php
// No session check needed here, it's in the header
require_once 'adminHeader.php'; // Includes session check, doctype, head, sidebar
require_once '../includes/db.php';

// --- Initialize Variables ---
$myths = [];
$errorMessage = '';

// --- Handle Sorting ---
$sortOptions = [
    'newest' => 'Date Added (Newest First)',
    'oldest' => 'Date Added (Oldest First)',
    'title_asc' => 'Title (A-Z)',
    'title_desc' => 'Title (Z-A)'
];
$currentSort = 'newest';
$orderByClause = 'ORDER BY m.created_at DESC, m.title ASC';

if (isset($_GET['sort']) && array_key_exists($_GET['sort'], $sortOptions)) {
    $currentSort = $_GET['sort'];
    switch ($currentSort) {
        case 'title_asc':
            $orderByClause = 'ORDER BY m.title ASC';
            break;
        case 'title_desc':
            $orderByClause = 'ORDER BY m.title DESC';
            break;
        case 'oldest':
             $orderByClause = 'ORDER BY m.created_at ASC';
            break;
        case 'newest':
        default:
            $orderByClause = 'ORDER BY m.created_at DESC, m.title ASC';
            break;
    }
}

// --- Fetch Myths with Associated File Info ---
try {
    $pdo = connect_db();
    if ($pdo) {
        $sql = "SELECT
                    m.id,
                    m.title,
                    m.content, 
                    m.created_at,
                    u.username as author_username,
                    mf.original_filename,
                    mf.file_path,
                    mf.description as file_description
                FROM
                    myths m
                LEFT JOIN
                    myth_files mf ON m.id = mf.myth_id
                LEFT JOIN 
                    users u ON m.user_id = u.id
                {$orderByClause}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $myths = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
    error_log("Admin View Myths Error: " . $e->getMessage());
}
?>

<!-- Main Content Area Specific to Viewing Myths -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
         <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Myth Entries</h3>
                    <p>View and manage myths and legends.</p>
                </div>
            </div>
             <div>
                 <a href="addMyth.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Myth</a>
            </div>
        </div>

        <!-- Display potential error message -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Myth Listing Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List of Myths</h5>
                    <form action="viewMyths.php" method="GET" class="d-flex align-items-center" style="max-width: 300px;">
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
                </div>

                 <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>File Attachment</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($myths)): ?>
                                <?php foreach ($myths as $myth): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($myth['title']); ?></td>
                                        <td><?php echo htmlspecialchars($myth['author_username'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($myth['file_path']) && !empty($myth['original_filename'])): ?>
                                                <?php
                                                    // $myth['file_path'] from DB is like: "aklatanUploads/filename.ext"
                                                    // viewMyths.php is in "admin/" directory.
                                                    // If aklatanUploads is also in "admin/", then the path is correct as is.
                                                    $rawFilePathFromDb = $myth['file_path'];
                                                    $actualLinkPath = htmlspecialchars($rawFilePathFromDb); 

                                                    // OLD LOGIC, if aklatanUploads was at project root:
                                                    // $actualLinkPath = '../' . htmlspecialchars($rawFilePathFromDb);

                                                    $fileName = htmlspecialchars($myth['original_filename']);
                                                    $fileDescription = !empty($myth['file_description']) ? htmlspecialchars($myth['file_description']) : '';
                                                ?>
                                                <a href="<?php echo $actualLinkPath; ?>" target="_blank" title="<?php echo $fileDescription ?: $fileName; ?>">
                                                    <i class="fas fa-file-alt me-1"></i><?php echo $fileName; ?>
                                                </a>
                                                <?php if ($fileDescription): ?>
                                                    <small class="d-block text-muted">(<?php echo $fileDescription; ?>)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($myth['created_at']))); ?></td>
                                        <td>
                                            <a href="editMyth.php?id=<?php echo $myth['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="deleteMyth.php?id=<?php echo $myth['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this myth and its associated file(s)?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No myths found. <?php if(empty($_GET['sort']) && empty($errorMessage)) echo 'Add one!'; else echo 'Try a different sort option.'; ?></td>
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
require_once 'adminFooter.php';
?>