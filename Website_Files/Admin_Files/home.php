<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['school_id'])) {
    include "../../DB_Connection/Connection.php";
    include '../../Back_End_Files/PHP_Files/User.php';
    $user = getUserById($_SESSION['user_id'], $connection);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
</head>
<body>
</body>
</html>
<?php }else {
        header("Location: ../login.php");
        exit;
    } ?>