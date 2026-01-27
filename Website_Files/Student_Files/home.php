<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['school_id'])) {
    include "../../DB_Connection/Connection.php";
    include '../../Back_End_Files/PHP_Files/User.php';
    $user = getStudentUserById($_SESSION['user_id'], $connection);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="../../Design/main_design.css">
    <title>Student Home</title>
</head>
<body>

<!-- header -->
    <div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>
    <div class="center">
        Student
    </div>
    <div class="right">
        <span id="profileName"></span>
        <img src="../../Assets/profile.jpg" alt="Profile Logo">
    </div>
    </div>





    <p>Name: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>



    <!-- footer -->
    <div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Information System
    </div>

</body>
</html>
<?php }else {
        header("Location: ../login.php");
        exit;
    } ?>