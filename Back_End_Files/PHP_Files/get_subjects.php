<?php
session_start();
header('Content-Type: application/json');
include "../../DB_Connection/Connection.php";

// Get student_id from session (adjust if you store it differently)
$student_id = $_SESSION['user_id'] ?? null;

$grade_level = $_GET['grade_level'] ?? '';
$strand_id   = $_GET['strand_id'] ?? '';

if (!$student_id || !$grade_level || !$strand_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing student ID, grade level, or strand',
        'debug' => [
            'student_id' => $student_id,
            'grade_level' => $grade_level,
            'strand_id' => $strand_id
        ],
        'subjects' => []
    ]);
    exit;
}

try {
    // Get all subjects for the grade and strand
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

      $stmt_sy = $connection->prepare("
        SELECT MAX(school_year) AS current_sy
        FROM student_subjects
        WHERE student_id = ?
    ");
    $stmt_sy->bind_param("i", $student_id);
    $stmt_sy->execute();
    $result_sy = $stmt_sy->get_result();
    $row_sy = $result_sy->fetch_assoc();

    // If no record exists, fall back to the calendar-based function
    $current_sy = $row_sy['current_sy'] ?? getCurrentSchoolYear();

    // Get subjects the student is already enrolled in
    $stmt2 = $connection->prepare("
        SELECT subject_id 
        FROM student_subjects 
        WHERE student_id = ? 
        AND school_year = ?
    ");
    $stmt2->bind_param("is", $student_id, $current_sy);
    $stmt2->execute();
    $enrolled_result = $stmt2->get_result();

    $enrolled_subjects = [];
    while ($row = $enrolled_result->fetch_assoc()) {
        $enrolled_subjects[] = $row['subject_id'];
    }

    // Mark which subjects are already enrolled
    foreach ($subjects as &$subj) {
        $subj['enrolled'] = in_array($subj['subject_id'], $enrolled_subjects);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Subjects loaded',
        'debug' => [
            'student_id' => $student_id,
            'grade_level' => $grade_level,
            'strand_id' => $strand_id,
            'enrolled_subjects' => $enrolled_subjects
        ],
        'subjects' => $subjects
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'student_id' => $student_id,
            'grade_level' => $grade_level,
            'strand_id' => $strand_id
        ],
        'subjects' => []
    ]);
}



function getCurrentSchoolYear() {
    $month = date('n'); // Numeric month 1-12
    $year = date('Y');  // Current year

    if ($month >= 6) { // Assuming school year starts in June
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}
