<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include "../../DB_Connection/Connection.php"; // $connection is mysqli

$input = json_decode(file_get_contents('php://input'), true);

$student_id  = $_SESSION['user_id'];
$grade_level = $input['grade_level'] ?? null;
$strand_id   = $input['strand_id'] ?? null;
$section_id  = $input['section_id'] ?? null;
$subjects    = $input['subjects'] ?? [];

if (!$grade_level || !$strand_id || !$section_id || empty($subjects)) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data']);
    exit;
}

$school_year = date('Y') . '-' . (date('Y') + 1); // e.g., 2026-2027

// Start mysqli transaction
$connection->begin_transaction();

try {
    // Insert into student_strand
    $stmt = $connection->prepare("INSERT INTO student_strand (student_id, strand_id, grade_level, section_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $student_id, $strand_id, $grade_level, $section_id);
    $stmt->execute();
    $stmt->close();

    // Insert subjects
    $stmtSubj = $connection->prepare("INSERT INTO student_subjects (student_id, subject_id, school_year) VALUES (?, ?, ?)");
    foreach ($subjects as $subject_id) {
        $stmtSubj->bind_param("iis", $student_id, $subject_id, $school_year);
        $stmtSubj->execute();
    }
    $stmtSubj->close();

    $connection->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
