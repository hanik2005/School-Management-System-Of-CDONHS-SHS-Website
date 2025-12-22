<?php
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
    // Get student email
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
    // EMAIL NOTIFICATION (STATUS UPDATE)
    // ===============================
    $to = $student['email'];
    $subject = "CDONHS-SHS Enrollment Status Update";

    $message = "
Good day {$student['first_name']} {$student['last_name']},

Your enrollment application has been updated.

Status: $status

Admin Remarks:
$remarks

Please comply with the instructions above if required.

Thank you,
CDONHS-SHS Enrollment Office
";

    $headers = "From: cdonhs-shs@school.edu.ph\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8";

    mail($to, $subject, $message, $headers);

    header("Location: ../../Website_Files/Admin_Files/admin_enrollment_list.php");
    exit;
}
?>
