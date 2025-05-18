<?php
// includes/csrf.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token_from_form) {
    if (empty($_SESSION['csrf_token']) || empty($token_from_form)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token_from_form);
}

// Optional: Function to regenerate token after successful POST to prevent reuse
function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>