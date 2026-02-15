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
    AND role_id = 2
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
include "../../Back_End_Files/PHP_Files/grade_validation_backend.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Grade Validation</title>
<link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
<link rel="stylesheet" href="../../Design/main_design.css">
<link rel="stylesheet" href="../../Design/profile_dropdown.css">
<link rel="stylesheet" href="../../Design/dashboard_design.css">
<link rel="stylesheet" href="../../Design/admin/admin_grade_validation.css">

</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png">
        <span>CDONSHS-SHS</span>
    </div>

    <div class="center">Admin</div>

    <div class="right">
        <button class="profile-btn">
            <img src="../../Assets/admin_profile.png">
        </button>

        <div class="profile-dropdown">
            <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
        </div>
    </div>
</div>

<!-- MAIN -->
<div class="main-container">

<h2>Grades Validation</h2>

<!-- FILTER -->
<form class="filter-container" id="filterForm">
    <label>Grade Level:</label>
    <select name="grade_level" id="grade_level">
       <option value="" <?= empty($_GET['grade_level']) ? 'selected' : '' ?>>All</option>
       <option value="11" <?= (isset($_GET['grade_level']) && $_GET['grade_level']=='11') ? 'selected' : '' ?>>11</option>
       <option value="12" <?= (isset($_GET['grade_level']) && $_GET['grade_level']=='12') ? 'selected' : '' ?>>12</option>
    </select>

    <label>Quarter:</label>
    <select name="quarter" id="quarter">
        <option value="" <?= empty($_GET['quarter']) ? 'selected' : '' ?>>All</option>
        <option value="1" <?= (isset($_GET['quarter']) && $_GET['quarter']=='1') ? 'selected' : '' ?>>1</option>
        <option value="2" <?= (isset($_GET['quarter']) && $_GET['quarter']=='2') ? 'selected' : '' ?>>2</option>
        <option value="3" <?= (isset($_GET['quarter']) && $_GET['quarter']=='3') ? 'selected' : '' ?>>3</option>
        <option value="4" <?= (isset($_GET['quarter']) && $_GET['quarter']=='4') ? 'selected' : '' ?>>4</option>
    </select>

    <label>Status:</label>
    <select name="status" id="status">
        <option value="" <?= empty($_GET['status']) ? 'selected' : '' ?>>All</option>
        <option value="Draft" <?= (isset($_GET['status']) && $_GET['status']=='Draft') ? 'selected' : '' ?>>Draft</option>
        <option value="Submitted" <?= (isset($_GET['status']) && $_GET['status']=='Submitted') ? 'selected' : '' ?>>Submitted</option>
        <option value="Approved" <?= (isset($_GET['status']) && $_GET['status']=='Approved') ? 'selected' : '' ?>>Approved</option>
    </select>

    

    <button type="submit" class="search-btn">Search</button>
    <button type="button" class="clear-btn" id="clearFilters">Clear</button>
</form>

<!-- VALIDATION TABLE -->
<div class="validation-table-container">
    <table class="validation-table">
        <thead>
            <tr>
                <th>Grade Level</th>
                <th>Strand</th>
                <th>Section</th>
                <th>Quarter</th>
                <th>Subjects Sent</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if($validationData): ?>
            <?php foreach($validationData as $row): ?>
            <tr class="validation-row" data-grade="<?= $row['grade_level'] ?>" data-section="<?= $row['section_name'] ?>" data-quarter="<?= $row['quarter'] ?>">
                <td><?= $row['grade_level'] ?></td>
                <td><?= $row['strand_name'] ?></td>
                <td><?= $row['section_name'] ?></td>
                <td><?= $row['quarter'] ?></td>
                <td><?= $row['subject_count'] ?></td>
                <td>
                    <select class="status-select">
                        <option value="Draft" <?= $row['status']=='Draft'?'selected':'' ?>>Draft</option>
                        <option value="Submitted" <?= $row['status']=='Submitted'?'selected':'' ?>>Submitted</option>
                        <option value="Approved" <?= $row['status']=='Approved'?'selected':'' ?>>Approved</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No Data</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- DASHBOARD -->
<div class="dashboard-panel">
    <h2>Teacher Checking</h2>
    <div id="dashboardContent">
        <p style="text-align:center;">Click a row to display grades</p>
    </div>
    <div class="dashboard-buttons" style="margin-top:10px;">
        <button id="confirmBtn">Confirm</button>
        <button id="clearBtn">Clear</button>
         <a href="home.php" class="back-btn">Back to Home</a>
    </div>
</div>


<!-- FOOTER -->
<div class="footer">
© 2026 Cagayan De Oro National High School - Senior High School
<br>
    School Management System
</div>

<script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
<script src="../../Back_End_Files/JSCRIPT_Files/grade_validation_function.js"></script>
</body>
</html>
