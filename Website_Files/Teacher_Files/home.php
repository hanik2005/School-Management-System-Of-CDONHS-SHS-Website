<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

$stmt = $connection->prepare("
    SELECT u.*, ta.profile_image 
    FROM users u
    INNER JOIN teachers s ON s.user_id = u.user_id
    INNER JOIN teacher_applications ta ON s.application_id = ta.teacher_application_id
    WHERE u.user_id = ? AND u.role_id = 3
");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Set profile image path
$profileImagePath = !empty($user['profile_image']) 
    ? "../../uploads/Profile/teacher/" . htmlspecialchars($user['profile_image']) 
    : "../../Assets/profile_button.png";

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
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
</head>
<body>
    <!-- header -->
    <div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>

    <?php include "../../Back_End_Files/PHP_Files/get_teacher_advisory.php"; ?>
    <div class="center">
        Advisory: <?php echo htmlspecialchars($advisoryText); ?>
    </div>


    <div class="right">
       <button class="profile-btn" type="button">
         <img src="<?php echo $profileImagePath; ?>">
     </button>

    <div class="profile-dropdown">
        <a href="profile_page.php">View Profile</a>
        <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>

    </div>
    </div>
    </div>


    <div class="dashboard">

  <div class="dashboard-box">

    <div class="dashboard-wrapper">

    <div class="dashboard-container">
        <a href="profile_page.php" class="dashboard-card">
            <img src="../../Assets/profile_button.png">
            <h3>My Profile</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="class_list.php" class="dashboard-card">
            <img src="../../Assets/class_list_button.png">
            <h3>Class List</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="grading_page.php" class="dashboard-card">
            <img src="../../Assets/grades_button.png">
            <h3>Grading Management</h3>
        </a>
    </div>

    <div class="dashboard-container">
        <a href="student_progress_page.php" class="dashboard-card">
            <img src="../../Assets/progress_button.png">
            <h3>Student Progress</h3>
        </a>
    </div>

      <div class="dashboard-container">
        <a href="form_137_138_page.php" class="dashboard-card">
            <img src="../../Assets/form_137_138_image.png">
            <h3>Form 137 or 138</h3>
        </a>
    </div>


    <div class="dashboard-container">
        <a href="honor_list.php" class="dashboard-card">
            <img src="../../Assets/honors_list_image.png">
            <h3>Honor List</h3>
        </a>
    </div>

</div>
</div>


  </div>




     <!-- footer -->
    <div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Management System
    </div>
<script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>
