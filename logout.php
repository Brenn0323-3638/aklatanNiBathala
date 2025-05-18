<?php
// Start the session to access session variables
session_start();

// --- Unset ONLY the USER session variables ---
// Keep admin session variables intact if they exist
unset($_SESSION['user_id']);
unset($_SESSION['user_username']);
unset($_SESSION['user_role']);
// Unset any other user-specific session data you might add later

// --- Session Regeneration (Optional but good practice if sensitive actions occurred) ---
// If you want to regenerate the ID even on user logout (might affect admin session if shared),
// you could do it here, but simply unsetting user keys is often sufficient.
// session_regenerate_id(true);

// --- Redirect to the public homepage ---
header("Location: index.php");
exit; // Ensure no further code executes
?>