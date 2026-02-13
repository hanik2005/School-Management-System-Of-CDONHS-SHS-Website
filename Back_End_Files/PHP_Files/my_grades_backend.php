<?php
/* ========================= */
/* my_grades_backend.php     */
/* ========================= */
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

$user_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

/* ========================= */
/* VERIFY STUDENT ACCOUNT    */
/* ========================= */
$sqlUser = "SELECT * FROM users WHERE user_id = ? AND school_id = ? AND role_id = 1";
$stmtUser = mysqli_prepare($connection, $sqlUser);
mysqli_stmt_bind_param($stmtUser, "ii", $user_id, $school_id);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

/* ========================= */
/* GET STUDENT ID & INFO     */
/* ========================= */
$sqlStudent = "
    SELECT s.student_id, ss.strand_id, ss.grade_level, ss.section_id
    FROM students s
    JOIN student_applications sa ON s.application_id = sa.application_id
    JOIN student_strand ss ON s.student_id = ss.student_id
    WHERE sa.lrn = ? AND s.school_id = ?
";
$stmtStudent = mysqli_prepare($connection, $sqlStudent);
mysqli_stmt_bind_param($stmtStudent, "si", $user['username'], $school_id);
mysqli_stmt_execute($stmtStudent);
$resultStudent = mysqli_stmt_get_result($stmtStudent);
$student = mysqli_fetch_assoc($resultStudent);

if (!$student) {
    die("Student ID not linked to this account.");
}

$student_id = $student['student_id'];
$grade_level_default = $student['grade_level'];
$strand_default = $student['strand_id'];
$section_id = $student['section_id'];

/* ========================= */
/* GET FILTER VALUES         */
/* ========================= */
$grade_level = $_GET['grade_level'] ?? '';
$strand = $_GET['strand'] ?? '';
$quarter = $_GET['quarter'] ?? '';

/* Only show table if student clicked Search */
$showTable = isset($_GET['grade_level']) && isset($_GET['strand']) && isset($_GET['quarter']);

$grades = [];
$quarterAverage = null;
$overallAverage = null;

if ($showTable) {

    // Determine if we should get current or archived strand
    $isCurrent = true;
    if (isset($_GET['archived']) && $_GET['archived'] == '1') {
        $isCurrent = false;
    }

    // 1️⃣ Get all enrolled subjects for this student, grade, strand
    if ($isCurrent) {
        $sqlEnrolled = "
            SELECT ss.subject_id
            FROM student_subjects ss
            JOIN subject s ON ss.subject_id = s.subject_id
            WHERE ss.student_id = ? 
            AND ss.status = 'Enrolled'
            AND s.grade_level = ?
            AND s.strand_id = ?
            AND ss.school_year = ?
        ";
        $stmtEnrolled = mysqli_prepare($connection, $sqlEnrolled);
        $school_year = date("Y") . "-" . (date("Y")+1); // adjust school year as needed
        mysqli_stmt_bind_param($stmtEnrolled, "iiis", $student_id, $grade_level, $strand, $school_year);
    } else {
        $sqlEnrolled = "
            SELECT ss.subject_id
            FROM student_subjects ss
            JOIN subject s ON ss.subject_id = s.subject_id
            JOIN archived_student_strand a ON a.student_id = ss.student_id
            WHERE ss.student_id = ?
            AND ss.status = 'Enrolled'
            AND s.grade_level = a.grade_level
            AND s.strand_id = a.strand_id
            AND a.grade_level = ?
            AND a.strand_id = ?
        ";
        $stmtEnrolled = mysqli_prepare($connection, $sqlEnrolled);
        mysqli_stmt_bind_param($stmtEnrolled, "iii", $student_id, $grade_level, $strand);
    }

    mysqli_stmt_execute($stmtEnrolled);
    $resultEnrolled = mysqli_stmt_get_result($stmtEnrolled);

    $enrolledSubjects = [];
    while ($row = mysqli_fetch_assoc($resultEnrolled)) {
        $enrolledSubjects[] = $row['subject_id'];
    }
    mysqli_stmt_close($stmtEnrolled);

    // 2️⃣ Fetch grades for selected quarter or all
    $sqlGrades = "
        SELECT g.subject_id, s.subject_name, g.grade, g.quarter
        FROM grade_entry g
        JOIN subject s ON g.subject_id = s.subject_id
        WHERE g.student_id = ?
    ";
    $types = "i";
    $params = [$student_id];

    if (!empty($grade_level)) {
        $sqlGrades .= " AND s.grade_level = ?";
        $types .= "i";
        $params[] = $grade_level;
    }
    if (!empty($strand)) {
        $sqlGrades .= " AND s.strand_id = ?";
        $types .= "i";
        $params[] = $strand;
    }
    if (!empty($quarter) && $quarter !== "all") {
        $sqlGrades .= " AND g.quarter = ?";
        $types .= "i";
        $params[] = $quarter;
    }

    $sqlGrades .= " ORDER BY s.subject_name";

    $stmtGrades = mysqli_prepare($connection, $sqlGrades);
    mysqli_stmt_bind_param($stmtGrades, $types, ...$params);
    mysqli_stmt_execute($stmtGrades);
    $resultGrades = mysqli_stmt_get_result($stmtGrades);

    $gradesWithScore = [];
    $quarterTotal = 0;
    $gradedSubjects = [];

    while ($row = mysqli_fetch_assoc($resultGrades)) {
        $gradesWithScore[] = $row;
        if ($row['grade'] !== null) {
            $quarterTotal += $row['grade'];
            $gradedSubjects[] = $row['subject_id'];
        }
    }
    mysqli_stmt_close($stmtGrades);

    $grades = $gradesWithScore;

    // 3️⃣ Calculate quarter average ONLY if all enrolled subjects have a grade
    if ($quarter !== "all" && count($enrolledSubjects) > 0) {
        $allGraded = empty(array_diff($enrolledSubjects, $gradedSubjects));
        if ($allGraded) {
            $quarterAverage = round($quarterTotal / count($gradedSubjects), 2);
        }
    }

    // 4️⃣ Calculate overall average only if "all" is selected
    if ($quarter === "all") {
        $overallTotal = 0;
        $overallCount = 0;
        foreach ($grades as $g) {
            if ($g['grade'] !== null) {
                $overallTotal += $g['grade'];
                $overallCount++;
            }
        }
        if ($overallCount > 0) {
            $overallAverage = round($overallTotal / $overallCount, 2);
        }
    }
}
?>
