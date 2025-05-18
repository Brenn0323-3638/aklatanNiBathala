<?php
// Include the common user header (starts session if needed)
require_once 'header.php';
require_once 'includes/db.php'; // db.php defines connect_db() using globals

// --- Initialize Variables ---
$myths = [];
$errorMessage = '';
$successMessage = '';

// *** ADDED: Handle Sorting ***
$sortOptions = [
    'newest' => 'Newest First',
    'oldest' => 'Oldest First',
    'title_asc' => 'Title (A-Z)',
    'title_desc' => 'Title (Z-A)'
];
// Default sort order
$currentSort = 'newest'; // Default to newest myth first
$orderByClause = 'ORDER BY m.created_at DESC'; // Default SQL ORDER BY

if (isset($_GET['sort']) && isset($sortOptions[$_GET['sort']])) {
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
            $orderByClause = 'ORDER BY m.created_at DESC';
            break;
    }
}
// ***************************

// --- CHECK FOR SUCCESS MESSAGE FROM SESSION ---
if (isset($_SESSION['submit_success'])) {
    $successMessage = $_SESSION['submit_success'];
    unset($_SESSION['submit_success']); // Clear after reading
}

// --- Fetch Approved Myths ---
try {
    $pdo = connect_db(); // Call without arguments

    if ($pdo) {
        // *** MODIFIED: Added dynamic $orderByClause ***
        $sql = "SELECT
                    m.id,
                    m.title,
                    m.content,
                    m.created_at, -- Need this for sorting
                    mf.file_path,
                    mf.original_filename
                FROM
                    myths m
                LEFT JOIN
                    myth_files mf ON m.id = mf.myth_id
                $orderByClause"; // Append the dynamic ORDER BY clause

        error_log("Fetching myths with SQL: " . $sql); // Log the query for debugging sort
        $stmt = $pdo->prepare($sql); // Use prepare for safety, even without user input here
        $stmt->execute();
        $myths = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $errorMessage = "Database connection failed.";
    }
} catch (PDOException $e) {
     if (strpos($e->getMessage(), 'Base table or view not found') !== false && strpos($e->getMessage(), 'myths') !== false) {
         $errorMessage = "Could not find myth data.";
         error_log("Myth Listing Error: 'myths' table not found.");
    } else {
        $errorMessage = "Database error fetching myths: " . $e->getMessage();
        error_log("Myth Listing Error: " . $e->getMessage());
    }
} catch (Exception $e) {
    $errorMessage = "Could not fetch myth data due to a configuration issue: " . $e->getMessage();
    error_log("Myth Listing Error: " . $e->getMessage());
}


?>

<!-- Page Title Header -->
<div class="text-center my-4">
    <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #2e3a59;">Myth Entries</h1>
    <p class="lead text-muted">Explore the collected tales from the Aklatan.</p>
</div>

<!-- Display Success Message -->
<?php if (!empty($successMessage)): ?>
    <div class="container mb-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Display Error Message -->
<?php if (!empty($errorMessage)): ?>
    <div class="container">
        <div class="alert alert-warning" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?> Please try again later.
        </div>
    </div>
<?php endif; ?>

<!-- *** ADD SORTING CONTROLS *** -->
<div class="container mb-4">
    <div class="row justify-content-end">
        <div class="col-md-6 col-lg-4">
             <form action="myths.php" method="GET" class="d-flex align-items-center">
                 <label for="sortSelect" class="form-label me-2 mb-0 text-nowrap">Sort by:</label>
                 <select class="form-select form-select-sm" id="sortSelect" name="sort" onchange="this.form.submit()">
                     <?php foreach ($sortOptions as $key => $value): ?>
                         <option value="<?php echo $key; ?>" <?php if ($currentSort === $key) echo 'selected'; ?>>
                             <?php echo $value; ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                 <noscript><button type="submit" class="btn btn-sm btn-secondary ms-2">Sort</button></noscript>
             </form>
        </div>
    </div>
</div>
<!-- ************************** -->


<!-- Myth Listing - Using Bootstrap Cards -->
<div class="container mb-5"> <?php // Added container wrapper ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        <?php if (!empty($myths)): ?>
            <?php foreach ($myths as $myth): ?>
                <?php
                // Prepare data (Unchanged)
                $mythId = $myth['id'];
                $mythTitle = htmlspecialchars($myth['title']);
                $mythExcerpt = htmlspecialchars(substr(strip_tags($myth['content'] ?? ''), 0, 150)) . (strlen(strip_tags($myth['content'] ?? '')) > 150 ? '...' : '');
                $imageUrl = null;
                if (!empty($myth['file_path'])) {
                     $imgExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                     $fileExt = strtolower(pathinfo($myth['file_path'], PATHINFO_EXTENSION));
                     if (in_array($fileExt, $imgExtensions)) {
                         $imageUrl = 'admin/' . htmlspecialchars($myth['file_path']);
                     }
                }
                 if (!$imageUrl) {
                     global $base_asset_url;
                     $imageUrl = ($base_asset_url ?? '') . '/images/placeholder_myth.png';
                 }
                $detailUrl = "viewMythDetails.php?id=" . $mythId;
                ?>
                <div class="col">
                    <?php // Added scroll-fade-in class for consistency ?>
                    <div class="card h-100 shadow-sm content-section border-0 scroll-fade-in">
                         <a href="<?php echo $detailUrl; ?>">
                            <img src="<?php echo $imageUrl; ?>" class="card-img-top" alt="<?php echo $mythTitle; ?> image" style="height: 200px; object-fit: cover;">
                         </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $mythTitle; ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?php echo $mythExcerpt; ?></p>
                            <a href="<?php echo $detailUrl; ?>" class="btn btn-sm btn-outline-primary mt-auto align-self-start">Read More <i class="fas fa-angle-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php elseif (empty($errorMessage)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    No myth entries found at this time. Check back later!
                </div>
            </div>
        <?php endif; ?>

    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>