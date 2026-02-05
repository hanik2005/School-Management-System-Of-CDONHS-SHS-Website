<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id = $_POST['student_application_id'];
    $remarks = $_POST['remarks'];
    $status = $_POST['application_status'];

    // ===============================
    // Update enrollment application
    // ===============================
    $update = $connection->prepare(
        "UPDATE student_applications 
         SET remarks = ?, application_status = ?
         WHERE application_id = ?"
    );
    $update->bind_param("ssi", $remarks, $status, $application_id);
    $update->execute();

    // ===============================
    // Get applicant info
    // ===============================
    $getStudent = $connection->prepare(
        "SELECT first_name, last_name, email, lrn
         FROM student_applications
         WHERE application_id = ?"
    );
    $getStudent->bind_param("i", $application_id);
    $getStudent->execute();
    $student = $getStudent->get_result()->fetch_assoc();

    // ===============================
    // IF APPROVED → CREATE STUDENT + USER
    // ===============================
    if ($status === 'Approved' || $status === 'Pending') {

        $connection->begin_transaction();

        // Generate school_id
        $result = $connection->query("SELECT MAX(school_id) AS max_id FROM students");
        $row = $result->fetch_assoc();
        $school_id = ($row['max_id'] ?? 304110) + 1;

        // Insert into students table
        $insertStudent = $connection->prepare(
            "INSERT INTO students (application_id, school_id)
             VALUES (?, ?)"
        );
        $insertStudent->bind_param("ii", $application_id, $school_id);
        $insertStudent->execute();

        // Get Student role_id
        $roleResult = $connection->query(
            "SELECT role_id FROM roles WHERE role_name = 'Student'"
        );
        $role_id = $roleResult->fetch_assoc()['role_id'];

        // Create user account
        $username = $student['lrn'];
        $defaultPassword = substr($student['lrn'], -6);
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $insertUser = $connection->prepare(
            "INSERT INTO users (school_id, username, password, role_id)
             VALUES (?, ?, ?, ?)"
        );
        $insertUser->bind_param(
            "issi",
            $school_id,
            $username,
            $hashedPassword,
            $role_id
        );
        $insertUser->execute();

        $connection->commit();
    }

    // ===============================
    // SEND EMAIL NOTIFICATION
    // ===============================
    try {

        $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Enrollment Office');
        $mail->addAddress(
            $student['email'],
            $student['first_name'] . ' ' . $student['last_name']
        );

        $mail->isHTML(true);
        $mail->Subject = 'CDONHS-SHS Enrollment Status Update';

        $loginInfo = ($status === 'Approved') ? "
        <p><b>Login Credentials:</b></p>
        <ul>
            <li><b>Username:</b> {$student['lrn']}</li>
            <li><b>Temporary Password:</b> {$defaultPassword}</li>
        </ul>
        <p>Please change your password after first login.</p>
        " : "";

        $mail->Body = "
        <p>Good day <b>{$student['first_name']} {$student['last_name']}</b>,</p>
        <p>Your enrollment application has been <b>$status</b>.</p>
        <p><b>Admin Remarks:</b></p>
        <p>$remarks</p>
        $loginInfo
        <br>
        <p>Thank you,<br><b>CDONHS-SHS Enrollment Office</b></p>
        ";

        $mail->send();

    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
    }

    header("Location: ../../Website_Files/Admin_Files/admin_student_application_list.php");
    exit;
}
?>
