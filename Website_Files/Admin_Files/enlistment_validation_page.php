<?php
session_start();
include "../../DB_Connection/Connection.php";

// Check session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

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

/* ================= FILTER ================= */

$grade = $_GET['grade_level'] ?? '';
$strand = $_GET['strand_id'] ?? '';
$section = $_GET['section_id'] ?? '';

/* ----------------- GET FILTER OPTIONS ----------------- */

// Get all strands
$strandQuery = $connection->query("SELECT strand_id, strand_name FROM strands ORDER BY strand_name");
$strands = [];
while ($s = $strandQuery->fetch_assoc()) $strands[] = $s;

// Get sections: if strand selected, only sections for that strand
$sectionSql = "SELECT DISTINCT sec.section_id, sec.section_name
               FROM section sec
               JOIN student_strand ss ON ss.section_id = sec.section_id
               WHERE 1=1";
if (!empty($strand)) {
    $strand_id = (int)$strand;
    $sectionSql .= " AND ss.strand_id = $strand_id";
}
$sectionSql .= " ORDER BY sec.section_name";

$sectionQuery = $connection->query($sectionSql);
$sections = [];
while ($sec = $sectionQuery->fetch_assoc()) $sections[] = $sec;

/* ----------------- FETCH STUDENTS ----------------- */

$sql = "
SELECT s.student_id,
       sa.lrn,
       sa.first_name,
       sa.last_name,
       ss.grade_level,
       st.strand_name,
       sec.section_name,
       s.enlistment_status
FROM students s
JOIN student_applications sa ON s.application_id = sa.application_id
LEFT JOIN student_strand ss ON s.student_id = ss.student_id
LEFT JOIN strands st ON ss.strand_id = st.strand_id
LEFT JOIN section sec ON ss.section_id = sec.section_id
WHERE s.enlistment_status = 'Pending'
";

$params = [];
$types = "";

// Grade filter
if (!empty($grade)) {
    $sql .= " AND ss.grade_level = ?";
    $params[] = $grade;
    $types .= "s";
}

// Strand filter
if (!empty($strand)) {
    $sql .= " AND st.strand_id = ?";
    $params[] = $strand;
    $types .= "i";

    // Section filter only if strand is selected
    if (!empty($section)) {
        $sql .= " AND sec.section_id = ?";
        $params[] = $section;
        $types .= "i";
    }
}

$stmt = $connection->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Enlistment Validation</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/dashboard_design.css">
    <link rel="stylesheet" href="../../Design/admin/admin_enlistment_validation.css">

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

    <div class="enlistment-container">
    <div class="enlistment-box">
        <h2>Enlistment Validation</h2>

        <!-- ================= FILTER SECTION ================= -->
        <form method="GET">
    <div class="filter-section">

        <div class="filter-group">
            <label>Grade Level:</label>
            <select name="grade_level" class="filter-dropdown">
                <option value="">Select Grade Level</option>
                <option value="11" <?= ($grade=='11')?'selected':'' ?>>11</option>
                <option value="12" <?= ($grade=='12')?'selected':'' ?>>12</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Strand:</label>
            <select name="strand_id" class="filter-dropdown" onchange="this.form.submit()">
                <option value="">Select Strand</option>
                <?php foreach ($strands as $s): ?>
                    <option value="<?= $s['strand_id'] ?>" <?= ($strand == $s['strand_id'])?'selected':'' ?>>
                        <?= $s['strand_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Section:</label>
            <select name="section_id" class="filter-dropdown" <?= empty($strand)?'disabled':'' ?>>
                <option value="">Select Section</option>
                <?php foreach ($sections as $sec): ?>
                    <option value="<?= $sec['section_id'] ?>" <?= ($section == $sec['section_id'])?'selected':'' ?>>
                        <?= $sec['section_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-buttons">
            <button type="submit" class="search-btn">Search</button>
            <a href="enlistment_validation_page.php" class="clear-filter-btn">Clear</a>
        </div>
    </div>
</form>


        <!-- ================= TABLE SECTION ================= -->

        <form method="POST" action="../../Back_End_Files/PHP_Files/admin_enlistment_validation_backend.php">

        <div class="table-container">
            <table class="validation-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>LRN</th>
                        <th>Student Name</th>
                        <th>Grade Level</th>
                        <th>Strand</th>
                        <th>Section</th>
                        <th>Enlistment Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;

                // Use the already prepared query with filters
                $stmt = $connection->prepare($sql);

                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()):
                    // Combine first name + last name
                    $student_name = $row['first_name'] . ' ' . $row['last_name'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['lrn'] ?></td>
                    <td><?= $student_name ?></td>
                    <td><?= $row['grade_level'] ?? '' ?></td>
                    <td><?= $row['strand_name'] ?? '' ?></td>
                    <td><?= $row['section_name'] ?? '' ?></td>
                    <td>
                        <select name="status[<?= $row['student_id'] ?>]" class="status-dropdown">
                            <option value="Pending" <?= $row['enlistment_status']=="Pending" ? "selected" : "" ?>>Pending</option>
                            <option value="Enlisted" <?= $row['enlistment_status']=="Enlisted" ? "selected" : "" ?>>Enlisted</option>
                            <option value="Rejected" <?= $row['enlistment_status']=="Rejected" ? "selected" : "" ?>>Rejected</option>
                        </select>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="validation-buttons">
            <button type="submit" name="confirm" class="confirm-btn">Confirm</button>
            <a href="enlistment_validation_page.php" class="clear-filter-btn">Clear</a>
        </div>

        </form>

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
