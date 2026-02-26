<?php
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 3;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

     $insertUser = $connection->prepare(
        "INSERT INTO users (username, password, role_id)
        VALUES (?, ?, ?)"
    );
     $insertUser->bind_param(
        "ssi",
        $username,
        $hashedPassword,
        $role
        );
    $insertUser->execute();

    header("Location: ../../Website_Files/login.php");
    exit;
}

?>