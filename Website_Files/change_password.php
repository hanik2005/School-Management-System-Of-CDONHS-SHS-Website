<?php
session_start();

// Check if user is logged in and has first_login = 1
if (!isset($_SESSION['user_id']) || !isset($_SESSION['first_login'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['first_login'] != 1) {
    // Not first login, redirect to appropriate dashboard
    switch ($_SESSION['role_id']) {
        case 1:
            header("Location: Student_Files/home.php");
            break;
        case 2:
            header("Location: Admin_Files/home.php");
            break;
        case 3:
            header("Location: Teacher_Files/home.php");
            break;
        default:
            header("Location: login.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - First Login | CDONHS-SHS</title>
    <link rel="stylesheet" href="../Design/change_password_design.css">
    <link rel="icon" href="../Assets/logo_resized_16x16.png" type="image/png">
</head>
<body>
    <div class="change-password-wrapper">
        <div class="change-password-form">
            <h2>🔐 Change Your Password</h2>
            <p class="subtitle">For security purposes, please change your default password to continue.</p>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php
                    switch ($_GET['error']) {
                        case 'wrong_password':
                            echo "❌ Current password is incorrect. Please try again.";
                            break;
                        case 'password_mismatch':
                            echo "❌ New password and confirm password do not match.";
                            break;
                        case 'same_password':
                            echo "❌ New password must be different from your current password.";
                            break;
                        case 'weak_password':
                            echo "❌ Password must be at least 8 characters long.";
                            break;
                        case 'update_failed':
                            echo "❌ Failed to update password. Please try again.";
                            break;
                        default:
                            echo "❌ An error occurred. Please try again.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="password-requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>Minimum 8 characters</li>
                    <li>Must be different from your current password</li>
                </ul>
            </div>

            <form action="../Back_End_Files/PHP_Files/change_password_backend.php" method="POST">
                <div class="password-container">
                    <label for="current_password">Current Password:</label>
                    <div class="input-wrapper">
                        <input type="password" id="current_password" name="current_password" required 
                               placeholder="Enter your current password" autofocus>
                        <img src="../Assets/NotVisible.png" 
                             class="toggle-password" 
                             alt="Toggle visibility"
                             onclick="togglePassword('current_password', this)">
                    </div>
                </div>

                <div class="password-container">
                    <label for="new_password">New Password:</label>
                    <div class="input-wrapper">
                        <input type="password" id="new_password" name="new_password" required 
                               placeholder="Enter your new password">
                        <img src="../Assets/NotVisible.png" 
                             class="toggle-password" 
                             alt="Toggle visibility"
                             onclick="togglePassword('new_password', this)">
                    </div>
                </div>

                <div class="password-container">
                    <label for="confirm_password">Confirm New Password:</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your new password">
                        <img src="../Assets/NotVisible.png" 
                             class="toggle-password" 
                             alt="Toggle visibility"
                             onclick="togglePassword('confirm_password', this)">
                    </div>
                </div>

                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.src = '../Assets/Visible.png';
            } else {
                field.type = 'password';
                icon.src = '../Assets/NotVisible.png';
            }
        }
    </script>
</body>
</html>
