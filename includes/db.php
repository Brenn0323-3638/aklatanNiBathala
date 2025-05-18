<?php
// Database connection settings
$host = 'localhost';
$dbName = 'aklat_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4'; // Good practice to set charset

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";

// Options for PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

// Function to create and return a PDO connection object
function connect_db() {
    global $dsn, $username, $password, $options; // Make config vars available inside function

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
   // Inside the connect_db() function in includes/db.php
} catch (\PDOException $e) {
    // Log the detailed error for the administrator
    error_log("FATAL: Database Connection Error in connect_db(): " . $e->getMessage());

    // Option 1: Throw the exception again to be caught by the calling script
    throw new \PDOException("Database connection failed.", (int)$e->getCode(), $e);

    // Option 2 (Alternative): Return null or false, let calling script check
    // return null; // or return false;
    // The calling script (adminLogin.php) would then need to check if ($pdo === null)
}
}