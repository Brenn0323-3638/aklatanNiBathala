<?php
require_once 'adminHeader.php'; // Includes session check, doctype, head, sidebar

// Optional: Check if there's an error message from a previous attempt
$error_message = '';
if (isset($_SESSION['upload_error'])) {
    $error_message = '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['upload_error']) . '</div>';
    unset($_SESSION['upload_error']); // Clear the message after displaying
}

// Optional: Check for success message
$success_message = '';
if (isset($_SESSION['upload_success'])) {
    $success_message = '<div class="alert alert-success">' . htmlspecialchars($_SESSION['upload_success']) . '</div>';
    unset($_SESSION['upload_success']); // Clear the message
}
?>

<!-- Main Content Area for Adding Myth -->
<div class="main-content" id="main">
    <div class="p-4">
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-dark me-3 d-md-none" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h3>Add New Myth</h3>
                    <p>Enter details and upload associated file.</p>
                </div>
            </div>
             <div>
                 <a href="viewMyths.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            </div>
        </div>

        <!-- Display Messages -->
        <?php echo $success_message; ?>
        <?php echo $error_message; ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Myth Details</h5>

                <!-- The Form -->
                <form action="processAddMyth.php" method="POST" enctype="multipart/form-data">
                    <!-- Security Token (Optional but recommended - CSRF protection) -->
                    <!-- <input type="hidden" name="csrf_token" value="<?php // echo generate_csrf_token(); ?>"> -->

                    <div class="mb-3">
                        <label for="mythTitle" class="form-label">Myth Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mythTitle" name="mythTitle" required>
                    </div>

                    <div class="mb-3">
                        <label for="mythContent" class="form-label">Myth Content / Description</label>
                        <textarea class="form-control" id="mythContent" name="mythContent" rows="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="mythFile" class="form-label">Upload File (Optional)</label>
                        <input class="form-control" type="file" id="mythFile" name="mythFile">
                        <div id="fileHelp" class="form-text">Select a document or image related to the myth.</div>
                    </div>

                    <div class="mb-3">
                        <label for="fileDescription" class="form-label">File Description (Optional)</label>
                        <input type="text" class="form-control" id="fileDescription" name="fileDescription" placeholder="E.g., Manuscript Scan, Artist Rendition">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Myth</button>
                </form>
                 <!-- End Form -->

            </div> <!-- End Card Body -->
        </div> <!-- End Card -->

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>