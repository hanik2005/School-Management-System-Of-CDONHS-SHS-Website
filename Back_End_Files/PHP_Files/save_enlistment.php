<?php
session_start();

// Disable HTML errors, log them instead
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

header('Content-Type: application/json');

if (!isset($_SESSION['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include "../../DB_Connection/Connection.php";

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $grade_level = isset($input['grade_level']) ? (int)$input['grade_level'] : null;
    $strand_id   = isset($input['strand_id']) ? (int)$input['strand_id'] : null;
    $section_id  = isset($input['section_id']) ? (int)$input['section_id'] : null;
    $subjects    = isset($input['subjects']) && is_array($input['subjects']) ? $input['subjects'] : [];

    if (!$grade_level || !$strand_id || !$section_id || empty($subjects)) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data']);
        exit;
    }

    $school_id = $_SESSION['school_id'];

    // Enlistment status and school year
    $enlistment_status = 'Pending';
    $school_year = date('Y') . '-' . (date('Y') + 1);

    // Start transaction
    $connection->begin_transaction();

    // 1️⃣ Find or create student
    $stmtStudent = $connection->prepare("
        SELECT student_id FROM students WHERE school_id = ?
    ");
    $stmtStudent->bind_param("i", $school_id);
    $stmtStudent->execute();
    $resStudent = $stmtStudent->get_result();
    $studentRow = $resStudent->fetch_assoc();
    $stmtStudent->close();

    if ($studentRow) {
        $student_id = $studentRow['student_id'];
    } else {
        // Insert new student
        $stmtInsertStudent = $connection->prepare("
            INSERT INTO students (school_id, enlistment_status) VALUES (?, ?)
        ");
        $stmtInsertStudent->bind_param("is", $school_id, $enlistment_status);
        $stmtInsertStudent->execute();
        $student_id = $stmtInsertStudent->insert_id;
        $stmtInsertStudent->close();
    }

    // 2️⃣ Check current enrollment
    $stmtCheck = $connection->prepare("
        SELECT strand_id, grade_level, section_id
        FROM student_strand
        WHERE student_id = ?
        ORDER BY grade_level DESC
        LIMIT 1
    ");
    $stmtCheck->bind_param("i", $student_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $current = $resCheck->fetch_assoc();
    $stmtCheck->close();

    if ($current) {
        // Promotion only (same strand, higher grade)
        if ($current['grade_level'] < $grade_level && $current['strand_id'] == $strand_id) {
            $stmtUpdate = $connection->prepare("
                UPDATE student_strand
                SET grade_level = ?, section_id = ?
                WHERE student_id = ? AND strand_id = ?
            ");
            $stmtUpdate->bind_param("iiii", $grade_level, $section_id, $student_id, $strand_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
        // Strand change
        elseif ($current['strand_id'] != $strand_id) {
            // Archive old strand
            $stmtArchive = $connection->prepare("
                INSERT INTO archived_student_strand
                (student_id, strand_id, grade_level, section_id, reason)
                VALUES (?, ?, ?, ?, 'MANUAL')
            ");
            $stmtArchive->bind_param("iiii", $student_id, $current['strand_id'], $current['grade_level'], $current['section_id']);
            $stmtArchive->execute();
            $stmtArchive->close();

            // Delete old strand
            $stmtDelete = $connection->prepare("
                DELETE FROM student_strand
                WHERE student_id = ? AND strand_id = ?
            ");
            $stmtDelete->bind_param("ii", $student_id, $current['strand_id']);
            $stmtDelete->execute();
            $stmtDelete->close();

            // Insert new strand
            $stmtInsert = $connection->prepare("
                INSERT INTO student_strand (student_id, strand_id, grade_level, section_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmtInsert->bind_param("iiii", $student_id, $strand_id, $grade_level, $section_id);
            $stmtInsert->execute();
            $stmtInsert->close();
        }
        // Already enrolled in same grade & strand
        else {
            // Just update section
            $stmtUpdate = $connection->prepare("
                UPDATE student_strand
                SET section_id = ?
                WHERE student_id = ? AND strand_id = ?
            ");
            $stmtUpdate->bind_param("iii", $section_id, $student_id, $strand_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    } else {
        // First enrollment
        $stmtInsert = $connection->prepare("
            INSERT INTO student_strand (student_id, strand_id, grade_level, section_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmtInsert->bind_param("iiii", $student_id, $strand_id, $grade_level, $section_id);
        $stmtInsert->execute();
        $stmtInsert->close();
    }

    // 3️⃣ Insert subjects
    $stmtSubj = $connection->prepare("
        INSERT INTO student_subjects (student_id, subject_id, school_year)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE school_year = VALUES(school_year)
    ");
    foreach ($subjects as $subject_id) {
        $stmtSubj->bind_param("iis", $student_id, $subject_id, $school_year);
        $stmtSubj->execute();
    }
    $stmtSubj->close();

    // 4️⃣ Update enlistment_status
    $stmtStatus = $connection->prepare("
        UPDATE students
        SET enlistment_status = ?
        WHERE student_id = ?
    ");
    $stmtStatus->bind_param("si", $enlistment_status, $student_id);
    $stmtStatus->execute();
    $stmtStatus->close();

    // Commit transaction
    $connection->commit();

    echo json_encode([
        'success' => true,
        'status' => $enlistment_status
    ]);

} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
