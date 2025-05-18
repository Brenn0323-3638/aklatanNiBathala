<?php
// Include the common user header (starts session if needed)
require_once 'header.php';
// Include DB connection
require_once 'includes/db.php'; // db.php defines connect_db() using globals

// --- Initialize Variables ---
$myth = null; // To store the fetched myth data
$errorMessage = '';
$mythId = null;

// --- Get and Validate Myth ID from URL ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    $mythId = (int)$_GET['id'];
} else {
    // Invalid or missing ID, maybe redirect to myths list or show error
    $errorMessage = "Invalid Myth ID specified.";
    // Optional: header('Location: myths.php'); exit;
}

// --- Fetch Specific Myth Details (if ID is valid) ---
if ($mythId && empty($errorMessage)) {
    try {
        $pdo = connect_db(); // Call without arguments

        if ($pdo) {
            // Select details for the specific myth ID
            // Also fetch associated file details if they exist
            $sql = "SELECT
                        m.id,
                        m.title,
                        m.content,
                        m.created_at, -- Or updated_at
                        u.username AS author_username, -- Get username of admin who added it
                        mf.file_path,
                        mf.original_filename,
                        mf.description as file_description,
                        mf.file_size
                    FROM
                        myths m
                    LEFT JOIN
                        users u ON m.user_id = u.id -- Join users table to get author (assuming user_id FK)
                    LEFT JOIN
                        myth_files mf ON m.id = mf.myth_id
                    WHERE
                        m.id = :mythId";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':mythId', $mythId, PDO::PARAM_INT);
            $stmt->execute();
            $myth = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$myth) {
                $errorMessage = "Myth not found."; // Myth ID doesn't exist in DB
            }

        } else {
            $errorMessage = "Database connection failed.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error fetching myth details: " . $e->getMessage();
        error_log("View Myth Details Error (ID: {$mythId}): " . $e->getMessage());
    } catch (Exception $e) {
        $errorMessage = "Could not fetch myth details due to a configuration issue: " . $e->getMessage();
        error_log("View Myth Details Error (ID: {$mythId}): " . $e->getMessage());
    }
} // end if mythId is valid

?>

<!-- Display Error Message if needed -->
<?php if (!empty($errorMessage)): ?>
    <div class="container mt-4"> <!-- Add container for proper layout -->
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?> <a href="myths.php" class="alert-link">Return to Myth List</a>.
        </div>
    </div>
<?php elseif ($myth): // Only display content if myth was found and no error occurred ?>

    <?php
        // --- Prepare data for display ---
        $mythTitle = htmlspecialchars($myth['title']);
        // Format content - nl2br converts newlines to <br>, then sanitize
        $mythContent = nl2br(htmlspecialchars($myth['content'] ?? ''));
        $authorUsername = htmlspecialchars($myth['author_username'] ?? 'Aklatan Admin'); // Default if author unknown
        $dateAdded = date('F j, Y', strtotime($myth['created_at']));

        // Prepare image/file data
        $imageUrl = null;
        $fileUrl = null;
        $fileName = null;
        $fileDescription = null;

        if (!empty($myth['file_path'])) {
             $filePath = 'admin/' . htmlspecialchars($myth['file_path']); // Prepend admin/ assuming path is relative
             $imgExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
             $fileExt = strtolower(pathinfo($myth['file_path'], PATHINFO_EXTENSION));

             if (in_array($fileExt, $imgExtensions)) {
                 $imageUrl = $filePath; // Use as image if it's an image type
             } else {
                 $fileUrl = $filePath; // Use as a general file link otherwise
                 $fileName = htmlspecialchars($myth['original_filename'] ?? 'Download File');
                 $fileDescription = htmlspecialchars($myth['file_description'] ?? '');
             }
        }
        // Use placeholder if no specific image was found/applicable
        if (!$imageUrl) {
             global $base_asset_url; // Make variable from header available
             $placeholderImageUrl = ($base_asset_url ?? '') . '/images/placeholder_myth_large.png'; // Use a potentially larger placeholder
        }
    ?>

    <!-- Myth Detail Content -->
    <article class="container my-4 content-section">
        <!-- Optional Header Image -->
        <?php if ($imageUrl): ?>
            <img src="<?php echo $imageUrl; ?>" alt="<?php echo $mythTitle; ?> illustration" class="img-fluid rounded mb-4" style="max-height: 400px; width: 100%; object-fit: cover;">
        <?php elseif (isset($placeholderImageUrl)): ?>
             <img src="<?php echo $placeholderImageUrl; ?>" alt="Placeholder image" class="img-fluid rounded mb-4" style="max-height: 350px; width: 100%; object-fit: cover; opacity: 0.7;">
        <?php endif; ?>

        <!-- Title -->
        <h1 class="display-5 fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #2e3a59;">
            <?php echo $mythTitle; ?>
        </h1>

        <!-- Meta Information -->
        <div class="text-muted mb-4 border-bottom pb-2">
            <small>
                <i class="fas fa-user-edit me-1"></i> Contributed by: <?php echo $authorUsername; ?> |
                <i class="fas fa-calendar-alt ms-2 me-1"></i> Added: <?php echo $dateAdded; ?>
            </small>
        </div>

        <!-- Main Content -->
        <div class="myth-content lh-lg"> <!-- Increased line height for readability -->
            <?php echo $mythContent; // Already includes <br> from nl2br ?>
        </div>

         <!-- Associated File Download (if not used as image) -->
         <?php if ($fileUrl): ?>
            <hr class="my-4">
            <h5><i class="fas fa-paperclip me-2"></i>Associated File</h5>
            <p>
                <a href="<?php echo $fileUrl; ?>" class="btn btn-outline-secondary btn-sm" target="_blank" download="<?php echo htmlspecialchars($myth['original_filename'] ?? ''); ?>">
                   <i class="fas fa-download me-1"></i> Download <?php echo $fileName; ?>
                </a>
                <?php if ($fileDescription): ?>
                    <br><small class="text-muted ms-1">(<?php echo $fileDescription; ?>)</small>
                <?php endif; ?>
            </p>
         <?php endif; ?>


        <!-- Back Button -->
        <hr class="my-4">
        <a href="myths.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Myth List</a>

    </article>

<?php endif; // End check if myth data exists ?>

<?php require_once 'footer.php'; ?>