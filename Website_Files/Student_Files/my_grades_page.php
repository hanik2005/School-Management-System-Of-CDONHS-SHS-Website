<?php
include "../../Back_End_Files/PHP_Files/my_grades_backend.php";
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
    <link rel="stylesheet" href="../../Design/student/my_grades_design.css">
    <title>Student My Grades Page</title>
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
            <?= htmlspecialchars($gradeLevel) ?>, 
            <?= htmlspecialchars($strandName) ?>, 
            <?= htmlspecialchars($sectionName) ?>
        <?php elseif($Promoted):?>
            Promoted
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

<div class="grades-container">
<h2>My Grades</h2>

<!-- ================= FILTER ================= -->
<form method="GET" class="filter-box">
    <label>Grade Level:</label>
    <select name="grade_level">
        <option value="" <?= $grade_level == '' ? 'selected' : '' ?>>Select</option>
        <?php
        $gradesLevels = ["11" => "Grade 11", "12" => "Grade 12"];
        foreach ($gradesLevels as $level => $label) {
            $selected = ($grade_level == $level) ? 'selected' : '';
            echo "<option value='$level' $selected>$label</option>";
        }
        ?>
    </select>

    <label>Strand:</label>
    <select name="strand">
        <option value="" <?= $strand == '' ? 'selected' : '' ?>>Select</option>
        <?php
        $sqlStrands = "SELECT strand_id, strand_name FROM strands ORDER BY strand_name";
        $resultStrands = mysqli_query($connection, $sqlStrands);
        while ($rowStrand = mysqli_fetch_assoc($resultStrands)) {
            $selected = ($strand == $rowStrand['strand_id']) ? 'selected' : '';
            echo "<option value='" . $rowStrand['strand_id'] . "' $selected>" . htmlspecialchars($rowStrand['strand_name']) . "</option>";
        }
        ?>
    </select>

    <label>Quarter:</label>
    <select name="quarter">
        <option value="" <?= $quarter == '' ? 'selected' : '' ?>>Select</option>
        <?php
        $quarters = [
            "1" => "1st Quarter",
            "2" => "2nd Quarter",
            "3" => "3rd Quarter",
            "4" => "4th Quarter",
            "all" => "All"
        ];
        foreach ($quarters as $qVal => $qLabel) {
            $selected = ($quarter == $qVal) ? 'selected' : '';
            echo "<option value='$qVal' $selected>$qLabel</option>";
        }
        ?>
    </select>

    <button type="submit" class="search-btn">Search</button>
</form>

<!-- ================= TABLE ================= -->
<?php if ($showTable): ?>
<div class="table-box">
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Subject</th>
            <?php if ($quarter === "all"): ?>
                <th>1st Quarter</th>
                <th>2nd Quarter</th>
                <th>3rd Quarter</th>
                <th>4th Quarter</th>
            <?php else: ?>
                <th><?= $quarters[$quarter] ?></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php if (count($grades) > 0): ?>
        <?php foreach ($grades as $subject): ?>
            <tr>
                <td><?= htmlspecialchars($subject['subject_name']) ?></td>

                <?php if ($quarter === "all"): ?>
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                        <td>
                            <?= isset($subject['grades'][$q]) && $subject['grades'][$q] !== null ? 
                                htmlspecialchars($subject['grades'][$q]) : '-' ?>
                        </td>
                    <?php endfor; ?>
                <?php else: ?>
                    <td>
                        <?php 
                        $qNum = (int)$quarter;
                        echo isset($subject['grades'][$qNum]) && $subject['grades'][$qNum] !== null ? 
                            htmlspecialchars($subject['grades'][$qNum]) : '-';
                        ?>
                    </td>
                <?php endif; ?>

            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="<?= $quarter === 'all' ? 5 : 2 ?>">No grades found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="average-box">
    <?php if ($quarter !== 'all'): ?>
        Quarter Average: <?= $quarterAverage ?? '-' ?>
    <?php endif; ?>
</div>

<?php if ($quarter === "all"): ?>
<div class="overall-average">
    Overall Average: <?= $overallAverage ?? '-' ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

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
