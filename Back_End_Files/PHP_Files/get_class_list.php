<?php
include "../../DB_Connection/Connection.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized access");
}

$user_id = $_SESSION['user_id'];

/* STEP 1: Get teacher_id of the logged-in teacher */
$getTeacher = $connection->prepare("
    SELECT teacher_id 
    FROM teachers 
    WHERE user_id = ?
    LIMIT 1
");
$getTeacher->bind_param("i", $user_id);
$getTeacher->execute();
$teacherResult = $getTeacher->get_result();
$teacherData = $teacherResult->fetch_assoc();

if (!$teacherData) {
    exit("Teacher record not found.");
}

$teacher_id = $teacherData['teacher_id'];

/* STEP 2: Get students under teacher advisory */
$stmt = $connection->prepare("
SELECT 
    sa.last_name,
    sa.first_name,
    sa.lrn,
    sa.sex,
    ss.grade_level,
    ss.strand_id,
    ss.section_id,
    s.enlistment_status,
    st.strand_name,
    sec.section_name
FROM teacher_advisory ta
JOIN student_strand ss 
    ON ta.strand_id = ss.strand_id
    AND ta.grade_level = ss.grade_level
    AND ta.section_id = ss.section_id
JOIN students s
    ON ss.student_id = s.student_id
JOIN student_applications sa
    ON s.application_id = sa.application_id
JOIN strands st ON ta.strand_id = st.strand_id
JOIN section sec ON ta.section_id = sec.section_id
WHERE ta.teacher_id = ?
AND s.enlistment_status = 'Enlisted'
ORDER BY sa.last_name ASC
");


$stmt->bind_param("i", $teacher_id);
$stmt->execute();

$result = $stmt->get_result();

$students = [];
$strandName = '';
$sectionName = '';
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
    // Get strand and section name from first student
    if (empty($strandName) && !empty($row['strand_name'])) {
        $strandName = $row['strand_name'];
        $sectionName = $row['section_name'];
    }
}

$stmt->close();
$connection->close();

/* $students now contains your class list */
?>
