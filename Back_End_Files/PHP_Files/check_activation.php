<?php
/**
 * Activation Status Checker
 * This file provides functions to check if certain features are activated
 * 
 * Usage:
 * include "check_activation.php";
 * if (!isFeatureEnabled('Student Enrollment')) {
 *     header("Location: access_denied.php");
 *     exit;
 * }
 */

/**
 * Check if a specific feature is enabled
 * @param string $feature_name The name of the feature to check
 * @return bool True if enabled, false if disabled
 */
function isFeatureEnabled($feature_name) {
    $conn_path = dirname(__DIR__, 2) . '/DB_Connection/Connection.php';
    include $conn_path;
    
    $stmt = mysqli_prepare($connection, "
        SELECT activation_status 
        FROM activation_settings 
        WHERE activation_name = ?
    ");
    mysqli_stmt_bind_param($stmt, "s", $feature_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['activation_status'] == 1;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Get the activation status for a specific feature
 * @param string $feature_name The name of the feature to check
 * @return int 0 if disabled, 1 if enabled
 */
function getActivationStatus($feature_name) {
    $conn_path = dirname(__DIR__, 2) . '/DB_Connection/Connection.php';
    include $conn_path;
    
    $stmt = mysqli_prepare($connection, "
        SELECT activation_status 
        FROM activation_settings 
        WHERE activation_name = ?
    ");
    mysqli_stmt_bind_param($stmt, "s", $feature_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return intval($row['activation_status']);
    }
    
    mysqli_stmt_close($stmt);
    return 0;
}

/**
 * Redirect to access denied page if feature is disabled
 * @param string $feature_name The name of the feature to check
 */
function requireFeatureEnabled($feature_name) {
    if (!isFeatureEnabled($feature_name)) {
        header("Location: ../access_denied.php?feature=" . urlencode($feature_name));
        exit;
    }
}

/**
 * Get all activation settings
 * @return array Array of activation settings
 */
function getAllActivationSettings() {
    $conn_path = dirname(__DIR__, 2) . '/DB_Connection/Connection.php';
    include $conn_path;
    
    $query = "SELECT * FROM activation_settings ORDER BY id ASC";
    $result = mysqli_query($connection, $query);
    
    $settings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[] = $row;
    }
    
    return $settings;
}
?>
