<?php
require_once 'adminHeader.php'; // Includes session check
require_once '../includes/db.php'; // Include DB connection

// 1. Get and Validate Myth ID from URL
$mythId = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $mythId = (int)$_GET['id'];
} else {
    // Invalid or missing ID, redirect with error
    $_SESSION['error_message'] = "Invalid Myth ID provided."; // Use a consistent session key maybe?
    header('Location: viewMyths.php');
    exit;
}

// 2. Fetch Myth and File Data from Database
$mythData = null;
$errorMessage = '';
$pdo = connect_db();

if ($pdo) {
    try {
        $sql = "SELECT
                    m.id, m.title, m.content,
                    mf.file_id, -- <<< Corrected column name
                    mf.original_filename,
                    mf.stored_filename,
                    mf.file_path,
                    mf.description as file_description
                FROM myths m
                LEFT JOIN myth_files mf ON m.id = mf.myth_id
                WHERE m.id = :mythId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mythId', $mythId, PDO::PARAM_INT);
        $stmt->execute();
        $mythData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mythData) {
            // Myth ID exists but not found in DB
            $_SESSION['error_message'] = "Myth not found for ID: " . $mythId;
            header('Location: viewMyths.php');
            exit;
        }

    } catch (PDOException $e) {
        $errorMessage = "Database error fetching myth data: " . $e->getMessage();
        error_log("Myth Fetch Error (Edit): " . $e->getMessage());
        // Optionally redirect, or just show error on this page
        // header('Location: viewMyths.php'); exit;
    }
} else {
    $errorMessage = "Database connection failed.";
}

// Optional: Check for error messages from processEditMyth.php
if (isset($_SESSION['upload_error'])) {
    $errorMessage = $_SESSION['upload_error']; // Overwrite or append?
    unset($_SESSION['upload_error']);
}
// Optional: Check for success (shouldn't happen on load, but maybe after failed validation?)
$success_message = '';
// if (isset($_SESSION['upload_success'])) { ... }

?>

<!-- Main Content Area for Editing Myth -->
<div class="main-content" id="main">
    <div class="p-4">
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h3>Edit Myth</h3>
                    <p>Modify details for: <?php echo $mythData ? htmlspecialchars($mythData['title']) : 'Myth ID ' . $mythId; ?></p>
                </div>
            </div>
             <div>
                 <a href="viewMyths.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            </div>
        </div>

        <!-- Display Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if ($mythData && empty($errorMessage)): // Only show form if data loaded successfully ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Myth Details</h5>

                <!-- The Edit Form -->
                <form action="processEditMyth.php" method="POST" enctype="multipart/form-data">
                    <!-- *** IMPORTANT: Hidden field to pass the Myth ID *** -->
                    <input type="hidden" name="mythId" value="<?php echo $mythData['id']; ?>">

                    <div class="mb-3">
                        <label for="mythTitle" class="form-label">Myth Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mythTitle" name="mythTitle" required value="<?php echo htmlspecialchars($mythData['title']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="mythContent" class="form-label">Myth Content / Description</label>
                        <textarea class="form-control" id="mythContent" name="mythContent" rows="5"><?php echo htmlspecialchars($mythData['content']); ?></textarea>
                    </div>

                    <hr> <!-- Separator for file section -->

                    <h6 class="card-subtitle mb-2 text-muted">File Attachment</h6>

                    <?php if (!empty($mythData['file_path'])): ?>
                        <div class="mb-3 p-3 border rounded bg-light">
                            <p class="mb-1"><strong>Current File:</strong></p>
                            <p class="mb-2">
                                <i class="fas fa-file-alt me-1"></i>
                                <a href="<?php echo htmlspecialchars($mythData['file_path']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($mythData['original_filename']); ?>
                                </a>
                            </p>
                            <p class="mb-1"><small><strong>Current Description:</strong> <?php echo !empty($mythData['file_description']) ? htmlspecialchars($mythData['file_description']) : '<em>(None)</em>'; ?></small></p>
                        </div>
                        <p class="form-text">Uploading a new file below will <strong class="text-danger">replace</strong> the current file.</p>
                    <?php else: ?>
                        <p class="mb-3">No file currently associated with this myth.</p>
                    <?php endif; ?>


                    <div class="mb-3">
                        <label for="mythFile" class="form-label">Upload New File (Optional)</label>
                        <input class="form-control" type="file" id="mythFile" name="mythFile">
                        <div id="fileHelp" class="form-text">Select a new document or image to replace the existing one (if any).</div>
                    </div>

                    <div class="mb-3">
                        <label for="fileDescription" class="form-label">File Description (Optional)</label>
                        <input type="text" class="form-control" id="fileDescription" name="fileDescription" placeholder="E.g., Manuscript Scan, Artist Rendition" value="<?php echo !empty($mythData['file_description']) ? htmlspecialchars($mythData['file_description']) : ''; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
                 <!-- End Form -->

            </div> <!-- End Card Body -->
        </div> <!-- End Card -->
        <?php endif; ?>

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>