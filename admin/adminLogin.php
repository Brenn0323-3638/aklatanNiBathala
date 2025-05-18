<?php
// *** START OUTPUT BUFFERING AT THE VERY TOP ***
ob_start();
// ***********************************************

session_start();
// Use require_once for robustness, ensure path is correct
require_once('../includes/db.php');

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userName = trim($_POST['username']);
    $userPassword = $_POST['password']; // Get the raw password

    try {
        // Call the function from db.php to get the PDO connection object
        $pdo = connect_db(); // This might throw an exception now if connection fails

        // No need for 'if (!$pdo)' check here if connect_db throws exception on failure

        // Prepare statement using PDO - select only necessary fields
        $query = "SELECT id, username, password FROM users WHERE username = ? AND role = 'admin'";
        $stmt = $pdo->prepare($query);

        // Execute statement using PDO (pass parameters in an array)
        $stmt->execute([$userName]);

        // Fetch the user using PDO fetch() - returns false if no user found
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if a user row was returned
        if ($userData) {
            // Verify the provided password against the hashed password from the database
            if (password_verify($userPassword, $userData['password'])) {
                // Password is correct
                $_SESSION['adminId'] = $userData['id'];
                $_SESSION['adminUsername'] = $userData['username'];

                error_log("DEBUG: SUCCESS! Intending to redirect user ID " . $userData['id'] . " (" . $userData['username'] . ")");

                // Check if headers were already sent (by whitespace, includes, etc.)
                if (headers_sent($file, $line)) {
                    error_log("DEBUG: PROBLEM! Output detected before header() call. Output started at {$file}:{$line}");
                    $loginError = "Cannot redirect: Output already started at {$file}:{$line}.";
                    // Let script continue to display login page with error
                  } else {
                    error_log("DEBUG: GOOD! No output detected before header(). Attempting redirect.");
                    ob_end_clean(); // Discard buffer before redirecting
                    session_write_close(); // <<<--- ADD THIS LINE TO COMMIT SESSION DATA
                    header("Location: dashboard.php");
                    exit;
                }

            } else {
                $loginError = "Incorrect password.";
                error_log("DEBUG: FAIL! Incorrect password for username: " . htmlspecialchars($userName));
            }
        } else {
            $loginError = "Admin user not found.";
             error_log("DEBUG: FAIL! Admin user not found with username: " . htmlspecialchars($userName));
        }

    } catch (PDOException $e) {
        // Catch connection errors (now thrown from connect_db) or query errors
        $loginError = "Database operation failed. Please contact support."; // More user-friendly
        // Log the detailed error for the admin/developer
        error_log("Admin Login PDO Exception: " . $e->getMessage() . " for username: " . htmlspecialchars($userName));
        error_log("DEBUG: FAIL! Caught PDOException during login.");

    } catch (Exception $e) {
        // Catch other potential errors
        $loginError = "An unexpected error occurred.";
        error_log("Admin Login General Exception: " . $e->getMessage() . " for username: " . htmlspecialchars($userName));
        error_log("DEBUG: FAIL! Caught generic Exception during login.");
    }
} // end if POST request

// *** If we reach here after POST, redirect likely failed. Clean buffer before HTML. ***
// Ensure buffer is cleaned if redirect failed or not a POST request
// Note: If redirect succeeded, exit() was called earlier.
if (ob_get_level() > 0) { // Use ob_get_level() to check buffer nesting depth ob_end_clean(); // Discard buffer content if redirect failed or script ends normally 
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | Aklatan ni Bathala</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/adminLogin.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-header">
      <!-- Consider adding an icon or small logo here -->
      <!-- <i class="fas fa-shield-alt fa-2x mb-2"></i> <br> -->
      <h3>Admin Login</h3>
      <p class="baybayin mb-0">ᜀᜃᜎᜆᜈ᜔ ᜈᜒ ᜊᜆ᜔ᜑᜎ</p>
    </div>
    <div class="login-body">
      <?php if (!empty($loginError)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
      <?php endif; ?>
      <form method="POST" action="adminLogin.php">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Login</button> <!-- Made button slightly larger -->
        </div>
      </form>

      <!-- *** ADDED TEXT AND SEPARATOR HERE *** -->
      <hr class="my-4"> <!-- Horizontal rule with vertical margin -->
      <p class="text-center text-muted small">
          If you want an admin account, please reach out to the developers.
          <br> <!-- Optional line break -->
          <!-- Maybe add a link back to the main site if applicable -->
          <!-- <a href="../index.php">Back to Main Site</a> -->
      </p>
      <!-- ************************************** -->

    </div> <!-- End login-body -->
  </div> <!-- End login-wrapper -->

  <!-- Optional: Font Awesome JS if using JS features, usually not needed for icons -->
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script> -->
</body>
</html>