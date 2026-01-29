<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['school_id'])) {
    include "../../DB_Connection/Connection.php";
    include '../../Back_End_Files/PHP_Files/User.php';
    $user = getTeacherUserById($_SESSION['user_id'], $connection);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <link rel="stylesheet" href="../../Design/main_design.css">
     <link rel="stylesheet" href="../../Design/profile_dropdown.css">
     <link rel="stylesheet" href="../../Design/dashboard_design.css">
    <title>Teacher Home</title>
</head>
<body>
    <!-- header -->
    <div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>
    <div class="center">
        Advisory:
    </div>
    <div class="right">
       <button class="profile-btn" type="button">
        <img src="../../Assets/profile_button.png">
    </button>

    <div class="profile-dropdown">
        <a href="student_profile.php">View Profile</a>
        <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>

    </div>
    </div>
    </div>


    <div class="dashboard">

  <div class="dashboard-box">

    <div class="dashboard-wrapper">

    <div class="dashboard-container">
        <a href="#" class="dashboard-card">
            <img src="../../Assets/profile_button.png">
            <h3>My Profile</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="#" class="dashboard-card">
            <img src="../../Assets/class_list_button.png">
            <h3>Class List</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="#" class="dashboard-card">
            <img src="../../Assets/grades_button.png">
            <h3>Grades</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="#" class="dashboard-card">
            <img src="../../Assets/progress_button.png">
            <h3>Student Progress</h3>
        </a>
    </div>

</div>
</div>


  </div>




     <!-- footer -->
    <div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Information System
    </div>
<script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>

<?php }else {
        header("Location: ../login.php");
        exit;
    } ?>