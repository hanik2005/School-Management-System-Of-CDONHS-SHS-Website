<?php
header('Content-Type: application/json');
include "../../DB_Connection/Connection.php";

$grade_level = $_GET['grade_level'] ?? '';
$strand_id   = $_GET['strand_id'] ?? '';

if ($grade_level && $strand_id) {
    try {
        // Get all sections for this grade and strand, ordered alphabetically
        $stmt = $connection->prepare("
            SELECT section_id, section_name
            FROM section
            WHERE grade_level = ? AND strand_id = ?
            ORDER BY section_name
        ");

        $stmt->bind_param("ii", $grade_level, $strand_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $available_section = null;

        while ($row = $result->fetch_assoc()) {
            // Count how many students are in this section
            $sec_id = $row['section_id'];
            $countStmt = $connection->prepare("
                SELECT COUNT(*) as student_count
                FROM student_strand
                WHERE section_id = ?
            ");
            $countStmt->bind_param("i", $sec_id);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $countRow = $countResult->fetch_assoc();

            if ($countRow['student_count'] < 50) {
                $available_section = $row;
                break; // Stop at the first section with available slots
            }
        }

        if ($available_section) {
            echo json_encode([$available_section]);
        } else {
            echo json_encode([]); // No available section
        }

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
