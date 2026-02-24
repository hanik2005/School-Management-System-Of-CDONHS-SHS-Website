<?php
// Disable all error display - only log errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

ob_start(); // start output buffering
header('Content-Type: application/json');

include "../../DB_Connection/Connection.php";
include "mailer_details.php";

// read JSON from frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['updates'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$updatedCount = 0;
$rejectedSections = []; // Track rejected sections for email notifications
$approvedSections = []; // Track approved sections for email notifications
$debugInfo = []; // For debugging

foreach ($data['updates'] as $item) {

    // validate item
    if (!isset($item['status'], $item['section'], $item['quarter'])) {
        $debugInfo[] = "Skipped invalid item: " . json_encode($item);
        continue; // skip invalid items
    }

    $status = $item['status'];
    $section_id = (int)$item['section'];
    $quarter = (int)$item['quarter'];
    $remarks = isset($item['remarks']) ? $item['remarks'] : "";

    // prepare query - exclude archived (promoted) students
    $stmt = $connection->prepare("
        UPDATE grade_entry ge
        JOIN section sec ON sec.section_id = ge.section_id
        SET ge.grade_status = ?
        WHERE sec.section_id = ?
        AND ge.quarter = ?
        AND NOT EXISTS (
            SELECT 1 FROM archived_student_strand ass
            WHERE ass.student_id = ge.student_id
            AND ass.section_id = ge.section_id
        )
    ");

    if (!$stmt) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Prepare failed: '.$connection->error]);
        exit;
    }

    $stmt->bind_param("sii", $status, $section_id, $quarter);
    $stmt->execute();

    $affectedRows = $stmt->affected_rows;
    $updatedCount += $affectedRows;
    $debugInfo[] = "Section $section_id, Quarter $quarter, Status $status: $affectedRows rows updated";
    $stmt->close();

    // Track rejected sections for email notification
    if ($status === 'Rejected') {
        $rejectedSections[] = [
            'section_id' => $section_id,
            'quarter' => $quarter,
            'remarks' => $remarks
        ];
    }
    
    // Track approved sections for email notification
    if ($status === 'Approved') {
        $approvedSections[] = [
            'section_id' => $section_id,
            'quarter' => $quarter
        ];
    }
}

// Send email notifications to advisors of rejected sections
try {
foreach ($rejectedSections as $rejected) {
    $section_id = $rejected['section_id'];
    $quarter = $rejected['quarter'];
    $remarks = $rejected['remarks'];

    // Get advisor info for this section
    $stmtAdvisor = $connection->prepare("
        SELECT ta.email, ta.first_name, ta.last_name, s.section_name, s.grade_level, st.strand_name
        FROM teacher_advisory tad
        JOIN teachers t ON tad.teacher_id = t.teacher_id
        JOIN teacher_applications ta ON t.application_id = ta.teacher_application_id
        JOIN section s ON tad.section_id = s.section_id
        JOIN strands st ON tad.strand_id = st.strand_id
        WHERE tad.section_id = ?
    ");
    if (!$stmtAdvisor) {
        error_log('Failed to prepare advisor query: ' . $connection->error);
        continue;
    }
    $stmtAdvisor->bind_param("i", $section_id);
    $stmtAdvisor->execute();
    $resultAdvisor = $stmtAdvisor->get_result();
    $advisorInfo = $resultAdvisor->fetch_assoc();
    $stmtAdvisor->close();

    // Send email if advisor found
    if ($advisorInfo) {
        try {
            $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Admin');
            $mail->addAddress($advisorInfo['email']);
            $mail->isHTML(true);
            $mail->Subject = "Grades Rejected - Section {$advisorInfo['section_name']}";

            // Build remarks section HTML
            $remarksHtml = "";
            if (!empty($remarks)) {
                $remarksHtml = "
                    <div class='info-box' style='background-color: #fff3cd; border-left: 4px solid #dc3545;'>
                        <p><strong>Reason for Rejection:</strong></p>
                        <p style='margin: 0; font-style: italic;'>{$remarks}</p>
                    </div>
                ";
            }

            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                    .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                    .header { font-size: 18px; font-weight: bold; color: #dc3545; margin-bottom: 15px; }
                    .status { font-weight: bold; color: #dc3545; }
                    .info-box { background-color: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; }
                    .footer { margin-top: 30px; font-size: 14px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>Good day {$advisorInfo['first_name']} {$advisorInfo['last_name']},</div>
                    
                    <p>We regret to inform you that the grades you submitted have been <span class='status'>REJECTED</span>.</p>
                    
                    <div class='info-box'>
                        <p><strong>Section Details:</strong></p>
                        <ul>
                            <li>Grade Level: {$advisorInfo['grade_level']}</li>
                            <li>Strand: {$advisorInfo['strand_name']}</li>
                            <li>Section: {$advisorInfo['section_name']}</li>
                            <li>Quarter: {$quarter}</li>
                        </ul>
                    </div>
                    
                    {$remarksHtml}
                    
                    <p>Please review the grades and make the necessary corrections. You may resubmit the grades after making the required changes.</p>
                    
                    <p>If you have any questions, please contact the school administration.</p>
                    
                    <p>Thank you,<br>
                    <b>CDONHS-SHS Admin</b></p>
                    
                    <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error (Rejected): " . $mail->ErrorInfo);
        }
        
        // Clear addresses for next iteration
        $mail->clearAddresses();
    }
}
} catch (Exception $e) {
    error_log("Rejected sections email loop error: " . $e->getMessage());
}

// Send email notifications for approved sections
try {
foreach ($approvedSections as $approved) {
    $section_id = $approved['section_id'];
    $quarter = $approved['quarter'];

    // Get advisor info for this section
    $stmtAdvisor = $connection->prepare("
        SELECT ta.email, ta.first_name, ta.last_name, s.section_name, s.grade_level, st.strand_name
        FROM teacher_advisory tad
        JOIN teachers t ON tad.teacher_id = t.teacher_id
        JOIN teacher_applications ta ON t.application_id = ta.teacher_application_id
        JOIN section s ON tad.section_id = s.section_id
        JOIN strands st ON tad.strand_id = st.strand_id
        WHERE tad.section_id = ?
    ");
    if (!$stmtAdvisor) {
        error_log('Failed to prepare advisor query for approved: ' . $connection->error);
        continue;
    }
    $stmtAdvisor->bind_param("i", $section_id);
    $stmtAdvisor->execute();
    $resultAdvisor = $stmtAdvisor->get_result();
    $advisorInfo = $resultAdvisor->fetch_assoc();
    $stmtAdvisor->close();

    // Send email to advisor if found
    if ($advisorInfo) {
        try {
            $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Admin');
            $mail->addAddress($advisorInfo['email']);
            $mail->isHTML(true);
            $mail->Subject = "Grades Approved - Section {$advisorInfo['section_name']}";

            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                    .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                    .header { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
                    .status { font-weight: bold; color: #28a745; }
                    .info-box { background-color: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; }
                    .footer { margin-top: 30px; font-size: 14px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>Good day {$advisorInfo['first_name']} {$advisorInfo['last_name']},</div>
                    
                    <p>We are pleased to inform you that the grades you submitted have been <span class='status'>APPROVED</span>.</p>
                    
                    <div class='info-box'>
                        <p><strong>Section Details:</strong></p>
                        <ul>
                            <li>Grade Level: {$advisorInfo['grade_level']}</li>
                            <li>Strand: {$advisorInfo['strand_name']}</li>
                            <li>Section: {$advisorInfo['section_name']}</li>
                            <li>Quarter: {$quarter}</li>
                        </ul>
                    </div>
                    
                    <p>The grades are now available for students to view. Thank you for your timely submission.</p>
                    
                    <p>Best regards,<br>
                    <b>CDONHS-SHS Admin</b></p>
                    
                    <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error (Advisor Approval): " . $mail->ErrorInfo);
        }
        
        // Clear addresses for next iteration
        $mail->clearAddresses();
    }

    // Get all students in this section with their emails
    $stmtStudents = $connection->prepare("
        SELECT sa.email, sa.first_name, sa.last_name
        FROM grade_entry ge
        JOIN students s ON ge.student_id = s.student_id
        JOIN student_applications sa ON s.application_id = sa.application_id
        WHERE ge.section_id = ?
        AND ge.quarter = ?
        AND NOT EXISTS (
            SELECT 1 FROM archived_student_strand ass
            WHERE ass.student_id = ge.student_id
            AND ass.section_id = ge.section_id
        )
        GROUP BY s.student_id
    ");
    if (!$stmtStudents) {
        error_log('Failed to prepare students query: ' . $connection->error);
        continue;
    }
    $stmtStudents->bind_param("ii", $section_id, $quarter);
    $stmtStudents->execute();
    $resultStudents = $stmtStudents->get_result();
    
    // Get section info for student emails
    $stmtSectionInfo = $connection->prepare("
        SELECT s.section_name, s.grade_level, st.strand_name
        FROM section s
        JOIN strands st ON s.strand_id = st.strand_id
        WHERE s.section_id = ?
    ");
    if ($stmtSectionInfo) {
        $stmtSectionInfo->bind_param("i", $section_id);
        $stmtSectionInfo->execute();
        $sectionInfo = $stmtSectionInfo->get_result()->fetch_assoc();
        $stmtSectionInfo->close();
    } else {
        $sectionInfo = ['section_name' => 'N/A', 'grade_level' => 'N/A', 'strand_name' => 'N/A'];
    }
    
    // Send email to each student
    while ($student = $resultStudents->fetch_assoc()) {
        try {
            $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Admin');
            $mail->addAddress($student['email']);
            $mail->isHTML(true);
            $mail->Subject = "Grades Available - Quarter {$quarter}";

            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                    .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                    .header { font-size: 18px; font-weight: bold; color: #1e3a8a; margin-bottom: 15px; }
                    .status { font-weight: bold; color: #28a745; }
                    .info-box { background-color: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; }
                    .footer { margin-top: 30px; font-size: 14px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>Good day {$student['first_name']} {$student['last_name']},</div>
                    
                    <p>We are pleased to inform you that your grades for <span class='status'>Quarter {$quarter}</span> are now available for viewing.</p>
                    
                    <div class='info-box'>
                        <p><strong>Details:</strong></p>
                        <ul>
                            <li>Grade Level: {$sectionInfo['grade_level']}</li>
                            <li>Strand: {$sectionInfo['strand_name']}</li>
                            <li>Section: {$sectionInfo['section_name']}</li>
                            <li>Quarter: {$quarter}</li>
                        </ul>
                    </div>
                    
                    <p>You may now log in to your account to view your grades.</p>
                    
                    <p>Best regards,<br>
                    <b>CDONHS-SHS Admin</b></p>
                    
                    <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error (Student Approval): " . $mail->ErrorInfo);
        }
        
        // Clear addresses for next iteration
        $mail->clearAddresses();
    }
    $stmtStudents->close();
}
} catch (Exception $e) {
    error_log("Approved sections email loop error: " . $e->getMessage());
}

ob_end_clean(); // clear any stray output

echo json_encode([
    'success' => true,
    'message' => "Grades updated successfully. Total rows updated: $updatedCount",
    'debug' => $debugInfo
]);
exit;
