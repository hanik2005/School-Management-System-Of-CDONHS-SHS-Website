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
<html>
<head>
    <title>Verify OTP - CDONHS-SHS</title>
</head>
<body>
    <h2>Verify OTP</h2>
    <form method="POST">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify</button>
    </form>
    <p style="color:red;"><?php echo $message; ?></p>
</body>
</html>
