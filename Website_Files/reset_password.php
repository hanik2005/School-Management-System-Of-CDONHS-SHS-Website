<?php
session_start();
include "../DB_Connection/Connection.php";

if (!isset($_SESSION['valid_reset_id'])) {
    die("Unauthorized access.");
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        $user_id = $_SESSION['reset_user_id'];
        $reset_id = $_SESSION['valid_reset_id'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();

        // Mark OTP as used
        $stmt = $connection->prepare("UPDATE password_resets SET is_used = 1 WHERE reset_id = ?");
        $stmt->bind_param("i", $reset_id);
        $stmt->execute();

        session_destroy();
        $message = "Password successfully reset! You can now login with your new password.";
        $messageType = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CDONHS-SHS</title>
    <link rel="stylesheet" href="../Design/forgot_password_design.css">
    <link rel="icon" href="../Assets/LOGO.png" type="image/jpg">
</head>
<body>
    <div class="wrapper">
        <!-- Left Panel -->
        <div class="left-panel">
            <img src="../Assets/LOGO.png" alt="CDONHS-SHS Logo" class="logo">
            <h1>CDONHS-SHS</h1>
            <p>Cagayan De Oro National High School - Senior High School</p>
            <p>School Management System</p>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="form-container">
                <div class="icon-container">
                    <img src="../Assets/logo_remBac.png" alt="Icon">
                </div>
                <h2>Reset Password</h2>
                <p class="subtitle">Enter your new password below.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="message message-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                        <?php if ($messageType === 'success'): ?>
                            <br><a href="login.php" style="font-weight: bold; color: #1e3a8a;">Click here to Login</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!isset($messageType) || $messageType !== 'success'): ?>
                <form method="POST">
                    <label for="new_password">New Password</label>
                    <div class="password-container">
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                        <img src="../Assets/NotVisible.png" alt="Toggle Password" class="toggle-password" onclick="togglePassword('new_password', this)">
                    </div>
                    
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        <img src="../Assets/NotVisible.png" alt="Toggle Password" class="toggle-password" onclick="togglePassword('confirm_password', this)">
                    </div>
                    
                    <button type="submit">Reset Password</button>
                </form>
                <?php endif; ?>

                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.src = '../Assets/Visible.png';
            } else {
                input.type = 'password';
                icon.src = '../Assets/NotVisible.png';
            }
        }
    </script>
</body>
</html>
