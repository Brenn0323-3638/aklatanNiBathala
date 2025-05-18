<?php
// 1. Start the session
// This is necessary to access and then destroy the current session.
session_start();

// 2. Unset all of the session variables.
// This clears all data stored within the $_SESSION superglobal for this user.
$_SESSION = array();

// 3. Destroy the session cookie (Optional but recommended)
// If you are using cookies for session IDs (most common setup),
// this step helps remove the cookie from the user's browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); // Get current cookie parameters
    setcookie(session_name(), '', time() - 42000, // Set expiry in the past
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server.
// This invalidates the session ID on the server side.
session_destroy();

// 5. Redirect to the login page.
// Assuming logout.php and adminLogin.php are in the same 'admin' folder.
// Adjust path if necessary.
header("Location: adminLogin.php");
exit; // Ensure no further code is executed after redirect.
?>