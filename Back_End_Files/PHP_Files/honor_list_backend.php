<?php
// Set profile image path
$profileImagePath = !empty($user['profile_image']) 
    ? "../../uploads/Profile/teacher/" . htmlspecialchars($user['profile_image']) 
    : "../../Assets/profile_button.png";

// Get teacher's advisory
include "../../Back_End_Files/PHP_Files/get_teacher_advisory.php";

// Get filter values
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_student = isset($_GET['student_id']) ? intval($_GET['student_id']) : '';
$filter_quarter = isset($_GET['quarter']) ? trim($_GET['quarter']) : 'all';

// Get all students in teacher's advisory for dropdown
$studentOptions = [];
if (!empty($advisorySectionId)) {
    $studentQuery = "
        SELECT s.student_id, sa.first_name, sa.last_name, sa.middle_name
        FROM students s
        INNER JOIN student_strand ss ON s.student_id = ss.student_id
        INNER JOIN student_applications sa ON s.application_id = sa.application_id
        WHERE ss.section_id = ? AND s.enrollment_status = 'Active'
        ORDER BY sa.last_name, sa.first_name
    ";
    $studentStmt = $connection->prepare($studentQuery);
    $studentStmt->bind_param("i", $advisorySectionId);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();
    while ($row = $studentResult->fetch_assoc()) {
        $studentOptions[$row['student_id']] = $row['last_name'] . ', ' . $row['first_name'] . ' ' . ($row['middle_name'] ?? '');
    }
}

// Build honor list query
$honorList = [];
if (!empty($advisorySectionId)) {
    // Base query for calculating averages
    if ($filter_quarter === 'all' || $filter_quarter === '') {
        // Overall average (all quarters)
        // Only include students who have complete grades for all 4 quarters
        $avgQuery = "
            SELECT 
                s.student_id,
                sa.first_name,
                sa.last_name,
                sa.middle_name,
                ss.grade_level,
                st.strand_name,
                sec.section_name,
                AVG(ge.grade) as average_grade,
                COUNT(DISTINCT ge.quarter) as quarters_count,
                (SELECT COUNT(*) FROM student_subjects ss2 WHERE ss2.student_id = s.student_id AND ss2.status = 'Enrolled') as total_subjects,
                COUNT(DISTINCT ge.entry_id) as total_grades
            FROM students s
            INNER JOIN student_strand ss ON s.student_id = ss.student_id
            INNER JOIN student_applications sa ON s.application_id = sa.application_id
            INNER JOIN strands st ON ss.strand_id = st.strand_id
            INNER JOIN section sec ON ss.section_id = sec.section_id
            INNER JOIN grade_entry ge ON s.student_id = ge.student_id AND ge.grade_status = 'Approved'
            WHERE ss.section_id = ? AND s.enrollment_status = 'Active'
        ";
        
        $params = [$advisorySectionId];
        $types = "i";
        
        if (!empty($filter_student)) {
            $avgQuery .= " AND s.student_id = ?";
            $params[] = $filter_student;
            $types .= "i";
        }
        
        if (!empty($search_name)) {
            $avgQuery .= " AND (sa.first_name LIKE ? OR sa.last_name LIKE ?)";
            $searchParam = "%$search_name%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        // Must have grades for all 4 quarters AND all enrolled subjects for all 4 quarters
        // total_subjects * 4 = total expected grades
        $avgQuery .= " GROUP BY s.student_id HAVING AVG(ge.grade) >= 90 AND COUNT(DISTINCT ge.quarter) = 4 ORDER BY average_grade DESC";
        
    } else {
        // Specific quarter
        $quarterNum = (int)$filter_quarter;
        $avgQuery = "
            SELECT 
                s.student_id,
                sa.first_name,
                sa.last_name,
                sa.middle_name,
                ss.grade_level,
                st.strand_name,
                sec.section_name,
                AVG(ge.grade) as average_grade,
                COUNT(ge.entry_id) as subjects_count
            FROM students s
            INNER JOIN student_strand ss ON s.student_id = ss.student_id
            INNER JOIN student_applications sa ON s.application_id = sa.application_id
            INNER JOIN strands st ON ss.strand_id = st.strand_id
            INNER JOIN section sec ON ss.section_id = sec.section_id
            INNER JOIN grade_entry ge ON s.student_id = ge.student_id AND ge.grade_status = 'Approved'
            WHERE ss.section_id = ? AND s.enrollment_status = 'Active' AND ge.quarter = ?
        ";
        
        $params = [$advisorySectionId, $quarterNum];
        $types = "ii";
        
        if (!empty($filter_student)) {
            $avgQuery .= " AND s.student_id = ?";
            $params[] = $filter_student;
            $types .= "i";
        }
        
        if (!empty($search_name)) {
            $avgQuery .= " AND (sa.first_name LIKE ? OR sa.last_name LIKE ?)";
            $searchParam = "%$search_name%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        $avgQuery .= " GROUP BY s.student_id HAVING AVG(ge.grade) >= 90 ORDER BY average_grade DESC";
    }
    
    $avgStmt = $connection->prepare($avgQuery);
    $avgStmt->bind_param($types, ...$params);
    $avgStmt->execute();
    $honorList = $avgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Determine quarter label
$quarterLabel = '';
switch ($filter_quarter) {
    case '1': $quarterLabel = '1st Quarter'; break;
    case '2': $quarterLabel = '2nd Quarter'; break;
    case '3': $quarterLabel = '3rd Quarter'; break;
    case '4': $quarterLabel = '4th Quarter'; break;
    default: $quarterLabel = 'Overall (1st - 4th Quarter)'; break;
}
?>