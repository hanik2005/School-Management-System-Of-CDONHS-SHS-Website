<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";


/* VERIFY TEACHER SESSION */
$stmt = $connection->prepare("
    SELECT u.*, ta.profile_image 
    FROM users u
    INNER JOIN teachers s ON s.user_id = u.user_id
    INNER JOIN teacher_applications ta ON s.application_id = ta.teacher_application_id
    WHERE u.user_id = ? AND u.school_id = ? AND u.role_id = 3
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

include "../../Back_End_Files/PHP_Files/get_grading_data.php";
include "../../Back_End_Files/PHP_Files/save_grades.php";

?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
 <link rel="stylesheet" href="../../Design/main_design.css">
 <link rel="stylesheet" href="../../Design/profile_dropdown.css">
 <link rel="stylesheet" href="../../Design/teacher/grading_design.css">
 <title>Grading Dashboard</title>
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

<div class="dashboard-container">

    <h2>Grading Dashboard</h2>

    <?php if (isset($_GET['success'])) : ?>
        <p style="color:green;">Grades Saved Successfully!</p>
    <?php endif; ?>

    <!-- SUBJECT + QUARTER SELECT -->
    <form method="GET">

        Subject:
        <select name="subject" onchange="this.form.submit()">
            <?php foreach ($subjects as $sub) : ?>
                <option value="<?= $sub['subject_id'] ?>"
                    <?= $subject_id == $sub['subject_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sub['subject_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        Quarter:
        <select name="quarter" onchange="this.form.submit()">
            <?php for ($q = 1; $q <= 4; $q++) : ?>
                <option value="<?= $q ?>" <?= $quarter == $q ? 'selected' : '' ?>>
                    Quarter <?= $q ?>
                </option>
            <?php endfor; ?>
        </select>

    </form>

    <br>

    <form method="POST">

        <table class="grade-table">

            <tr>
                <th>No</th>
                <th>LRN</th>
                <th>Student Name</th>
                <th>Grade</th>
            </tr>

            <?php if (!empty($students)) : ?>

                <?php $count = 1; foreach ($students as $student) : ?>

                    <tr>
                        <td><?= $count++ ?></td>

                         <td>
                            <?= htmlspecialchars($student['lrn']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($student['student_name']) ?>
                        </td>

                        <td>
                            <input type="number"
                                   name="grades[<?= $student['student_id'] ?>]"
                                   value="<?= isset($student['grade']) && $student['grade'] ? $student['grade'] : 60 ?>"
                                   min="0"
                                   max="100">
                        </td>
                    </tr>

                <?php endforeach; ?>

            <?php else : ?>

                <tr>
                    <td colspan="3">No students found.</td>
                </tr>

            <?php endif; ?>

        </table>

        <br>

        <button type="submit" class="confirm-btn">Submit Grades</button>
        <button type="reset" class="clear-btn">Clear</button>
        <a href="home.php" class="back-btn">Back to Home</a>
        

    </form>

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