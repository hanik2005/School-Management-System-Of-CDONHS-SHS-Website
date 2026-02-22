<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* Verify student session and get profile image */
$stmt = $connection->prepare("
    SELECT u.*, sa.profile_image 
    FROM users u
    INNER JOIN students s ON s.user_id = u.user_id
    INNER JOIN student_applications sa ON s.application_id = sa.application_id
    WHERE u.user_id = ? AND u.school_id = ? AND u.role_id = 1
");

$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['school_id']);
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
    ? "../../uploads/" . htmlspecialchars($user['profile_image']) 
    : "../../Assets/profile_button.png";

include "../../Back_End_Files/PHP_Files/check_enrollment.php";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="../../Design/main_design.css">
     <link rel="stylesheet" href="../../Design/profile_dropdown.css">
     <link rel="stylesheet" href="../../Design/dashboard_design.css">
    <title>Multiple Enlistment Page</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
</head>
<body>

<!-- header -->
    <div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>

    <?php include "../../Back_End_Files/PHP_Files/get_student_program.php"; ?>
    <div class="center">
        Program:
        <?php if ($isEnlisted): ?>
        <?php echo htmlspecialchars($gradeLevel); ?>, 
        <?php echo htmlspecialchars($strandName); ?>, 
        <?php echo htmlspecialchars($sectionName); ?>

    <?php elseif($isPending):?>
        Pending Enlistment
    <?php elseif($isRejected):?>
        Rejected Enlistment
    <?php elseif($Promoted):?>
        Promoted
    <?php else: ?>
        Not enrolled yet
    <?php endif; ?>
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
    <?php if (!$isEnlisted && !$isPending && !$Promoted): ?>
        <a href="student_enlistment.php" class="dashboard-card">
            <img src="../../Assets/enlistment_button.png">
            <h3>Enlistment</h3>
        </a>
    <?php elseif ($isPending): ?>
        <div class="dashboard-card" style="opacity:0.5; pointer-events:none;">
            <img src="../../Assets/enlistment_button.png">
            <h3>Pending Enlistment</h3>
        </div>
    <?php else: ?>
        <div class="dashboard-card" style="opacity:0.5; pointer-events:none;">
            <img src="../../Assets/enlistment_button.png">
            <h3>Already Enlisted</h3>
        </div>
    <?php endif; ?>
    </div>


    <div class="dashboard-container">
        <?php if ($Promoted): ?>
        <a href="student_promoted_enlistment.php" class="dashboard-card">
            <img src="../../Assets/enlistment_button.png">
            <h3>Promoted Enlistment</h3>
        </a>
        <?php else: ?>
        <div class="dashboard-card" style="opacity:0.5; pointer-events:none;">
            <img src="../../Assets/enlistment_button.png">
            <h3>Not Promoted Yet</h3>
        </div>
        <?php endif; ?>
    </div>

     

</div>


  </div>

</div>

    <!-- Back Button -->
    <div class="back-button-container">
        <a href="home.php" class="back-button">← Back to Home</a>
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
