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
    <title>Application List - CDONHS-SHS Admin</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/dashboard_design.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="left">
            <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
            <span>CDONHS-SHS</span>
        </div>
        <div class="center">
            Admin
        </div>
        <div class="right">
            <button class="profile-btn" type="button">
                <img src="../../Assets/admin_profile.png">
            </button>
            <div class="profile-dropdown">
                <a href="home.php">Home</a>
                <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
            </div>
        </div>
    </div>

   

    <!-- Dashboard -->
    <div class="dashboard">
        <div class="dashboard-box">
            <div class="dashboard-wrapper">

                <div class="dashboard-container">
                    <a href="admin_teacher_application_list.php" class="dashboard-card">
                        <img src="../../Assets/teacher_application_image.png" alt="Teacher Applications">
                        <h3>Teacher Applications</h3>
                    </a>
                </div>

                <div class="dashboard-container">
                    <a href="admin_student_application_list.php" class="dashboard-card">
                        <img src="../../Assets/student_application_image.jpg" alt="Student Applications">
                        <h3>Student Applications</h3>
                    </a>
                </div>

                <div class="dashboard-container">
                    <a href="../Teacher_Online_Form.php" class="dashboard-card">
                        <img src="../../Assets/teacher_enrollment_image.png" alt="Enroll Teacher">
                        <h3>Enroll New Teacher</h3>
                    </a>
                </div>

                <div class="dashboard-container">
                    <a href="../Student_Online_Form.php" class="dashboard-card">
                        <img src="../../Assets/student_enrollment_image.png" alt="Enroll Student">
                        <h3>Enroll New Student</h3>
                    </a>
                </div>

                

            </div>
        </div>
    </div>

     <!-- Back Button -->
                <div class="back-button-container">
                    <a href="home.php" class="back-button">← Back to Home</a>
                </div>

    <!-- Footer -->
    <div class="footer">
        © 2026 Cagayan De Oro National High School - Senior High School  
        <br>
        School Management System
    </div>
    
    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>
