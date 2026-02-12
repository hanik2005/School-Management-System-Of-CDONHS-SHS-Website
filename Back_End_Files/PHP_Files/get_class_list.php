<?php
include "../../DB_Connection/Connection.php";

if (!isset($_SESSION['school_id'])) {
    exit("Unauthorized access");
}

$school_id = $_SESSION['school_id'];

/* STEP 1: Get teacher_id of the logged-in teacher */
$getTeacher = $connection->prepare("
    SELECT teacher_id 
    FROM teachers 
    WHERE school_id = ?
    LIMIT 1
");
$getTeacher->bind_param("i", $school_id);
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
    sa.gender,
    ss.grade_level,
    ss.strand_id,
    ss.section_id
FROM teacher_advisory ta
JOIN student_strand ss 
    ON ta.strand_id = ss.strand_id
    AND ta.grade_level = ss.grade_level
    AND ta.section_id = ss.section_id
JOIN students s
    ON ss.student_id = s.student_id
JOIN student_applications sa
    ON s.application_id = sa.application_id
WHERE ta.teacher_id = ?
ORDER BY sa.last_name ASC

");

$stmt->bind_param("i", $teacher_id);
$stmt->execute();

$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

$stmt->close();
$connection->close();

/* $students now contains your class list */
?>
