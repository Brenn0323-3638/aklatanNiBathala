<?php require_once 'header.php'; ?>

<!-- Hero Section with Animation -->
<section class="hero-section text-center py-5 mb-5 shadow-sm scroll-fade-in">
    <div class="container">
        <!-- Optional Logo -->
        <!-- <img src="<?php echo $base_asset_url; ?>/images/aklatan_logo_dark.png" alt="Logo" height="90" class="mb-4"> -->
        <h1 class="display-4 hero-title"><?php echo "Aklatan ni Bathala"; ?></h1>
        <p class="fs-4 col-lg-8 mx-auto hero-subtitle">Rediscover the rich tapestry of Philippine Mythology.</p>
        <hr class="my-4 mx-auto hero-divider">
        <p class="lead mb-4">Explore ancient tales, divine beings, and legendary creatures from across the archipelago.</p>
        <a href="myths.php" class="btn btn-primary btn-lg px-4 me-sm-2 mb-2 mb-sm-0">Explore Myths</a>
        <a href="quizzes.php" class="btn btn-outline-secondary btn-lg px-4 mb-2 mb-sm-0">Test Your Knowledge</a>
    </div>
</section>

<!-- Featured Content Section -->
<section class="featured-content mb-5">
    <div class="row g-4"> <!-- Added gutters -->
        <!-- Box 1: Featured Myth -->
        <div class="col-md-6">
            <div class="featured-box featured-box-dark h-100 p-5 scroll-fade-in animation-delay-1">
                <i class="fas fa-book-open fa-3x mb-3"></i> <!-- Icon -->
                <h2>Featured Myth</h2>
                <p>Check out "The wicked woman's reward" now! A short, but interesting story. Want to take a look?</p>
                <a href="viewMythDetails.php?id=19" class="btn btn-outline-light mt-auto">Read More</a> <!-- Use <a> for links -->
            </div>
        </div>
        <!-- Box 2: Contribute -->
        <div class="col-md-6">
            <div class="featured-box featured-box-light h-100 p-5 scroll-fade-in animation-delay-2">
                 <i class="fas fa-feather-alt fa-3x mb-3"></i> <!-- Icon -->
                <h2>Contribute!</h2>
                <p>Share your knowledge! Submit myths and help grow the Aklatan. Your contributions are valuable.</p>
                <a href="submitMyth.php" class="btn btn-outline-secondary mt-auto">Submit an Entry</a> <!-- Use <a> for links -->
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>