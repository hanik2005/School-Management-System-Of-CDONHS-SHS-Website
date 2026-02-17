<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* ========================= */
/* VERIFY ADMIN SESSION      */
/* ========================= */
$user_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

// Prepare statement
$stmt = mysqli_prepare($connection, "
    SELECT * FROM users 
    WHERE user_id = ? 
    AND school_id = ? 
    AND role_id = 2
");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $school_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
     <link rel="stylesheet" href="../../Design/dashboard_design.css">
</head>
<body>
    <!-- header -->
    <div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>
    <div class="center">
        Admin
    </div>
    <div class="right">
         <button class="profile-btn" type="button">
        <img src="../../Assets/admin_profile.png">
    </button>

    <div class="profile-dropdown">
        <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>

    </div>
    </div>
    </div>

    <div class="dashboard">

  <div class="dashboard-box">

    <div class="dashboard-wrapper">

    <div class="dashboard-container">
        <a href="application_page.php" class="dashboard-card">
            <img src="../../Assets/application_button.jpg">
            <h3>Application List</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="#" class="dashboard-card">
            <img src="../../Assets/Visible.png">
            <h3>Sensitive Information</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="#" class="dashboard-card">
            <img src="../../Assets/activation_button.png">
            <h3>Activation Page</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="grade_validation_page.php" class="dashboard-card">
            <img src="../../Assets/validation_button.png">
            <h3>Grades Validation</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="enlistment_validation_page.php" class="dashboard-card">
            <img src="../../Assets/enlistment_validation.png">
            <h3>Enlistment Validation</h3>
        </a>
    </div>

</div>


  </div>

</div>




    <!-- <a href="admin_teacher_application_list.php">Teacher Application List</a>
    <a href="admin_student_application_list.php">Student Application List</a>    
    <a href="../Teacher_Online_Form.php">Enrollment Teacher</a>
    <a href="../Student_Online_Form.php">Enrollment Student</a> -->

     <!-- footer -->
    <div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Management System
    </div>
    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>
