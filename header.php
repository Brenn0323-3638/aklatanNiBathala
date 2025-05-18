<?php
// *** UNCOMMENT OR ADD SESSION START ***
session_start();

// --- Determine base URL for assets ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_dir_root = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_path = rtrim($script_dir_root, '/');
$base_asset_url = $protocol . $host . $base_path . '/assets';

// --- Check User Login Status ---
$isUserLoggedIn = isset($_SESSION['user_id']); // Check if user session ID is set
$loggedInUsername = $isUserLoggedIn ? htmlspecialchars($_SESSION['user_username']) : 'Guest'; // Get username if logged in

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Aklatan ni Bathala - Philippine Mythology</title>

    <!-- CSS Includes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;1,400&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tagalog&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo $base_asset_url; ?>/css/main.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark site-header sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <span class="navbar-brand-title">Aklatan ni Bathala</span>
                <span class="navbar-brand-baybayin ms-2">ᜀᜃᜎᜆᜈ᜔ ᜈᜒ ᜊᜆ᜔ᜑᜎ</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center"> <!-- Added align-items for vertical centering on lg+ -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a> <!-- Active class will be added by JS -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="myths.php">Myths</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quizzes.php">Quizzes</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="submitMyth.php">Contribute</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>

                    <!-- *** Conditional Login/User Area *** -->
                    <?php if ($isUserLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $loggedInUsername; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <!-- Add link to profile page when created -->
                                 <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog fa-fw me-2"></i>My Profile</a></li> 
                                 <li><hr class="dropdown-divider"></li> 
                                <li><a class="dropdown-item" href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
                                    <i class="fas fa-sign-out-alt fa-fw me-1"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                         <li class="nav-item ms-lg-2"> <!-- Add margin on larger screens -->
                             <a class="btn btn-outline-light btn-sm" href="login.php">Login</a>
                         </li>
                         <li class="nav-item ms-lg-2">
                              <a class="btn btn-warning btn-sm" href="register.php">Register</a> <!-- Example using warning color -->
                         </li>
                    <?php endif; ?>
                    <!-- ************************************ -->

                </ul>
            </div>
        </div>
    </nav>

    <!-- Start Main Content Wrapper (Closed in footer.php) -->
    <main class="flex-grow-1 site-content">
        <div class="container py-4"> <!-- Main content container with padding -->