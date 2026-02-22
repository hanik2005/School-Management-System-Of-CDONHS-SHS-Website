<?php
ob_start(); // start output buffering
error_reporting(E_ERROR | E_PARSE);  // only fatal errors
header('Content-Type: application/json');

include "../../DB_Connection/Connection.php";

// read JSON from frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['updates'])) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$updatedCount = 0;

foreach ($data['updates'] as $item) {

    // validate item
    if (!isset($item['status'], $item['section'], $item['quarter'])) {
        continue; // skip invalid items
    }

    $status = $item['status'];
    $section_id = (int)$item['section'];
    $quarter = (int)$item['quarter'];

    // prepare query - exclude archived (promoted) students
    $stmt = $connection->prepare("
        UPDATE grade_entry ge
        JOIN section sec ON sec.section_id = ge.section_id
        SET ge.grade_status = ?
        WHERE sec.section_id = ?
        AND ge.quarter = ?
        AND NOT EXISTS (
            SELECT 1 FROM archived_student_strand ass 
            WHERE ass.student_id = ge.student_id 
            AND ass.section_id = ge.section_id
        )
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: '.$connection->error]);
        exit;
    }

    $stmt->bind_param("sii", $status, $section_id, $quarter);
    $stmt->execute();

    $updatedCount += $stmt->affected_rows; // count updated rows
    $stmt->close();
}

ob_end_clean(); // clear any stray output

echo json_encode([
    'success' => true,
    'message' => "Grades updated successfully. Total rows updated: $updatedCount"
]);
exit;
