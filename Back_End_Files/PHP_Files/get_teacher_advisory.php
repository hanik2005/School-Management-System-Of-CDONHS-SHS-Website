<?php
include "../../DB_Connection/Connection.php";
/* ===============================
   GET TEACHER ADVISORY
================================ */
$getTeacher = $connection->prepare("
    SELECT t.teacher_id
    FROM teachers t
    WHERE t.user_id = ?
");

$getTeacher->bind_param("i", $_SESSION['user_id']);
$getTeacher->execute();
$teacherResult = $getTeacher->get_result()->fetch_assoc();

$advisoryText = "No Advisory Assigned";
$advisorySectionId = null;
$advisoryGradeLevel = null;
$advisoryStrandId = null;

if ($teacherResult) {

    $teacher_id = $teacherResult['teacher_id'];

    $getAdvisory = $connection->prepare("
        SELECT 
            ta.grade_level,
            ta.strand_id,
            ta.section_id,
            s.strand_name,
            sec.section_name
        FROM teacher_advisory ta
        JOIN strands s ON ta.strand_id = s.strand_id
        JOIN section sec ON ta.section_id = sec.section_id
        WHERE ta.teacher_id = ?
        LIMIT 1
    ");

    $getAdvisory->bind_param("i", $teacher_id);
    $getAdvisory->execute();
    $advisory = $getAdvisory->get_result()->fetch_assoc();

    if ($advisory) {
        $advisoryText =
            "Grade " . $advisory['grade_level'] .
            " - " . $advisory['strand_name'] .
            " - " . $advisory['section_name'];
        $advisorySectionId = $advisory['section_id'];
        $advisoryGradeLevel = $advisory['grade_level'];
        $advisoryStrandId = $advisory['strand_id'];
    }
}

?>