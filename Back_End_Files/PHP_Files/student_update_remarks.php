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
    // Get applicant info FIRST (before any update/delete)
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
    // IF REJECTED → Delete application and send email
    // ===============================
    if ($status === 'Rejected') {
        // Send rejection email
        try {
            $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Enrollment Office');
            $mail->addAddress(
                $student['email'],
                $student['first_name'] . ' ' . $student['last_name']
            );

            $mail->isHTML(true);
            $mail->Subject = 'CDONHS-SHS Enrollment Status Update';

            $mail->Body = "
            <p>Good day <b>{$student['first_name']} {$student['last_name']}</b>,</p>
            <p>We regret to inform you that your enrollment application has been <b>rejected</b>.</p>
            <p><b>Admin Remarks:</b></p>
            <p>$remarks</p>
            <br>
            <p>Thank you for your interest in CDONHS-SHS.</p>
            <p><br>
            <b>CDONHS-SHS Enrollment Office</b></p>
            ";

            $mail->send();

        } catch (Exception $e) {
            error_log('Mail Error: ' . $mail->ErrorInfo);
        }

        // Delete the application
        $delete = $connection->prepare(
            "DELETE FROM student_applications WHERE application_id = ?"
        );
        $delete->bind_param("i", $application_id);
        $delete->execute();

        header("Location: ../../Website_Files/Admin_Files/admin_student_application_list.php");
        exit;
    }

    // ===============================
    // Update enrollment application (for Pending or Approved)
    // ===============================
    $update = $connection->prepare(
        "UPDATE student_applications 
         SET remarks = ?, application_status = ?
         WHERE application_id = ?"
    );
    $update->bind_param("ssi", $remarks, $status, $application_id);
    $update->execute();

    // ===============================
    // IF APPROVED → CREATE USER + STUDENT
    // ===============================
    if ($status === 'Approved') {

        $connection->begin_transaction();

        try {

            // Generate student_number
            $result = $connection->query("SELECT MAX(student_number) AS max_id FROM students");
            $row = $result->fetch_assoc();
            $student_number = ($row['max_id'] ?? 304111) + 1;

            // Get Student role_id
            $roleResult = $connection->query(
                "SELECT role_id FROM roles WHERE role_name = 'Student'"
            );
            $role_id = $roleResult->fetch_assoc()['role_id'];

            // Create login credentials
            $username = $student['lrn'];
            $defaultPassword = substr($student['lrn'], -6);
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // ===============================
            // 1️⃣ INSERT INTO USERS FIRST
            // ===============================
            $insertUser = $connection->prepare(
                "INSERT INTO users (username, password, role_id)
                 VALUES (?, ?, ?)"
            );
            $insertUser->bind_param(
                "ssi",
                $username,
                $hashedPassword,
                $role_id
            );
            $insertUser->execute();

            // Get generated user_id
            $user_id = $connection->insert_id;

            // ===============================
            // 2️⃣ INSERT INTO STUDENTS WITH user_id
            // ===============================
            
            // Get current school year
            // Philippine school year runs from August to May/June
            // If current month is January-July (1-7), we're in the school year that started last year
            // If current month is August-December (8-12), we're in the school year that started this year
            $currentMonth = date('n');
            $currentYear = date('Y');
            if ($currentMonth >= 8) {
                $school_year = $currentYear . '-' . ($currentYear + 1);
            } else {
                $school_year = ($currentYear - 1) . '-' . $currentYear;
            }
            
            $insertStudent = $connection->prepare(
                "INSERT INTO students (user_id, application_id, student_number, school_year)
                 VALUES (?, ?, ?, ?)"
            );
            $insertStudent->bind_param(
                "iiis",
                $user_id,
                $application_id,
                $student_number,
                $school_year
            );
            $insertStudent->execute();

            $connection->commit();

        } catch (Exception $e) {
            $connection->rollback();
            die("Transaction failed: " . $e->getMessage());
        }
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
            <li><b>Username:</b> {$username}</li>
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
