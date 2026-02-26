<?php
session_start();
header('Content-Type: application/json');
include "../../DB_Connection/Connection.php";

// Get user_id from session
$user_id = $_SESSION['user_id'] ?? null;

$grade_level = $_GET['grade_level'] ?? '';
$strand_id   = $_GET['strand_id'] ?? '';

if (!$user_id || !$grade_level || !$strand_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing user ID, grade level, or strand',
        'debug' => [
            'user_id' => $user_id,
            'grade_level' => $grade_level,
            'strand_id' => $strand_id
        ],
        'subjects' => []
    ]);
    exit;
}

try {
    // 1️⃣ Get student_id from students table
    $stmtStudent = $connection->prepare("
        SELECT student_id 
        FROM students 
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtStudent->bind_param("i", $user_id);
    $stmtStudent->execute();
    $resultStudent = $stmtStudent->get_result();
    $studentRow = $resultStudent->fetch_assoc();

    if (!$studentRow) {
        throw new Exception("Student not found for this user.");
    }

    $student_id = $studentRow['student_id'];

    // 2️⃣ Get all subjects for the grade and strand
    $stmt = $connection->prepare("
        SELECT subject_id, subject_name
        FROM subject
        WHERE grade_level = ? AND strand_id = ?
        ORDER BY subject_name
    ");
    $stmt->bind_param("ii", $grade_level, $strand_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    // 3️⃣ Get current school year
    $stmtSY = $connection->prepare("
        SELECT MAX(school_year) AS current_sy
        FROM student_subjects
        WHERE student_id = ?
    ");
    $stmtSY->bind_param("i", $student_id);
    $stmtSY->execute();
    $resultSY = $stmtSY->get_result();
    $rowSY = $resultSY->fetch_assoc();

    $current_sy = $rowSY['current_sy'] ?? getCurrentSchoolYear();

    // 4️⃣ Get subjects student is already enrolled in for the current SY
    // Only auto-check if status is Enrolled, Withdrawn with Grades, or Withdrawn
    // Exclude Pending and Dropped statuses
    $stmtEnrolled = $connection->prepare("
        SELECT subject_id 
        FROM student_subjects 
        WHERE student_id = ? AND school_year = ?
        AND status IN ('Enrolled', 'Withdrawn with Grades', 'Withdrawn')
    ");
    $stmtEnrolled->bind_param("is", $student_id, $current_sy);
    $stmtEnrolled->execute();
    $enrolledResult = $stmtEnrolled->get_result();

    $enrolled_subjects = [];
    while ($row = $enrolledResult->fetch_assoc()) {
        $enrolled_subjects[] = $row['subject_id'];
    }

    // 5️⃣ Auto-check enrolled subjects
    foreach ($subjects as &$subj) {
        $subj['enrolled'] = in_array($subj['subject_id'], $enrolled_subjects);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Subjects loaded successfully',
        'debug' => [
            'student_id' => $student_id,
            'grade_level' => $grade_level,
            'strand_id' => $strand_id,
            'current_sy' => $current_sy,
            'enrolled_subjects' => $enrolled_subjects
        ],
        'subjects' => $subjects
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'user_id' => $user_id,
            'grade_level' => $grade_level,
            'strand_id' => $strand_id
        ],
        'subjects' => []
    ]);
}

// Helper function to get current school year
function getCurrentSchoolYear() {
    $month = date('n'); // Numeric month 1-12
    $year = date('Y');

    if ($month >= 6) { // Assuming school year starts in June
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}
