<?php
// Return JSON only, disable HTML errors in output
header('Content-Type: application/json');

// Log PHP errors to a file instead of displaying them
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/fetch_teacher_grades.log');
error_reporting(E_ALL);

include "../../DB_Connection/Connection.php";


try {
    $grade = isset($_GET['grade']) ? intval($_GET['grade']) : 0;
    $section = isset($_GET['section']) ? $_GET['section'] : '';

    if (!$grade || !$section) {
        echo json_encode([]);
        exit;
    }

    $query = "
    SELECT 
        sa.last_name,
        sa.first_name,
        ge.grade,
        ge.grade_status AS status,
        sub.subject_name
    FROM grade_entry ge
    JOIN students s ON s.student_id = ge.student_id
    JOIN student_applications sa ON sa.application_id = s.application_id
    JOIN section sec ON sec.section_id = ge.section_id
    JOIN subject sub ON sub.subject_id = ge.subject_id
    WHERE sec.grade_level = ?
      AND sec.section_name = ?
    ORDER BY sub.subject_name, sa.last_name
    ";

    $stmt = $connection->prepare($query);

    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);

    $stmt->bind_param("is", $grade, $section);

    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

    $result = $stmt->get_result();
    $grades = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($grades);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
