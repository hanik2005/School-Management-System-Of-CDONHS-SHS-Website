<?php
header('Content-Type: application/json');
include "../../DB_Connection/Connection.php";

$grade_level = $_GET['grade_level'] ?? '';
$strand_id   = $_GET['strand_id'] ?? '';

if ($grade_level && $strand_id) {
    try {
        // Get subjects for the grade and strand
        $stmt = $connection->prepare("
            SELECT s.subject_id, s.subject_name
            FROM subject s
            WHERE s.grade_level = ? AND s.strand_id = ?
            ORDER BY s.subject_name
        ");
        $stmt->bind_param("ii", $grade_level, $strand_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }

        echo json_encode($subjects);

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
