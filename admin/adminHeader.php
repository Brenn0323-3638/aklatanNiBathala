<?php
// Start session and check login status AT THE VERY TOP
session_start();

// CORRECTED CHECK: Make sure the admin ID exists in the session
if (!isset($_SESSION['adminId'])) { // Check if adminId is set
    // Redirect to login if adminId is NOT set
    header("Location: adminLogin.php");
    exit();
}

// --- Determine the base path dynamically (optional but good practice) ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_asset_url = $protocol . $host . rtrim(dirname($script_dir), '/') . '/assets';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | Aklatan ni Bathala</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Merriweather&family=Playfair+Display:wght@600&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tagalog&display=swap" rel="stylesheet" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?php echo $base_asset_url; ?>/css/adminDashboard.css">
</head>
<body>
  <div class="d-flex" id="dashboardWrapper">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar"> <!-- Main sidebar container -->

        
        <!-- Inner container for scrolling content -->
        <div class="sidebar-inner d-flex flex-column h-100">
            <div class="sidebar-header py-3 px-3"> <!-- Removed flex from here -->
                <div> <!-- Wrapper for title/baybayin -->
                  <h2 class="mb-0">Admin Dashboard</h2> <!-- Removed margin bottom -->
                  <p class="baybayin mb-0">ᜀᜃᜎᜆᜈ᜔ ᜈᜒ ᜊᜆ᜔ᜑᜎ</p>
                </div>
                <!-- *** OLD BUTTON LOCATION (Now Removed) *** -->
            </div>
            <ul class="nav flex-column px-3 mt-3"> <!-- Added margin top to nav -->
              <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home fa-fw me-2"></i> Home</a> <!-- Added fa-fw -->
              </li>
              <li class="nav-item">
                <a class="nav-link" href="viewMyths.php"><i class="fas fa-book-open fa-fw me-2"></i> Myth Entries</a> <!-- Added fa-fw -->
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pendingMyths.php"><i class="fas fa-inbox fa-fw me-2"></i> Entry Requests</a>
                <!-- Optional: Add a badge later to show count -->
                <!-- <span class="badge bg-warning ms-auto">3</span> -->
              </li>
              <li class="nav-item">
                <a class="nav-link" href="manageQuiz.php"> <i class="fas fa-question-circle fa-fw me-2"></i> Manage Quizzes</a>
              </li>
              <li class="nav-item">
                 <a class="nav-link" href="viewQuizProgress.php"> <i class="fas fa-chart-line fa-fw me-2"></i> Quiz Progress</a>
              </li>
              <li class="nav-item">
                 <a class="nav-link" href="aboutAklatan.php"> <i class="fas fa-info-circle fa-fw me-2"></i> About Aklatan</a>
              </li>
              <li class="nav-item">
                <a class="nav-link"
                   href="adminLogout.php"  <?php /* --- Make sure href matches your logout script filename --- */ ?>
                   onclick="return confirm('Are you sure you want to log out?');"> <?php /* <<< ADD THIS ONCLICK ATTRIBUTE */ ?>
                     <i class="fas fa-sign-out-alt fa-fw me-2"></i> Logout
                </a>
              </li>
            </ul>
            <div class="mt-auto p-3 text-center">
                <small>Logged in as: <?php echo isset($_SESSION['adminUsername']) ? htmlspecialchars($_SESSION['adminUsername']) : 'Admin'; ?></small> <!-- Changed session key based on login script -->
            </div>
            <div class="mt-auto p-3 text-center">
              <div class="mt-auto p-3 text-center" style="font-size: 12px"><small>This website is developed as a final project for ITEP 204, ITEL 203 and ITEP 206.<br>
A project by: Group 3, BS INFO 2C</small></div>
            </div>
        </div>
        <!-- End sidebar-inner -->
    </div>
    <!-- End sidebar -->
     <!-- *** ADD NEW ARROW BUTTON HERE *** -->
     <button class="btn" id="sidebarCollapseArrow" aria-label="Toggle Sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
        <!-- ******************************* -->


    <!-- The main content div STARTS in the specific page (e.g., dashboard.php) -->
    <!-- Example: <div class="main-content" id="main"> -->