<?php
session_start();
include "../DB_Connection/Connection.php";
require "../Back_End_Files/PHP_Files/mailer_details.php"; // include PHPMailer setup

// Set correct timezone
date_default_timezone_set('Asia/Manila');

$message = "";
$messageType = "";

if (isset($_POST['submit'])) {

    $email = trim($_POST['email']);
    $user_id = null;

    // Check students
    $stmt = $connection->prepare("
        SELECT s.user_id
        FROM student_applications sa
        JOIN students s ON sa.application_id = s.application_id
        WHERE sa.email = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) $user_id = $row['user_id'];

    // Check teachers
    if (!$user_id) {
        $stmt = $connection->prepare("
            SELECT t.user_id
            FROM teacher_applications ta
            JOIN teachers t ON ta.teacher_application_id = t.application_id
            WHERE ta.email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) $user_id = $row['user_id'];
    }

    if ($user_id) {
        // Generate OTP (6 uppercase chars)
        $otp_code = strtoupper(bin2hex(random_bytes(3))); // e.g., A1B2C3
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Insert into password_resets
        $stmt = $connection->prepare("
            INSERT INTO password_resets (user_id, email, otp_code, expires_at, is_used, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $stmt->bind_param("isss", $user_id, $email, $otp_code, $expires_at);
        $stmt->execute();

        $_SESSION['reset_email'] = $email;

        // Send OTP email
        try {
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "CDONHS-SHS Password Reset OTP";
            $mail->Body = "
                <h2>Password Reset OTP</h2>
                <p>Hello,</p>
                <p>Your One-Time Password (OTP) for resetting your password is:</p>
                <h3 style='color:blue;'>$otp_code</h3>
                <p>This OTP will expire in 1 hour.</p>
                <p>If you did not request a password reset, ignore this email.</p>
            ";
            $mail->send();
            $message = "OTP has been sent to your email.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Mailer Error: " . $mail->ErrorInfo;
            $messageType = "error";
        }

        // DEBUG: show OTP on page (optional)
        $_SESSION['otp_debug'] = $otp_code;

        header("Location: verify_otp.php");
        exit;

    } else {
        $message = "Email not found in the system.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CDONHS-SHS</title>
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
                <h2>Forgot Password</h2>
                <p class="subtitle">Enter your email address and we'll send you an OTP to reset your password.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="message message-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                    <button type="submit" name="submit">Send OTP</button>
                </form>

                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>

                <?php
                // Show OTP for debugging (remove on production)
                if (isset($_SESSION['otp_debug'])) {
                    echo "<div class='debug-info'>DEBUG OTP: " . $_SESSION['otp_debug'] . "</div>";
                    unset($_SESSION['otp_debug']);
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
