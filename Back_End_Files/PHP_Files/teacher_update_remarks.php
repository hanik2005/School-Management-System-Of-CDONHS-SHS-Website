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

$updates = $data['updates'];

$updatedCount = 0;
$errorCount = 0;

foreach ($updates as $item) {
    if (!isset($item['application_id'], $item['status'])) {
        $errorCount++;
        continue;
    }
    
    $applicationId = (int)$item['application_id'];
    $status = $item['status'];
    $remarks = isset($item['remarks']) ? $item['remarks'] : "";
    
    // Get teacher application info first
    $stmtApp = $connection->prepare("SELECT * FROM teacher_applications WHERE teacher_application_id = ?");
    $stmtApp->bind_param("i", $applicationId);
    $stmtApp->execute();
    $resultApp = $stmtApp->get_result();
    $teacherApp = $resultApp->fetch_assoc();
    $stmtApp->close();
    
    if (!$teacherApp) {
        $errorCount++;
        continue;
    }
    
    $advisory = isset($item['advisory']) ? $item['advisory'] : null;
    
    // Handle Approved status
    if ($status === 'Approved') {
        // Update application status
        $stmt = $connection->prepare("
            UPDATE teacher_applications 
            SET application_status = ?, remarks = ?
            WHERE teacher_application_id = ?
        ");
        $stmt->bind_param("ssi", $status, $remarks, $applicationId);
        $stmt->execute();
        $stmt->close();
        
        // Check if teacher already exists
        $stmtCheck = $connection->prepare("SELECT teacher_id FROM teachers WHERE application_id = ?");
        $stmtCheck->bind_param("i", $applicationId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $existingTeacher = $resultCheck->fetch_assoc();
        $stmtCheck->close();
        
        if ($existingTeacher) {
            // Teacher already exists - just update advisory if provided
            $teacherId = $existingTeacher['teacher_id'];
            
            if ($advisory) {
                $advisoryParts = explode('|', $advisory);
                if (count($advisoryParts) === 3) {
                    $strandId = (int)$advisoryParts[0];
                    $gradeLevel = (int)$advisoryParts[1];
                    $sectionId = (int)$advisoryParts[2];
                    
                    // Check if advisory already exists
                    $stmtAdvCheck = $connection->prepare("
                        SELECT advisory_id FROM teacher_advisory 
                        WHERE teacher_id = ? AND strand_id = ? AND grade_level = ? AND section_id = ?
                    ");
                    $stmtAdvCheck->bind_param("iiii", $teacherId, $strandId, $gradeLevel, $sectionId);
                    $stmtAdvCheck->execute();
                    $resultAdvCheck = $stmtAdvCheck->get_result();
                    
                    if ($resultAdvCheck->num_rows === 0) {
                        // Insert new advisory
                        $stmtAdvisory = $connection->prepare("
                            INSERT INTO teacher_advisory (teacher_id, strand_id, grade_level, section_id) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmtAdvisory->bind_param("iiii", $teacherId, $strandId, $gradeLevel, $sectionId);
                        $stmtAdvisory->execute();
                        $stmtAdvisory->close();
                    }
                    $stmtAdvCheck->close();
                }
            }
            
            // Email about approval (teacher already has account)
            try {
                $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Admin');
                $mail->addAddress($teacherApp['email']);
                $mail->isHTML(true);
                
                if ($advisory) {
                    $mail->Subject = "Advisory Assignment Updated - CDONHS-SHS";
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                                .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                                .header { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>Good day {$teacherApp['first_name']} {$teacherApp['last_name']},</div>
                                <p>Your advisory assignment has been updated.</p>
                                <p>You may now log in to your account to view your advisory class.</p>
                                <p>Thank you,<br><b>CDONHS-SHS Admin</b></p>
                            </div>
                        </body>
                        </html>
                    ";
                } else {
                    $mail->Subject = "Application Approved - CDONHS-SHS";
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                                .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                                .header { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>Good day {$teacherApp['first_name']} {$teacherApp['last_name']},</div>
                                <p>Congratulations! Your application to CDONHS-SHS has been <strong>APPROVED</strong>.</p>
                                <p>However, your advisory assignment has not been decided yet. We will inform you once it has been assigned.</p>
                                <p>Thank you,<br><b>CDONHS-SHS Admin</b></p>
                            </div>
                        </body>
                        </html>
                    ";
                }
                
                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
            
            $mail->clearAddresses();
            
        } else {
            // Generate teacher number
            $teacherNumber = rand(500000, 599999);
            
            // Check if teacher number exists
            $stmtNumCheck = $connection->prepare("SELECT teacher_id FROM teachers WHERE teacher_number = ?");
            $stmtNumCheck->bind_param("i", $teacherNumber);
            $stmtNumCheck->execute();
            $resultNumCheck = $stmtNumCheck->get_result();
            
            if ($resultNumCheck->num_rows > 0) {
                // Generate new teacher number
                $teacherNumber = rand(500000, 599999);
            }
            $stmtNumCheck->close();


            $username = "T_" . $teacherNumber;
            // $tempPassword = bin2hex(random_bytes(4)); THIS IS THE TRUE DEFAULT
            $stringNumber = (string) $teacherNumber;
            $tempPassword = substr($stringNumber, -6);
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

             // Insert into users table (role_id = 3 for teacher)
            $roleId = 3;
            $stmtUser = $connection->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
            $stmtUser->bind_param("ssi", $username, $passwordHash, $roleId);
            $stmtUser->execute();
            $userId = $stmtUser->insert_id;
            $stmtUser->close();
            
            // Create new teacher record with user_id and teacher_number
            $stmtTeacher = $connection->prepare("INSERT INTO teachers (application_id, user_id, teacher_number) VALUES (?, ?, ?)");
            $stmtTeacher->bind_param("iii", $applicationId, $userId, $teacherNumber);
            $stmtTeacher->execute();
            $teacherId = $stmtTeacher->insert_id;
            $stmtTeacher->close();
            
            // Assign advisory if provided
            if ($advisory) {
                $advisoryParts = explode('|', $advisory);
                if (count($advisoryParts) === 3) {
                    $strandId = (int)$advisoryParts[0];
                    $gradeLevel = (int)$advisoryParts[1];
                    $sectionId = (int)$advisoryParts[2];
                    
                    $stmtAdvisory = $connection->prepare("
                        INSERT INTO teacher_advisory (teacher_id, strand_id, grade_level, section_id) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmtAdvisory->bind_param("iiii", $teacherId, $strandId, $gradeLevel, $sectionId);
                    $stmtAdvisory->execute();
                    $stmtAdvisory->close();
                }
            }
            
            // Send email with username and password
            try {
                $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Admin');
                $mail->addAddress($teacherApp['email']);
                $mail->isHTML(true);
                
                if ($advisory) {
                    $mail->Subject = "Application Approved - CDONHS-SHS";
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                                .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                                .header { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
                                .credentials { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>Good day {$teacherApp['first_name']} {$teacherApp['last_name']},</div>
                                <p>Congratulations! Your application to CDONHS-SHS has been <strong>APPROVED</strong>.</p>
                                <p>You may now log in to your account using the credentials below:</p>
                                <div class='credentials'>
                                    <p><strong>Username:</strong> {$username}</p>
                                    <p><strong>Temporary Password:</strong> {$tempPassword}</p>
                                </div>
                                <p>Please change your password after logging in.</p>
                                <p>Thank you,<br><b>CDONHS-SHS Admin</b></p>
                            </div>
                        </body>
                        </html>
                    ";
                } else {
                    $mail->Subject = "Application Approved - CDONHS-SHS";
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                                .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                                .header { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
                                .credentials { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>Good day {$teacherApp['first_name']} {$teacherApp['last_name']},</div>
                                <p>Congratulations! Your application to CDONHS-SHS has been <strong>APPROVED</strong>.</p>
                                <p>However, your advisory assignment has not been decided yet. We will inform you once it has been assigned.</p>
                                <div class='credentials'>
                                    <p><strong>Username:</strong> {$username}</p>
                                    <p><strong>Temporary Password:</strong> {$tempPassword}</p>
                                </div>
                                <p>Please change your password after logging in.</p>
                                <p>Thank you,<br><b>CDONHS-SHS Admin</b></p>
                            </div>
                        </body>
                        </html>
                    ";
                }
                
                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
            
            $mail->clearAddresses();
        }
        
    } elseif ($status === 'Rejected') {
        // Update application status
        $stmt = $connection->prepare("
            UPDATE teacher_applications 
            SET application_status = ?, remarks = ?
            WHERE teacher_application_id = ?
        ");
        $stmt->bind_param("ssi", $status, $remarks, $applicationId);
        $stmt->execute();
        $stmt->close();
        
        // Send rejection email
        try {
            $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Admin');
            $mail->addAddress($teacherApp['email']);
            $mail->isHTML(true);
            
            $mail->Subject = "Application Rejected - CDONHS-SHS";
            $remarksHtml = !empty($remarks) ? "<p><strong>Reason:</strong> {$remarks}</p>" : "";
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                        .header { font-size: 18px; font-weight: bold; color: #dc3545; margin-bottom: 15px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>Good day {$teacherApp['first_name']} {$teacherApp['last_name']},</div>
                        <p>We regret to inform you that your application to CDONHS-SHS has been <strong>REJECTED</strong>.</p>
                        {$remarksHtml}
                        <p>Thank you,<br><b>CDONHS-SHS Admin</b></p>
                    </div>
                </body>
                </html>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
        
        $mail->clearAddresses();
        
        // Delete the teacher application record after sending rejection email
        $stmtDelete = $connection->prepare("DELETE FROM teacher_applications WHERE teacher_application_id = ?");
        $stmtDelete->bind_param("i", $applicationId);
        $stmtDelete->execute();
        $stmtDelete->close();
    } else {
        // Just update status for other cases (Pending, etc.)
        $stmt = $connection->prepare("
            UPDATE teacher_applications 
            SET application_status = ?, remarks = ?
            WHERE teacher_application_id = ?
        ");
        $stmt->bind_param("ssi", $status, $remarks, $applicationId);
        $stmt->execute();
        $stmt->close();
    }
    
    $updatedCount++;
}

ob_end_clean();

echo json_encode([
    'success' => true,
    'message' => "Applications updated successfully. Total: {$updatedCount}"
]);
exit;
