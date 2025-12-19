<?php

/**
 * TINK E-Commerce Jewelry Store - Configuration File
 * Project: Web Application Development (TMF3973)
 * Version: 1.0
 * 
 * This file contains database connection configuration and
 * other application settings for TINK platform
 */

// =====================================================
// DATABASE CONFIGURATION
// =====================================================

define('DB_HOST', 'localhost');           // Database server
define('DB_USER', 'root');                // Database username
define('DB_PASSWORD', '');                // Database password
define('DB_NAME', 'tink_db');             // Database name
define('DB_PORT', 3306);                  // Database port

// =====================================================
// APPLICATION SETTINGS
// =====================================================

define('APP_NAME', 'TINK');
define('APP_VERSION', '1.0');
define('APP_TIMEZONE', 'Asia/Kuala_Lumpur');

// =====================================================
// FILE PATHS
// =====================================================

define('BASE_PATH', dirname(__FILE__));
define('IMAGES_PATH', BASE_PATH . '/public/images/');
define('UPLOADS_PATH', BASE_PATH . '/uploads/');

// =====================================================
// DATABASE CONNECTION (MySQLi)
// =====================================================

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set character set to UTF8
    $conn->set_charset("utf8mb4");

    // Set timezone
    date_default_timezone_set(APP_TIMEZONE);
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}

// =====================================================
// SESSION CONFIGURATION
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// =====================================================
// SECURITY SETTINGS (Student Project Level)
// =====================================================

// Password hashing algorithm (bcrypt)
define('PASSWORD_ALGORITHM', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', array('cost' => 10));

// Basic input validation flag
define('VALIDATE_INPUT', true);

// =====================================================
// ERROR REPORTING
// =====================================================

// For development - show errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// For production - log errors only
// error_reporting(E_ALL);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', BASE_PATH . '/logs/errors.log');