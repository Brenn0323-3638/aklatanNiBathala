<?php
// Include the common user header
require_once 'header.php';

// Define the content here
$appName = "Aklatan ni Bathala";
$purpose = "Aklatan ni Bathala aims to spread the knowledge and the rich history of Filipino Mythology. It sets out to give a platform for the creative and educational opportunities associated with Filipino mythology.";
$overview = "Aklatan ni Bathala is a multipurpose website/platform for all things Filipino mythology related. It has libraries of content and resources, with more interactive features to come. One thing is for certain, Aklatan ni Bathala is a community driven passion project.";

?>


<!-- Page Title Header -->
<div class="text-center my-4">
     <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #2e3a59;">About <?php echo $appName; ?></h1>
     <p class="lead text-muted">Learn more about our mission and platform.</p>
</div>


<div class="container mb-5">
    <div class="row justify-content-center">
         <div class="col-lg-10">

             <!-- Overview Section -->
            <div class="content-section about-section-user text-center scroll-fade-in"> <!-- Add scroll class -->
                <i class="fas fa-binoculars"></i>
                <h2 class="mb-3">Overview</h2>
                <p class="lead"><?php echo $overview; ?></p>
            </div>

            <!-- Purpose Section -->
            <div class="content-section about-section-user text-center scroll-fade-in"> <!-- Add scroll class -->
                <i class="fas fa-bullseye"></i>
                <h2 class="mb-3">Our Purpose</h2>
                <p class="lead"><?php echo $purpose; ?></p>
            </div>

            <!-- Row for Features & Contribute -->
            <div class="row g-4 about-section-user">
                <div class="col-md-6 scroll-fade-in"> <!-- Add scroll class -->
                    <div class="content-section h-100 text-center">
                         <i class="fas fa-star"></i>
                        <h4 class="mb-3">What You Can Do</h4>
                        <ul class="list-unstyled text-start feature-list">
                            <li><i class="fas fa-check"></i>Explore a growing library of myths and legends.</li>
                            <li><i class="fas fa-check"></i>Test your knowledge with interactive quizzes.</li>
                            <li><i class="fas fa-check"></i>Discover different deities, creatures, and heroes.</li>
                            <li><i class="fas fa-check"></i>Learn about the diverse folklore across the Philippines.</li>
                        </ul>
                    </div>
                </div>
                 <div class="col-md-6 scroll-fade-in"> <!-- Add scroll class -->
                    <div class="content-section h-100 text-center">
                         <i class="fas fa-users"></i>
                        <h4 class="mb-3">Join the Community</h4>
                         <p>Help us build the most comprehensive resource for Philippine Mythology!</p>
                        <a href="submitMyth.php" class="btn btn-primary">Contribute an Entry</a>
                        <p class="mt-3 small text-muted">All submissions are reviewed by administrators.<br>
                    Report bugs & send suggestions to the email below:<br>
                0323-3638@lspu.edu.ph</p>
                    </div>
                </div>
            </div>

         </div> <!-- /col -->
    </div> <!-- /row -->
</div> <!-- /container -->


<?php
// Standard footer
require_once 'footer.php';
?>