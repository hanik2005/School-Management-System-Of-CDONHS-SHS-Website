<?php
session_start();


include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CAPTCHA check
    if (
        !isset($_POST['captcha'], $_POST['correct_sum']) ||
        $_POST['captcha'] != $_POST['correct_sum']
    ) {
        $_SESSION['login_data'] = ['username' => $_POST['username'] ?? ''];
        header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/login.php?error=captcha");
        exit();
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Get user by username ONLY
    $stmt = $connection->prepare("SELECT user_id, username, password, role_id, school_id, status, first_login FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password'])) {

            // Login success - store all needed info in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['school_id'] = $user['school_id'];
            $_SESSION['status'] = $user['status'];
            $_SESSION['first_login'] = $user['first_login'];

            if ($user['first_login'] == 1) {
                header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/change_password.php");
                exit();
            }

            switch ($user['role_id']) {
                case 1: // Student
                    header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/Student_Files/home.php");
                    break;

                case 2: // Admin
                    header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/Admin_Files/home.php");
                    break;

                case 3: // Teacher
                    header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/Teacher_Files/home.php");
                    break;

                default:
                    header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/login.php?error=role");
                    break;
    }

        
            exit();

        } else {
            // Wrong password
            header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/login.php?error=invalid");
            exit();
        }

    } else {
        // Username not found
        header("Location: /SMS_CDONHS-SHS_WEBSITE/Website_Files/login.php?error=invalid");
        exit();
    }

    $stmt->close();
}

$connection->close();
?>
