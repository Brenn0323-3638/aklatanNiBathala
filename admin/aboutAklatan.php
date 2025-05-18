<?php
// Use the standard header which includes session check
require_once 'adminHeader.php';

// Define the content here
$appName = "Aklatan ni Bathala";
$version = "1.0.0 (Admin Beta)"; // Example version

$purpose = "Aklatan ni Bathala aims to spread the knowledge and the rich history of Filipino Mythology. It sets out to give a platform for the creative and educational opportunities associated with Filipino mythology.";
$overview = "Aklatan ni Bathala is a multipurpose website/platform for all things Filipino mythology related. It has libraries of content and resources, with more interactive features to come. One thing is for certain, Aklatan ni Bathala is a community driven passion project.";

?>



<!-- Main Content Area for About Page -->
<div class="main-content" id="main">
    <div class="p-4">

        <!-- Hero Section -->
        <div class="about-hero text-center mb-5 shadow-sm">
             <!-- Optional: Add a logo image here -->
             <!-- <img src="<?php echo $base_asset_url; ?>/images/aklatan_logo_dark.png" alt="<?php echo $appName; ?> Logo" height="80" class="mb-3"> -->
            <h3 class="display-5"><?php echo $appName; ?></h3>
            <p class="lead mt-3"><?php echo $overview; ?></p>
            <hr style="max-width: 100px; margin: 1.5rem auto; border-width: 2px;">
            <span class="badge bg-light text-dark shadow-sm"><?php echo $version; ?></span>
        </div>


        <!-- Row for Purpose & Details -->
        <div class="row g-4 mb-4 align-items-center about-section">
            <div class="col-md-3 text-center">
                 <!-- Image Placeholder 1: Replace with relevant image/icon -->
                 <!-- <img src="<?php echo $base_asset_url; ?>/images/purpose_icon.png" alt="Purpose" class="img-fluid rounded icon-placeholder"> -->
                 <i class="fas fa-bullseye icon-placeholder-fa"></i>
            </div>
            <div class="col-md-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="card-title" style="color: #2e3a59;">Our Purpose</h4>
                        <p class="card-text text-muted"><?php echo $purpose; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row for Features & Future Plans -->
        <div class="row g-4 mb-4 align-items-center about-section">
             <div class="col-md-9 order-md-1"> <!-- Text first -->
                 <div class="card shadow-sm border-0">
                     <div class="card-header bg-transparent border-0 pt-3">
                         <h4 style="color: #2e3a59;">Key Features (Admin)</h4>
                     </div>
                    <div class="card-body pt-0">
                         <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><i class="fas fa-tachometer-alt fa-fw me-2 text-primary"></i>Dashboard & Statistics</li>
                            <li class="list-group-item border-0"><i class="fas fa-book-open fa-fw me-2 text-primary"></i>Myth Entry Management (CRUD)</li>
                            <li class="list-group-item border-0"><i class="fas fa-inbox fa-fw me-2 text-primary"></i>Entry Request Moderation</li>
                            <li class="list-group-item border-0"><i class="fas fa-question-circle fa-fw me-2 text-primary"></i>Quiz Management</li>
                            <li class="list-group-item border-0"><i class="fas fa-chart-line fa-fw me-2 text-primary"></i>Quiz Progress Overview (Planned)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3 order-md-2 text-center"> <!-- Image second -->
                 <!-- Image Placeholder 2: Replace -->
                 <!-- <img src="<?php echo $base_asset_url; ?>/images/features_icon.png" alt="Features" class="img-fluid rounded icon-placeholder"> -->
                  <i class="fas fa-cogs icon-placeholder-fa"></i>
            </div>
        </div>

        <!-- Row for Tech Details & Future Plans -->
         <div class="row g-4">
            <div class="col-md-6">
                 <div class="card shadow-sm mb-4 h-100 border-0">
                     <div class="card-body">
                        <h5 class="card-title mb-3"><i class="fas fa-tools me-2 text-secondary"></i>Technology</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Backend:</strong> Custom PHP</li>
                            <li class="mb-2"><strong>Frontend UI:</strong> Bootstrap 5</li>
                            <li class="mb-2"><strong>Icons:</strong> Font Awesome 6</li>
                            <li class="mb-2"><strong>Database:</strong> MySQL (via PDO)</li>
                        </ul>
                    </div>
                </div>
            </div>
             <div class="col-md-6">
                  <div class="card shadow-sm mb-4 h-100 border-0">
                     <div class="card-body">
                        <h5 class="card-title mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i>Future Plans</h5>
                        <p class="small text-muted mb-2">Potential upcoming features:</p>
                        <ul class="list-unstyled small">
                            <li class="mb-1"><i class="fas fa-check text-muted me-2"></i>User Accounts & Profiles</li>
                            <li class="mb-1"><i class="fas fa-check text-muted me-2"></i>User Quiz Interface</li>
                            <li class="mb-1"><i class="fas fa-check text-muted me-2"></i>Enhanced Search/Filtering</li>
                            <li class="mb-1"><i class="fas fa-check text-muted me-2"></i>Media Management</li>
                            <li class="mb-1"><i class="fas fa-check text-muted me-2"></i>Comments/Discussions</li>
                        </ul>
                    </div>
                </div>
            </div>
         </div>


    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
// Standard footer
require_once 'adminFooter.php';
?>