<?php
// Get teacher's advisory
include "../../Back_End_Files/PHP_Files/get_teacher_advisory.php";

// Initialize variables
$progressData = [];
$successMessage = '';
$errorMessage = '';

// Function to get current school year
function getCurrentSchoolYear() {
    $currentMonth = date('n');
    $currentYear = date('Y');
    
    // School year typically starts in June (month 6)
    // If current month is June onwards, school year is currentYear-nextYear
    // If current month is before June, school year is previousYear-currentYear
    if ($currentMonth >= 6) {
        return $currentYear . '-' . ($currentYear + 1);
    } else {
        return ($currentYear - 1) . '-' . $currentYear;
    }
}

// Handle Finalize Status POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize_status'])) {
    $student_id = intval($_POST['student_id'] ?? 0);
    $new_status = trim($_POST['new_status'] ?? '');
    
    if ($student_id > 0 && in_array($new_status, ['Promoted', 'Retained', 'Graduated'])) {
        // Start transaction
        $connection->begin_transaction();
        
        try {
            // Get current student strand info
            $getCurrentStrand = $connection->prepare("
                SELECT ss.*, s.enrollment_status 
                FROM student_strand ss
                INNER JOIN students s ON ss.student_id = s.student_id
                WHERE ss.student_id = ?
            ");
            $getCurrentStrand->bind_param("i", $student_id);
            $getCurrentStrand->execute();
            $currentStrand = $getCurrentStrand->get_result()->fetch_assoc();
            
            if ($currentStrand) {
                // Archive current strand info
                $archiveStmt = $connection->prepare("
                    INSERT INTO archived_student_strand (student_id, strand_id, grade_level, section_id, reason)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $reason = $new_status === 'Promoted' ? 'PROMOTION' : ($new_status === 'Graduated' ? 'PROMOTION' : 'MANUAL');
                $archiveStmt->bind_param("iiiis", 
                    $student_id, 
                    $currentStrand['strand_id'], 
                    $currentStrand['grade_level'], 
                    $currentStrand['section_id'],
                    $reason
                );
                $archiveStmt->execute();
                
                // Get current school year for update (only for Promoted and Retained)
                $currentSchoolYear = getCurrentSchoolYear();
                
                if ($new_status === 'Promoted') {
                    // Get next grade level section (same strand, next grade level, same section name)
                    $currentGrade = $currentStrand['grade_level'];
                    $nextGrade = $currentGrade + 1;
                    
                    // Get current section name
                    $getSectionName = $connection->prepare("
                        SELECT section_name FROM section WHERE section_id = ?
                    ");
                    $getSectionName->bind_param("i", $currentStrand['section_id']);
                    $getSectionName->execute();
                    $sectionResult = $getSectionName->get_result()->fetch_assoc();
                    $currentSectionName = $sectionResult['section_name'] ?? 'A';
                    
                    // Check if current section in next grade is full (50 students)
                    $checkCapacity = $connection->prepare("
                        SELECT COUNT(*) as student_count 
                        FROM student_strand 
                        WHERE section_id = ? AND grade_level = ?
                    ");
                    $checkCapacity->bind_param("ii", $currentStrand['section_id'], $nextGrade);
                    $checkCapacity->execute();
                    $capacityResult = $checkCapacity->get_result()->fetch_assoc();
                    $currentSectionCount = $capacityResult['student_count'];
                    $checkCapacity->close();
                    
                    $nextSectionId = null;
                    $sectionMessage = "";
                    
                    // If current section is full (50 students), find another available section
                    if ($currentSectionCount >= 50) {
                        // Find available section with same strand and next grade level
                        $findSection = $connection->prepare("
                            SELECT s.section_id, s.section_name, 
                                   (50 - COUNT(ss.student_id)) as available_slots
                            FROM section s
                            LEFT JOIN student_strand ss ON s.section_id = ss.section_id 
                                AND ss.grade_level = s.grade_level
                            WHERE s.grade_level = ? AND s.strand_id = ?
                            GROUP BY s.section_id, s.section_name
                            HAVING available_slots > 0
                            ORDER BY available_slots DESC
                            LIMIT 1
                        ");
                        $findSection->bind_param("ii", $nextGrade, $currentStrand['strand_id']);
                        $findSection->execute();
                        $availableSection = $findSection->get_result()->fetch_assoc();
                        $findSection->close();
                        
                        if ($availableSection) {
                            $nextSectionId = $availableSection['section_id'];
                            $sectionMessage = " (moved to Section " . $availableSection['section_name'] . " because original section is full)";
                        }
                    }
                    
                    // If no available section found through auto-assignment, use the same section name
                    if ($nextSectionId === null) {
                        // Find next grade level section with same strand and section name
                        $getNextSection = $connection->prepare("
                            SELECT section_id FROM section 
                            WHERE strand_id = ? AND grade_level = ? AND section_name = ?
                            LIMIT 1
                        ");
                        $getNextSection->bind_param("iis", $currentStrand['strand_id'], $nextGrade, $currentSectionName);
                        $getNextSection->execute();
                        $nextSectionResult = $getNextSection->get_result()->fetch_assoc();
                        $nextSectionId = $nextSectionResult['section_id'] ?? null;
                    }
                    
                    if ($nextSectionId) {
                        // Update student_strand to next grade level
                        $updateStrand = $connection->prepare("
                            UPDATE student_strand 
                            SET grade_level = ?, section_id = ?
                            WHERE student_id = ?
                        ");
                        $updateStrand->bind_param("iii", $nextGrade, $nextSectionId, $student_id);
                        $updateStrand->execute();
                        
                        // Update enlistment status and school_year in students table
                        $updateEnlistment = $connection->prepare("
                            UPDATE students SET enlistment_status = 'Promoted', school_year = ?
                            WHERE student_id = ?
                        ");
                        $updateEnlistment->bind_param("si", $currentSchoolYear, $student_id);
                        $updateEnlistment->execute();
                        
                        // Update student_subjects status to 'Completed' for promoted students
                        $updateSubjects = $connection->prepare("
                            UPDATE student_subjects 
                            SET status = 'Completed'
                            WHERE student_id = ? AND status = 'Enrolled'
                        ");
                        $updateSubjects->bind_param("i", $student_id);
                        $updateSubjects->execute();
                        
                        $successMessage = "Student has been successfully promoted to Grade $nextGrade!$sectionMessage";
                    } else {
                        $errorMessage = "No corresponding section found for Grade $nextGrade. Please contact administrator.";
                    }
                    
                } elseif ($new_status === 'Graduated') {
                    // Update student enrollment status to Graduated
                    // Do NOT update school_year for graduated students
                    $updateGraduated = $connection->prepare("
                        UPDATE students SET enrollment_status = 'Graduated', enlistment_status = 'Finished'
                        WHERE student_id = ?
                    ");
                    $updateGraduated->bind_param("i", $student_id);
                    $updateGraduated->execute();
                    
                    // Update student_subjects status to 'Completed' for graduated students
                    $updateSubjects = $connection->prepare("
                        UPDATE student_subjects 
                        SET status = 'Completed'
                        WHERE student_id = ? AND status = 'Enrolled'
                    ");
                    $updateSubjects->bind_param("i", $student_id);
                    $updateSubjects->execute();
                    
                    $successMessage = "Student has been marked as Graduated!";
                    
                } elseif ($new_status === 'Retained') {
                    // Keep same grade level and section, update enlistment status and school_year
                    $updateEnlistment = $connection->prepare("
                        UPDATE students SET enlistment_status = 'Enlisted', school_year = ?
                        WHERE student_id = ?
                    ");
                    $updateEnlistment->bind_param("si", $currentSchoolYear, $student_id);
                    $updateEnlistment->execute();
                    
                    $successMessage = "Student has been marked as Retained.";
                }
            }
            
            $connection->commit();
            
        } catch (Exception $e) {
            $connection->rollback();
            $errorMessage = "Error updating student status: " . $e->getMessage();
        }
    }
}

// Handle Bulk Finalize POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_finalize'])) {
    $selectedStudents = $_POST['selected_students'] ?? [];
    
    if (!empty($selectedStudents)) {
        $connection->begin_transaction();
        
        try {
            $currentSchoolYear = getCurrentSchoolYear();
            $promotedCount = 0;
            $retainedCount = 0;
            $graduatedCount = 0;
            
            foreach ($selectedStudents as $student_id) {
                $student_id = intval($student_id);
                
                // Get student's calculated status
                $getStatus = $connection->prepare("
                    SELECT ss.grade_level, ss.strand_id, ss.section_id, s.enrollment_status,
                           (SELECT AVG(grade) FROM grade_entry WHERE student_id = ? AND grade_status = 'Approved') as overall_avg
                    FROM student_strand ss
                    INNER JOIN students s ON ss.student_id = s.student_id
                    WHERE ss.student_id = ?
                ");
                $getStatus->bind_param("ii", $student_id, $student_id);
                $getStatus->execute();
                $studentData = $getStatus->get_result()->fetch_assoc();
                
                if (!$studentData) continue;
                
                // Determine status
                $new_status = '';
                $grade_level = $studentData['grade_level'];
                $overall_avg = $studentData['overall_avg'];
                
                if ($grade_level == 12 && $overall_avg >= 75) {
                    $new_status = 'Graduated';
                } elseif ($overall_avg >= 75) {
                    $new_status = 'Promoted';
                } else {
                    $new_status = 'Retained';
                }
                
                // Archive current strand info
                $archiveStmt = $connection->prepare("
                    INSERT INTO archived_student_strand (student_id, strand_id, grade_level, section_id, reason)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $reason = $new_status === 'Retained' ? 'MANUAL' : 'PROMOTION';
                $archiveStmt->bind_param("iiiis", 
                    $student_id, 
                    $studentData['strand_id'], 
                    $studentData['grade_level'], 
                    $studentData['section_id'],
                    $reason
                );
                $archiveStmt->execute();
                
                if ($new_status === 'Promoted') {
                    $nextGrade = $grade_level + 1;
                    
                    // Get current section name
                    $getSectionName = $connection->prepare("
                        SELECT section_name FROM section WHERE section_id = ?
                    ");
                    $getSectionName->bind_param("i", $studentData['section_id']);
                    $getSectionName->execute();
                    $sectionResult = $getSectionName->get_result()->fetch_assoc();
                    $currentSectionName = $sectionResult['section_name'] ?? 'A';
                    
                    // Check if current section in next grade is full (50 students)
                    $checkCapacity = $connection->prepare("
                        SELECT COUNT(*) as student_count 
                        FROM student_strand 
                        WHERE section_id = ? AND grade_level = ?
                    ");
                    $checkCapacity->bind_param("ii", $studentData['section_id'], $nextGrade);
                    $checkCapacity->execute();
                    $capacityResult = $checkCapacity->get_result()->fetch_assoc();
                    $currentSectionCount = $capacityResult['student_count'];
                    $checkCapacity->close();
                    
                    $nextSectionId = null;
                    
                    // If current section is full (50 students), find another available section
                    if ($currentSectionCount >= 50) {
                        // Find available section with same strand and next grade level
                        $findSection = $connection->prepare("
                            SELECT s.section_id, s.section_name, 
                                   (50 - COUNT(ss.student_id)) as available_slots
                            FROM section s
                            LEFT JOIN student_strand ss ON s.section_id = ss.section_id 
                                AND ss.grade_level = s.grade_level
                            WHERE s.grade_level = ? AND s.strand_id = ?
                            GROUP BY s.section_id, s.section_name
                            HAVING available_slots > 0
                            ORDER BY available_slots DESC
                            LIMIT 1
                        ");
                        $findSection->bind_param("ii", $nextGrade, $studentData['strand_id']);
                        $findSection->execute();
                        $availableSection = $findSection->get_result()->fetch_assoc();
                        $findSection->close();
                        
                        if ($availableSection) {
                            $nextSectionId = $availableSection['section_id'];
                        }
                    }
                    
                    // If no available section found through auto-assignment, use the same section name
                    if ($nextSectionId === null) {
                        $getNextSection = $connection->prepare("
                            SELECT section_id FROM section 
                            WHERE strand_id = ? AND grade_level = ? AND section_name = ?
                            LIMIT 1
                        ");
                        $getNextSection->bind_param("iis", $studentData['strand_id'], $nextGrade, $currentSectionName);
                        $getNextSection->execute();
                        $nextSectionResult = $getNextSection->get_result()->fetch_assoc();
                        $nextSectionId = $nextSectionResult['section_id'] ?? null;
                    }
                    
                    if ($nextSectionId) {
                        $updateStrand = $connection->prepare("
                            UPDATE student_strand 
                            SET grade_level = ?, section_id = ?
                            WHERE student_id = ?
                        ");
                        $updateStrand->bind_param("iii", $nextGrade, $nextSectionId, $student_id);
                        $updateStrand->execute();
                        
                        $updateEnlistment = $connection->prepare("
                            UPDATE students SET enlistment_status = 'Promoted', school_year = ?
                            WHERE student_id = ?
                        ");
                        $updateEnlistment->bind_param("si", $currentSchoolYear, $student_id);
                        $updateEnlistment->execute();
                        
                        // Update student_subjects status to 'Completed' for promoted students
                        $updateSubjects = $connection->prepare("
                            UPDATE student_subjects 
                            SET status = 'Completed'
                            WHERE student_id = ? AND status = 'Enrolled'
                        ");
                        $updateSubjects->bind_param("i", $student_id);
                        $updateSubjects->execute();
                        
                        $promotedCount++;
                    }
                    
                } elseif ($new_status === 'Graduated') {
                    $updateGraduated = $connection->prepare("
                        UPDATE students SET enrollment_status = 'Graduated', enlistment_status = 'Promoted'
                        WHERE student_id = ?
                    ");
                    $updateGraduated->bind_param("i", $student_id);
                    $updateGraduated->execute();
                    
                    // Update student_subjects status to 'Completed' for graduated students
                    $updateSubjects = $connection->prepare("
                        UPDATE student_subjects 
                        SET status = 'Completed'
                        WHERE student_id = ? AND status = 'Enrolled'
                    ");
                    $updateSubjects->bind_param("i", $student_id);
                    $updateSubjects->execute();
                    
                    $graduatedCount++;
                    
                } elseif ($new_status === 'Retained') {
                    $updateEnlistment = $connection->prepare("
                        UPDATE students SET enlistment_status = 'Enlisted', school_year = ?
                        WHERE student_id = ?
                    ");
                    $updateEnlistment->bind_param("si", $currentSchoolYear, $student_id);
                    $updateEnlistment->execute();
                    $retainedCount++;
                }
            }
            
            $connection->commit();
            $successMessage = "Successfully finalized: $promotedCount promoted, $retainedCount retained, $graduatedCount graduated.";
            
        } catch (Exception $e) {
            $connection->rollback();
            $errorMessage = "Error updating student statuses: " . $e->getMessage();
        }
    }
}

// Fetch student progress data
if (!empty($advisorySectionId)) {
    // Get all students in the advisory section with their grades
    // Using simple query with proper joins
    $progressQuery = "
        SELECT 
            s.student_id,
            sa.lrn,
            sa.first_name,
            sa.last_name,
            sa.middle_name,
            sa.extension_name,
            ss.grade_level,
            st.strand_name,
            sec.section_name,
            s.enrollment_status,
            s.enlistment_status,
            s.school_year
        FROM students s
        INNER JOIN student_strand ss ON s.student_id = ss.student_id AND ss.section_id = ?
        INNER JOIN student_applications sa ON s.application_id = sa.application_id
        INNER JOIN strands st ON ss.strand_id = st.strand_id
        INNER JOIN section sec ON ss.section_id = sec.section_id
        WHERE s.enrollment_status = 'Active'
        ORDER BY sa.last_name, sa.first_name
    ";
    
    $progressStmt = $connection->prepare($progressQuery);
    $progressStmt->bind_param("i", $advisorySectionId);
    $progressStmt->execute();
    $students = $progressStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Remove duplicates by student_id using associative array
    $uniqueStudents = [];
    foreach ($students as $student) {
        $uniqueStudents[$student['student_id']] = $student;
    }
    $students = array_values($uniqueStudents);
    
    // Calculate quarterly averages for each student
    // Only include students with complete grades (all 4 quarters)
    $processedStudents = [];
    foreach ($students as $student) {
        $studentId = $student['student_id'];
        
        // Calculate overall average
        $overallAvgQuery = "
            SELECT AVG(grade) as overall_avg, COUNT(DISTINCT quarter) as quarter_count
            FROM grade_entry
            WHERE student_id = ? AND grade_status = 'Approved'
        ";
        $overallStmt = $connection->prepare($overallAvgQuery);
        $overallStmt->bind_param("i", $studentId);
        $overallStmt->execute();
        $overallResult = $overallStmt->get_result()->fetch_assoc();
        
        $student['overall_avg'] = $overallResult['overall_avg'] ? round($overallResult['overall_avg'], 2) : null;
        $student['quarter_count'] = $overallResult['quarter_count'];
        
        // Only include students with complete grades (all 4 quarters)
        if ($student['quarter_count'] != 4) {
            continue; // Skip students without complete grades
        }
        
        // Determine status based on grades
        $student['calculated_status'] = 'Pending';
        
        if ($student['quarter_count'] == 4) {
            // All quarters have grades
            if ($student['grade_level'] == 12 && $student['overall_avg'] >= 75) {
                $student['calculated_status'] = 'Graduated';
            } elseif ($student['overall_avg'] >= 75) {
                $student['calculated_status'] = 'Promoted';
            } else {
                $student['calculated_status'] = 'Retained';
            }
        }
        
        // Check if already finalized (has been promoted/graduated)
        $checkArchive = $connection->prepare("
            SELECT COUNT(*) as archived FROM archived_student_strand WHERE student_id = ?
        ");
        $checkArchive->bind_param("i", $studentId);
        $checkArchive->execute();
        $archiveResult = $checkArchive->get_result()->fetch_assoc();
        $student['is_finalized'] = $archiveResult['archived'] > 0;
        
        $processedStudents[] = $student;
    }
    
    $progressData = $processedStudents;
}
?>
