<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===============================
// PHPMailer (Manual Import)
// ===============================
require $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/mailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/mailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/mailer/src/SMTP.php';

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

    // ===============================
    // SEND EMAIL VIA PHPMailer
    // ===============================
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nickcharlesclarito@gmail.com'; // Gmail
        $mail->Password   = 'ygcutibfqfhgzzdt';              // App Password
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('nickcharlesclarito@gmail.com', 'CDONHS-SHS Enrollment Office');
        $mail->addAddress($student['email'], $student['first_name'] . ' ' . $student['last_name']);

        $mail->isHTML(true);
        $mail->Subject = 'CDONHS-SHS Enrollment Status Update';

        $mail->Body = "
            <p>Good day <b>{$student['first_name']} {$student['last_name']}</b>,</p>

            <p>Your enrollment application has been <b>updated</b>.</p>

            <p><b>Status:</b> $status</p>

            <p><b>Admin Remarks:</b></p>
            <p>$remarks</p>

            <p>Please comply with the instructions above if required.</p>

            <br>
            <p>Thank you,<br>
            <b>CDONHS-SHS Enrollment Office</b></p>
        ";

        $mail->AltBody = "Good day {$student['first_name']} {$student['last_name']},
Your enrollment application has been updated.

Status: $status

Admin Remarks:
$remarks

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
