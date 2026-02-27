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

        $available_sections = [];
        $full_sections = [];

        while ($row = $result->fetch_assoc()) {
            // Count how many students are in this section for this grade level
            $sec_id = $row['section_id'];
            $countStmt = $connection->prepare("
                SELECT COUNT(*) as student_count
                FROM student_strand
                WHERE section_id = ? AND grade_level = ?
            ");
            $countStmt->bind_param("ii", $sec_id, $grade_level);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $countRow = $countResult->fetch_assoc();

            // Separate available and full sections
            if ($countRow['student_count'] < 50) {
                $available_sections[] = $row;
            } else {
                $full_sections[] = $row;
            }
            $countStmt->close();
        }

        // If there are available sections, show only the first one (auto-assignment)
        if (!empty($available_sections)) {
            echo json_encode([$available_sections[0]]);
        } 
        // If ALL sections are full (A, B, C, D all have 50 students), show all for random selection
        elseif (!empty($full_sections)) {
            echo json_encode($full_sections, JSON_PRETTY_PRINT);
        } 
        else {
            echo json_encode([]); // No sections available
        }

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
