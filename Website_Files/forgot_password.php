<?php
session_start();
include "../DB_Connection/Connection.php";
require "../Back_End_Files/PHP_Files/mailer_details.php"; // include PHPMailer setup

// Set correct timezone
date_default_timezone_set('Asia/Manila');

$message = "";

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
        } catch (Exception $e) {
            $message = "Mailer Error: " . $mail->ErrorInfo;
        }

        // DEBUG: show OTP on page (optional)
        $_SESSION['otp_debug'] = $otp_code;

        header("Location: verify_otp.php");
        exit;

    } else {
        $message = "Email not found in the system.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - CDONHS-SHS</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit" name="submit">Send OTP</button>
    </form>
    <?php if (!empty($message)) { ?>
        <p style="color:red;"><?php echo $message; ?></p>
    <?php } ?>

    <?php
    // Show OTP for debugging (remove on production)
    if (isset($_SESSION['otp_debug'])) {
        echo "<p style='color:green;'>DEBUG OTP: " . $_SESSION['otp_debug'] . "</p>";
        unset($_SESSION['otp_debug']);
    }
    ?>
</body>
</html>
