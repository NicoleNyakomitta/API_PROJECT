<?php
// includes/auth.php
// This file is now simplified since functions are in functions.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database and functions
require_once 'config/database.php';
require_once 'includes/functions.php';
?>