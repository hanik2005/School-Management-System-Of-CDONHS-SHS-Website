<?php
// Include database connection
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';



// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['captcha']) || !isset($_POST['correct_sum']) || $_POST['captcha'] != $_POST['correct_sum']) {
        $_SESSION['login_data'] = ['username' => $_POST['username'] ?? ''];
        header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/login.php?error=captcha");
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query to check user credentials
    $stmt = $connection->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login successful, redirect to home.php
        header('Location: ../../Website_Files/home.php');
        exit();
    } else {
        // Invalid credentials, redirect back to login with error
        header('Location: ../../Website_Files/login.php?error=1');
        exit();
    }

    $stmt->close();
}

$connection->close();
?>