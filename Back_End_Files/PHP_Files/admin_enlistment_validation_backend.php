<?php
include "../../DB_Connection/Connection.php";
include "mailer_details.php";

if (isset($_POST['confirm']) && isset($_POST['status'])) {

    foreach ($_POST['status'] as $student_id => $status) {

        // Update student's enlistment status
        $stmt = $connection->prepare("
            UPDATE students 
            SET enlistment_status=? 
            WHERE student_id=?
        ");

        $stmt->bind_param("si", $status, $student_id);
        $stmt->execute();
        $stmt->close();

        // If admin confirms as "Enlisted", update subject statuses
        if ($status === 'Enlisted') {
            // Update subjects that student requested to "Enrolled"
            $stmtEnrolled = $connection->prepare("
                UPDATE student_subjects 
                SET status = 'Enrolled' 
                WHERE student_id = ? AND requested = 1 AND status = 'Pending'
            ");
            $stmtEnrolled->bind_param("i", $student_id);
            $stmtEnrolled->execute();
            $stmtEnrolled->close();

            // Update subjects that student did NOT request to "Dropped"
            $stmtDropped = $connection->prepare("
                UPDATE student_subjects 
                SET status = 'Dropped' 
                WHERE student_id = ? AND requested = 0 AND status = 'Pending'
            ");
            $stmtDropped->bind_param("i", $student_id);
            $stmtDropped->execute();
            $stmtDropped->close();

            // Get student's email and name for notification
            $stmtGetEmail = $connection->prepare("
                SELECT sa.email, sa.first_name, sa.last_name
                FROM students s
                INNER JOIN student_applications sa ON s.application_id = sa.application_id
                WHERE s.student_id = ?
            ");
            $stmtGetEmail->bind_param("i", $student_id);
            $stmtGetEmail->execute();
            $resultEmail = $stmtGetEmail->get_result();
            $studentInfo = $resultEmail->fetch_assoc();
            $stmtGetEmail->close();

            // Send approval email notification
            if ($studentInfo) {
                try {
                    $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Enrollment Office');
                    $mail->addAddress($studentInfo['email']);
                    $mail->isHTML(true);
                    $mail->Subject = "Enlistment Application Approved";

                    $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                            .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                            .header { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
                            .status { font-weight: bold; color: #28a745; }
                            .footer { margin-top: 30px; font-size: 14px; color: #777; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>Good day {$studentInfo['first_name']} {$studentInfo['last_name']},</div>
                            
                            <p>We are pleased to inform you that your <b>enlistment application</b> has been <span class='status'>APPROVED</span>!</p>
                            
                            <p>You are now officially enlisted. Please proceed to enrollment to complete your registration.</p>
                            
                            <p>Thank you and welcome to CDONHS-SHS!<br>
                            <b>CDONHS-SHS Enrollment Office</b></p>
                            
                            <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
                        </div>
                    </body>
                    </html>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                }
            }
        }

        // If admin rejects, delete pending subjects and send email notification
        if ($status === 'Rejected') {
            // Delete pending subjects
            $stmtReject = $connection->prepare("
                DELETE FROM student_subjects 
                WHERE student_id = ? AND status = 'Pending'
            ");
            $stmtReject->bind_param("i", $student_id);
            $stmtReject->execute();
            $stmtReject->close();

            // Get student's email and name for notification
            $stmtGetEmail = $connection->prepare("
                SELECT sa.email, sa.first_name, sa.last_name
                FROM students s
                INNER JOIN student_applications sa ON s.application_id = sa.application_id
                WHERE s.student_id = ?
            ");
            $stmtGetEmail->bind_param("i", $student_id);
            $stmtGetEmail->execute();
            $resultEmail = $stmtGetEmail->get_result();
            $studentInfo = $resultEmail->fetch_assoc();
            $stmtGetEmail->close();

            // Send rejection email notification
            if ($studentInfo) {
                try {
                    $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Enrollment Office');
                    $mail->addAddress($studentInfo['email']);
                    $mail->isHTML(true);
                    $mail->Subject = "Enlistment Application Rejected";

                    $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
                            .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                            .header { font-size: 18px; font-weight: bold; color: #dc3545; margin-bottom: 15px; }
                            .status { font-weight: bold; color: #dc3545; }
                            .footer { margin-top: 30px; font-size: 14px; color: #777; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>Good day {$studentInfo['first_name']} {$studentInfo['last_name']},</div>
                            
                            <p>We regret to inform you that your <b>enlistment application</b> has been <span class='status'>REJECTED</span>.</p>
                            
                            <p>Your selected subjects have been removed from the system. You may contact the school administration for more information regarding this decision.</p>
                            
                            <p>Thank you for your understanding.<br>
                            <b>CDONHS-SHS Enrollment Office</b></p>
                            
                            <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
                        </div>
                    </body>
                    </html>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                }
            }
        }
    }

    header("Location: ../../Website_Files/Admin_Files/enlistment_validation_page.php");
    exit;
}
?>
