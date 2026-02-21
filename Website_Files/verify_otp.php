<?php
session_start();
include "../DB_Connection/Connection.php";

// Make sure the user came from forgot_password
if (!isset($_SESSION['reset_email'])) {
    die("Unauthorized access.");
}

// Set correct timezone
date_default_timezone_set('Asia/Manila');

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $entered_otp = trim($_POST['otp']);
    $email = $_SESSION['reset_email'];
    $now = date("Y-m-d H:i:s"); // Current PHP time

    // Check OTP against password_resets table
    $stmt = $connection->prepare("
        SELECT pr.reset_id, pr.user_id
        FROM password_resets pr
        WHERE pr.email = ?
        AND TRIM(pr.otp_code) = ?
        AND pr.is_used = 0
        AND pr.expires_at > ?
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("sss", $email, $entered_otp, $now);
    $stmt->execute();
    $result = $stmt->get_result();
    $valid = $result->fetch_assoc();

    if (!$valid) {
        $message = "Invalid or expired OTP. Please check your email.";
        $messageType = "error";
    } else {
        // OTP is valid — save info for reset_password.php
        $_SESSION['valid_reset_id'] = $valid['reset_id'];
        $_SESSION['reset_user_id'] = $valid['user_id'];

        // Redirect to reset password page
        header("Location: reset_password.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - CDONHS-SHS</title>
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
                <h2>Verify OTP</h2>
                <p class="subtitle">We've sent a One-Time Password (OTP) to your email. Please enter it below.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="message message-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <label for="otp">Enter OTP Code</label>
                    <input type="text" id="otp" name="otp" class="otp-input" placeholder="XXXXXX" maxlength="6" required>
                    <button type="submit">Verify OTP</button>
                </form>

                <div class="back-link">
                    <a href="forgot_password.php">← Resend OTP</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
