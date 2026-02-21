<?php
session_start();

include "../../DB_Connection/Connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Website_Files/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ========== VALIDATION ==========

    // 1. Check if new password matches confirm password
    if ($new_password !== $confirm_password) {
        header("Location: ../../Website_Files/change_password.php?error=password_mismatch");
        exit();
    }

    // 2. Check password strength (minimum 8 characters)
    if (strlen($new_password) < 8) {
        header("Location: ../../Website_Files/change_password.php?error=weak_password");
        exit();
    }

    // 3. Get current password from database using prepared statement (prevents SQL injection)
    $stmt = $connection->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        header("Location: ../../Website_Files/login.php");
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // 4. Verify current password is correct
    if (!password_verify($current_password, $user['password'])) {
        header("Location: ../../Website_Files/change_password.php?error=wrong_password");
        exit();
    }

    // 5. Check that new password is different from current password
    if (password_verify($new_password, $user['password'])) {
        header("Location: ../../Website_Files/change_password.php?error=same_password");
        exit();
    }

    // ========== UPDATE PASSWORD ==========

    // Hash the new password securely
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password and set first_login = 0 using prepared statement
    $update_stmt = $connection->prepare("UPDATE users SET password = ?, first_login = 0 WHERE user_id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user_id);

    if ($update_stmt->execute()) {
        // Update session
        $_SESSION['first_login'] = 0;
        
        // Redirect to appropriate dashboard based on role
        switch ($_SESSION['role_id']) {
            case 1: // Student
                header("Location: ../../Website_Files/Student_Files/home.php?success=password_changed");
                break;
            case 2: // Admin
                header("Location: ../../Website_Files/Admin_Files/home.php?success=password_changed");
                break;
            case 3: // Teacher
                header("Location: ../../Website_Files/Teacher_Files/home.php?success=password_changed");
                break;
            default:
                header("Location: ../../Website_Files/login.php");
        }
    } else {
        header("Location: ../../Website_Files/change_password.php?error=update_failed");
    }

    $update_stmt->close();
}

$connection->close();
?>
