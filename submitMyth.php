<?php
// session_start(); // <<< REMOVED - header.php starts session

// Include the common user header
require_once 'header.php'; // header.php starts session now

// --- Check Login Status ---
$isUserLoggedIn = isset($_SESSION['user_id']); // Use the USER session variable

// --- Retrieve potential messages/old input ---
// *** READ session data FIRST ***
$submitError = $_SESSION['submit_error'] ?? null;
$submitSuccess = $_SESSION['submit_success'] ?? null;
$oldInput = $_SESSION['old_submit_input'] ?? [];

// *** THEN clear session variables ***
unset($_SESSION['submit_error']);
unset($_SESSION['submit_success']);
unset($_SESSION['old_submit_input']);

?>

<!-- Page Title Header -->
<div class="text-center my-4">
    <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #2e3a59;">Contribute a Myth</h1>
    <?php if ($isUserLoggedIn): ?>
        <p class="lead text-muted">Share your knowledge of Philippine Mythology with the Aklatan!</p>
    <?php else: ?>
         <p class="lead text-muted">Help grow the Aklatan by sharing myths you know.</p>
    <?php endif; ?>
</div>

<!-- Display Error/Success Messages -->
<?php if ($submitSuccess): ?>
    <div class="container">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($submitSuccess); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>
<?php if ($submitError): ?>
     <div class="container">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
             <?php echo htmlspecialchars($submitError); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <?php if ($isUserLoggedIn): // --- SHOW FORM ONLY IF LOGGED IN --- ?>

                 <div class="content-section mt-4 mb-5">
                    <h4 class="mb-3">Submission Form</h4>
                    <p class="text-muted small mb-4">Please provide as much detail as possible. Your submission will be reviewed by an administrator before being published. Thank you for contributing!</p>

                    <!-- Submission Form points to processSubmitMyth.php -->
                    <form action="processSubmitMyth.php" method="POST" novalidate>

                        <div class="mb-3">
                            <label for="mythTitle" class="form-label">Myth Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mythTitle" name="title" required value="<?php echo htmlspecialchars($oldInput['title'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="mythContent" class="form-label">Myth Content / Story <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="mythContent" name="content" rows="10" required placeholder="Please write the full story or description of the myth here..."><?php echo htmlspecialchars($oldInput['content'] ?? ''); ?></textarea>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Optional Details</h5>

                         <div class="mb-3">
                            <label for="mythCategory" class="form-label">Suggested Category</label>
                            <input type="text" class="form-control" id="mythCategory" name="category" placeholder="e.g., Creation Myth, Tikbalang Story, Visayan Folklore" value="<?php echo htmlspecialchars($oldInput['category'] ?? ''); ?>">
                            <div class="form-text">Suggest a category this myth might belong to.</div>
                        </div>

                         <div class="mb-3">
                            <label for="mythTags" class="form-label">Suggested Tags</label>
                            <input type="text" class="form-control" id="mythTags" name="tags" placeholder="e.g., Luzon, Bathala, Aswang, Origin Story" value="<?php echo htmlspecialchars($oldInput['tags'] ?? ''); ?>">
                            <div class="form-text">Comma-separated keywords related to the myth.</div>
                        </div>

                         <div class="mb-3">
                            <label for="mythSource" class="form-label">Source Information</label>
                            <textarea class="form-control" id="mythSource" name="source_info" rows="3" placeholder="Where did you learn about this myth? (e.g., book title, website URL, relative/place)"><?php echo htmlspecialchars($oldInput['source_info'] ?? ''); ?></textarea>
                        </div>

                         <div class="mb-3">
                            <label for="imageSuggestion" class="form-label">Image Suggestion (URL or Description)</label>
                            <input type="text" class="form-control" id="imageSuggestion" name="image_suggestion" placeholder="e.g., Describe an image or provide a link" value="<?php echo htmlspecialchars($oldInput['image_suggestion'] ?? ''); ?>">
                        </div>


                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Submit for Review</button>
                        </div>

                    </form>
                    <!-- End Form -->

                </div><!-- /.content-section -->

            <?php else: // --- SHOW LOGIN/REGISTER PROMPT IF NOT LOGGED IN --- ?>

                <div class="content-section mt-4 mb-5 text-center">
                    <h4 class="mb-3">Login Required</h4>
                    <p class="lead">You need to be logged in to contribute a myth entry.</p>
                    <p>Please log in to your account or register a new one to share your knowledge with the Aklatan community.</p>
                    <div class="mt-4">
                        <a href="login.php" class="btn btn-primary me-2">Login</a>
                        <a href="register.php" class="btn btn-secondary">Register</a>
                    </div>
                </div><!-- /.content-section -->

            <?php endif; // --- End Login Check --- ?>

        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>