<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===============================
// PHPMailer (Manual Import)
// ===============================
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';

// ===============================
// DB Connection
// ===============================
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id = $_POST['application_id'];
    $remarks = $_POST['remarks'];
    $status = $_POST['application_status'];

    // ===============================
    // Update status and remarks
    // ===============================
    $update = $connection->prepare(
        "UPDATE enrollment_applications 
         SET remarks = ?, application_status = ?
         WHERE application_id = ?"
    );
    $update->bind_param("ssi", $remarks, $status, $application_id);
    $update->execute();

    // ===============================
    // Get student info
    // ===============================
    $getStudent = $connection->prepare(
        "SELECT email, first_name, last_name 
         FROM enrollment_applications 
         WHERE application_id = ?"
    );
    $getStudent->bind_param("i", $application_id);
    $getStudent->execute();
    $result = $getStudent->get_result();
    $student = $result->fetch_assoc();

    try {

        $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Enrollment Office');
        $mail->addAddress($student['email'], $student['first_name'] . ' ' . $student['last_name']);

        $mail->isHTML(true);
        $mail->Subject = 'CDONHS-SHS Enrollment Status Update';

        $mail->Body = "
        <html>
        <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
            .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
            .header { font-size: 18px; font-weight: bold; color: #0056b3; margin-bottom: 15px; }
            .status { font-weight: bold; color: #ff6600; }
            .remarks { margin-left: 20px; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffcc00; border-radius: 4px; }
            .footer { margin-top: 30px; font-size: 14px; color: #777; }
        </style>
        </head>
        <body>
        <div class='container'>
            <div class='header'>Good day <b>{$student['first_name']} {$student['last_name']}</b>,</div>
            
            <p>Your enrollment application has been <b>updated</b>.</p>
            
            <p><b>Status:</b> <span class='status'>$status</span></p>
            
            <p><b>Admin Remarks:</b></p>
            <div class='remarks'>$remarks</div>
            
            <p>Please comply with the instructions above if required.</p>
            
            <br>
            <p>Thank you,<br>
            <b>CDONHS-SHS Enrollment Office</b></p>
            
            <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
        </div>
        </body>
        </html>
        ";

// Plain text version
$mail->AltBody = "Good day {$student['first_name']} {$student['last_name']},

Your enrollment application has been updated.

Status: $status

Admin Remarks:
$remarks

Please comply with the instructions above if required.

Thank you,
CDONHS-SHS Enrollment Office";

        $mail->send();

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
    }

    // ===============================
    // Redirect back to admin page
    // ===============================
    header("Location: ../../Website_Files/Admin_Files/admin_enrollment_list.php");
    exit;
}
?>
