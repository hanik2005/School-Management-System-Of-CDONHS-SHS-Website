<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* Verify student session */
$stmt = $connection->prepare("
    SELECT * FROM users 
    WHERE user_id = ? 
    AND school_id = ? 
    AND role_id = 1
");

$stmt->execute([
    $_SESSION['user_id'],
    $_SESSION['school_id']
]);

$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
include "../../Back_End_Files/PHP_Files/check_enrollment.php";

if($isEnlisted){
    header("Location: home.php");
    exit;
}

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
     <link rel="stylesheet" href="../../Design/student/enlistment.css">
    <title>Student Enlistment</title>
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
    <?php elseif($isRejected):?>
        Rejected Enlistment
    <?php else: ?>
        Not enrolled yet
    <?php endif; ?>
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



  <!-- MAIN CONTENT -->
<div class="enlistment-container">

    <h2>Enlistment By Student</h2>

    <!-- SINGLE FORM FOR EVERYTHING -->
    <form id="enlistment-form">

        <div class="enlistment-content">

            <!-- LEFT PANEL -->
            <div class="left-panel">
                <h3>GradeLevel and Strand</h3>

                <label>Grade Level:</label>
                <select id="grade_level" name="grade_level" required>
                    <option value="">Select Grade Level</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                </select>

                <label>Strand:</label>
                <select id="strand" name="strand" required>
                    <option value="">Select Strand</option>
                </select>

                <label>Section:</label>
                <select id="section" name="section" required>
                    <option value="">Select Section</option>
                </select>
            </div>

            <!-- RIGHT PANEL -->
            <div class="right-panel">
                <h3>Subjects</h3>
                <table class="subject-table">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Enlist</th>
                        </tr>
                    </thead>
                    <tbody id="subjects-container">
                        <!-- Subjects will load here -->
                    </tbody>
                </table>

                 <!-- SINGLE SUBMIT BUTTON -->
                <button type="submit" class="submit-btn">Submit Enlistment</button>
            </div>

        </div>

       

    </form>

</div>

  










    <!-- footer -->
    <div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Management System
    </div>
<script src="../../Back_End_Files/JSCRIPT_Files/enlistment_get_boxes.js"></script>
<script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>