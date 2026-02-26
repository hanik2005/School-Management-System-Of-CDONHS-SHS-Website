<?php

if (!isset($_SESSION)) {
    session_start();
}

include "../../DB_Connection/Connection.php";

$student_id = null;
$enlistmentStatus = 'Not Enlisted';
$isEnlisted = false;

/* ✅ GET student_id AND enlistment_status */
$getStudent = $connection->prepare("
    SELECT student_id, enlistment_status 
    FROM students 
    WHERE user_id = ?
");

$getStudent->bind_param("i", $_SESSION['user_id']);
$getStudent->execute();

$result = $getStudent->get_result()->fetch_assoc();

if ($result) {

    $student_id = $result['student_id'];
    $enlistmentStatus = $result['enlistment_status'];

    if ($enlistmentStatus === 'Enlisted') {
        $isEnlisted = true;
    }
}
?>
