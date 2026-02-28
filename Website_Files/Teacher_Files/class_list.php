<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

$stmt = $connection->prepare("
    SELECT u.*, ta.profile_image, ta.first_name, ta.last_name 
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
      <link rel="stylesheet" href="../../Design/teacher/classList_design.css">
    <title>Teacher Class List</title>
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

    <div class="main-container">

    <!-- Page Title -->
    <div class="page-title">
        <h1>Class List</h1>
    </div>

    <!-- Table Section -->
     <?php include "../../Back_End_Files/PHP_Files/get_class_list.php"?>

<!-- Print Header - Only visible when printing -->
<div class="print-header" style="display: none;">
    <div class="print-school-info">
        <img src="../../Assets/LOGO.png" alt="School Logo" class="print-logo">
        <div class="print-school-details">
            <h1>Cagayan De Oro National High School - Senior High School</h1>
            <p>Class List</p>
        </div>
    </div>
    <div class="print-class-details">
        <table class="print-info-table">
            <tr>
                <td><strong>Grade Level:</strong></td>
                <td><?php echo !empty($students[0]['grade_level']) ? htmlspecialchars($students[0]['grade_level']) : 'N/A'; ?></td>
                <td><strong>Strand:</strong></td>
                <td><?php echo !empty($strandName) ? htmlspecialchars($strandName) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td><strong>Section:</strong></td>
                <td><?php echo !empty($sectionName) ? htmlspecialchars($sectionName) : 'N/A'; ?></td>
                <td><strong>School Year:</strong></td>
                <td><?php echo date('Y') . '-' . (date('Y') + 1); ?></td>
            </tr>
            <tr>
                <td><strong>Adviser:</strong></td>
                <td colspan="3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="table-container">
        <table class="class-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Student Name</th>
                    <th>LRN</th>
                    <th>Sex</th>
                </tr>
            </thead>
            <tbody>
                        <?php if (!empty($students)): ?>
                <?php $count = 1; ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td>
                            <?php 
                                echo htmlspecialchars(
                                    $student['last_name'] . ", " . $student['first_name']
                                ); 
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['lrn']); ?></td>
                        <td><?php echo htmlspecialchars($student['sex']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No students found.</td>
                    </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Print Button -->
    <div class="print-container">
        <button class="print-btn" onclick="window.print()">Print</button>
    </div>

    <!-- Print Footer - Only visible when printing -->
    <div class="print-footer" style="display: none;">
        <div class="signature-line">
            <div class="sign-box">
                <div class="line"></div>
                <p>Prepared by: <br><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></p>
            </div>
            <div class="sign-box">
                <div class="line"></div>
                <p>Checked by: <br><strong>School Principal</strong></p>
            </div>
            <div class="sign-box">
                <div class="line"></div>
                <p>Date Signed:</p>
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
