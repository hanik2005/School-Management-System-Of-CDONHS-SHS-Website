<?php
if (!isset($_SESSION)) {
    session_start();
}

include "../../DB_Connection/Connection.php";

// Default values
$student_id = null;
$isEnlisted = false;
$isPending = false;
$isRejected = false;
$Promoted = false;
$gradeLevel = null;
$strandName = null;
$sectionName = null;

// Get student_id
$stmtStudent = $connection->prepare("
    SELECT student_id, enlistment_status 
    FROM students 
    WHERE school_id = ?
");
$stmtStudent->bind_param("i", $_SESSION['school_id']);
$stmtStudent->execute();
$resStudent = $stmtStudent->get_result();
$studentRow = $resStudent->fetch_assoc();
$stmtStudent->close();

if ($studentRow) {
    $student_id = $studentRow['student_id'];
    if ($studentRow['enlistment_status'] === 'Enlisted') {
        $isEnlisted = true;

        // Get program info
        $stmtProgram = $connection->prepare("
            SELECT ss.grade_level, s.strand_name, sec.section_name
            FROM student_strand ss
            LEFT JOIN strands s ON ss.strand_id = s.strand_id
            LEFT JOIN section sec ON ss.section_id = sec.section_id
            WHERE ss.student_id = ?
            ORDER BY ss.grade_level DESC
            LIMIT 1
        ");
        $stmtProgram->bind_param("i", $student_id);
        $stmtProgram->execute();
        $resProgram = $stmtProgram->get_result();
        if ($row = $resProgram->fetch_assoc()) {
            $gradeLevel = $row['grade_level'];
            $strandName = $row['strand_name'];
            $sectionName = $row['section_name'];
        }
        $stmtProgram->close();
    }elseif ($studentRow['enlistment_status'] === 'Pending') {
        # code...
        $isPending = true;
    }elseif ($studentRow['enlistment_status'] === 'Rejected') {
        # code...
        $isRejected = true;
    }elseif ($studentRow['enlistment_status'] === 'Promoted') {
        # code...
        $Promoted = true;
    }
}
