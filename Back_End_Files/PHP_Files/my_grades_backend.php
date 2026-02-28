<?php
/* ========================= */
/* my_grades_backend.php     */
/* ========================= */
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

$user_id = $_SESSION['user_id'];

/* ========================= */
/* GET STUDENT ACCOUNT       */
/* ========================= */
$sqlStudent = "
    SELECT s.student_id, ss.strand_id, ss.grade_level, ss.section_id, sa.profile_image, s.school_year
    FROM students s
    JOIN student_strand ss ON s.student_id = ss.student_id
    JOIN student_applications sa ON s.application_id = sa.application_id
    WHERE s.user_id = ?
";
$stmtStudent = mysqli_prepare($connection, $sqlStudent);
mysqli_stmt_bind_param($stmtStudent, "i", $user_id);
mysqli_stmt_execute($stmtStudent);
$resultStudent = mysqli_stmt_get_result($stmtStudent);
$student = mysqli_fetch_assoc($resultStudent);

if (!$student) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$student_id = $student['student_id'];
$grade_level_default = $student['grade_level'];
$strand_default = $student['strand_id'];
$section_id = $student['section_id'];
$student_school_year = $student['school_year'];

// Set profile image path
$profileImagePath = !empty($student['profile_image']) 
    ? "../../uploads/Profile/student/" . htmlspecialchars($student['profile_image']) 
    : "../../Assets/profile_button.png";

/* ========================= */
/* GET FILTER VALUES         */
/* ========================= */
$grade_level = $_GET['grade_level'] ?? '';
$strand = $_GET['strand'] ?? '';
$quarter = $_GET['quarter'] ?? '';

$showTable = isset($_GET['grade_level']) && isset($_GET['strand']) && isset($_GET['quarter']);

$grades = [];
$quarterAverage = null;
$overallAverage = null;

if ($showTable) {
    $isCurrent = !(isset($_GET['archived']) && $_GET['archived'] == '1');

    /* ========================= */
    /* FETCH ALL ENROLLED SUBJECTS + APPROVED GRADES */
    /* Only show subjects with status: Completed, Enrolled, or Withdrawn with Grades */
    /* ========================= */
    if ($isCurrent) {
        $sql = "
            SELECT ss.subject_id, s.subject_name, ge.grade, ge.quarter
            FROM student_subjects ss
            JOIN subject s ON ss.subject_id = s.subject_id
            LEFT JOIN grade_entry ge 
                ON ss.student_id = ge.student_id 
                AND ss.subject_id = ge.subject_id
                AND ge.grade_status = 'Approved'
            WHERE ss.student_id = ? 
              AND ss.status IN ('Completed', 'Enrolled', 'Withdrawn with Grades')
              AND s.grade_level = ?
              AND s.strand_id = ?
        ";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $student_id, $grade_level, $strand);
    } else {
        $sql = "
            SELECT ss.subject_id, s.subject_name, ge.grade, ge.quarter
            FROM student_subjects ss
            JOIN subject s ON ss.subject_id = s.subject_id
            LEFT JOIN grade_entry ge 
                ON ss.student_id = ge.student_id 
                AND ss.subject_id = ge.subject_id
                AND ge.grade_status = 'Approved'
            JOIN archived_student_strand a ON a.student_id = ss.student_id
            WHERE ss.student_id = ?
              AND ss.status IN ('Completed', 'Enrolled', 'Withdrawn with Grades')
              AND s.grade_level = a.grade_level
              AND s.strand_id = a.strand_id
              AND a.grade_level = ?
              AND a.strand_id = ?
        ";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $student_id, $grade_level, $strand);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $subjects = [];

    // Organize grades per subject per quarter
    while ($row = mysqli_fetch_assoc($result)) {
        $subId = $row['subject_id'];
        if (!isset($subjects[$subId])) {
            $subjects[$subId] = [
                'subject_id' => $subId,
                'subject_name' => $row['subject_name'],
                'grades' => [1 => null, 2 => null, 3 => null, 4 => null] // default nulls
            ];
        }

        // Assign grade if approved
        if ($row['quarter'] !== null) {
            $subjects[$subId]['grades'][(int)$row['quarter']] = $row['grade'] ?? null;
        }
    }
    mysqli_stmt_close($stmt);

    /* ========================= */
    /* CALCULATE QUARTER & OVERALL AVERAGE ONLY IF ALL GRADES APPROVED */
    /* ========================= */
    if ($quarter !== "all") {
        $quarterTotal = 0;
        $gradedCount = 0;
        $allGraded = true;

        foreach ($subjects as $sub) {
            $gradeValue = $sub['grades'][$quarter] ?? null;
            if ($gradeValue === null) {
                $allGraded = false; // missing grade, do not calculate
            } else {
                $quarterTotal += $gradeValue;
                $gradedCount++;
            }
        }

        if ($allGraded && $gradedCount > 0) {
            $quarterAverage = round($quarterTotal / $gradedCount, 2);
        } else {
            $quarterAverage = null; // keep blank if any grade missing
        }

    } else {
        // Overall average: only if all subjects have all 4 quarters approved
        $overallTotal = 0;
        $overallCount = 0;
        $allQuartersGraded = true;

        foreach ($subjects as $sub) {
            for ($q = 1; $q <= 4; $q++) {
                $gradeValue = $sub['grades'][$q] ?? null;
                if ($gradeValue === null) {
                    $allQuartersGraded = false;
                    break 2; // exit both loops
                } else {
                    $overallTotal += $gradeValue;
                    $overallCount++;
                }
            }
        }

        if ($allQuartersGraded && $overallCount > 0) {
            $overallAverage = round($overallTotal / $overallCount, 2);
        } else {
            $overallAverage = null; // keep blank if any grade missing
        }
    }

    // Convert associative array to indexed array for frontend
    $grades = array_values($subjects);
}
?>
