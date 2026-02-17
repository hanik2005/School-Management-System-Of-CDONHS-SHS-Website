<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/fetch_teacher_grades.log');
error_reporting(E_ALL);

include "../../DB_Connection/Connection.php";

try {

    $section = isset($_GET['section']) ? intval($_GET['section']) : 0;
    $quarter = isset($_GET['quarter']) ? intval($_GET['quarter']) : 0;

    if (!$section || !$quarter) {
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
        JOIN subject sub ON sub.subject_id = ge.subject_id
        WHERE ge.section_id = ?
        AND ge.quarter = ?
        ORDER BY sub.subject_name, sa.last_name
    ";

    $stmt = $connection->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);

    // ✅ CORRECT BINDING (2 integers only)
    $stmt->bind_param("ii", $section, $quarter);

    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

    $result = $stmt->get_result();
    $grades = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($grades);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
