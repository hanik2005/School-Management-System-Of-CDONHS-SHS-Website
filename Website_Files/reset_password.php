<?php
session_start();
include "../DB_Connection/Connection.php";

if (!isset($_SESSION['valid_reset_id'])) {
    die("Unauthorized access.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
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
        $message = "Password successfully reset. <a href='login.php'>Login here</a>";
    }
}
?>

<form method="POST">
    <h2>Reset Password</h2>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Reset Password</button>
</form>
<p style="color:green;"><?php echo $message; ?></p>
