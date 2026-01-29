<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id = $_POST['teacher_application_id'];
    $remarks        = $_POST['remarks'];
    $status         = $_POST['application_status'];

    // ===============================
    // Update teacher application
    // ===============================
    $update = $connection->prepare(
        "UPDATE teacher_applications
         SET remarks = ?, application_status = ?
         WHERE teacher_application_id = ?"
    );
    $update->bind_param("ssi", $remarks, $status, $application_id);
    $update->execute();

    // ===============================
    // Get teacher info
    // ===============================
    $getTeacher = $connection->prepare(
        "SELECT first_name, last_name, email
         FROM teacher_applications
         WHERE teacher_application_id = ?"
    );
    $getTeacher->bind_param("i", $application_id);
    $getTeacher->execute();
    $teacher = $getTeacher->get_result()->fetch_assoc();

    // ===============================
    // IF APPROVED → INSERT + CREATE ACCOUNT
    // ===============================
    if ($status === 'Approved' || $status === 'Pending') {

        $connection->begin_transaction();

        try {
            // Generate next school_id
            $result = $connection->query(
                "SELECT MAX(school_id) AS max_id FROM teachers"
            );
            $row = $result->fetch_assoc();
            $school_id = ($row['max_id'] ?? 502300) + 1;

            // Prevent duplicate insert
            $check = $connection->prepare(
                "SELECT teacher_id FROM teachers WHERE application_id = ?"
            );
            $check->bind_param("i", $application_id);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows === 0) {

                // -------------------------------
                // INSERT INTO teachers
                // -------------------------------
                $insertTeacher = $connection->prepare(
                    "INSERT INTO teachers (application_id, school_id)
                     VALUES (?, ?)"
                );
                $insertTeacher->bind_param("ii", $application_id, $school_id);
                $insertTeacher->execute();

                $teacher_id = $connection->insert_id;

                // -------------------------------
                // GENERATE USERNAME & PASSWORD
                // -------------------------------
                $username = (string)$school_id;

                $tempPassword = bin2hex(random_bytes(4)); // 8-char temp password
                $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

                // -------------------------------
                // INSERT INTO teacher_accounts
                // -------------------------------
                $insertAccount = $connection->prepare(
                    "INSERT INTO users 
                        (school_id, username, password, role_id, status)
                     VALUES (?, ?, ?, 3, 'Active')"
                );
                $insertAccount->bind_param("iss", $school_id, $username, $passwordHash);
                $insertAccount->execute();
            }

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

        $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS HR Office');
        $mail->addAddress(
            $teacher['email'],
            $teacher['first_name'] . ' ' . $teacher['last_name']
        );

        $mail->isHTML(true);
        $mail->Subject = 'CDONHS-SHS Teacher Application Status Update';

        $mail->Body = "
        <p>Good day <b>{$teacher['first_name']} {$teacher['last_name']}</b>,</p>

        <p>Your <b>teacher application</b> has been 
        <b>$status</b>.</p>

        <p><b>Admin Remarks:</b></p>
        <p>$remarks</p>
        ";

        // ADD LOGIN INFO IF APPROVED
        if ($status === 'Approved' || $status === 'Pending') {
            $mail->Body .= "
            <hr>
            <p><b>Your Login Credentials:</b></p>
            <p>
                Username: <b>$username</b><br>
                Temporary Password: <b>$tempPassword</b>
            </p>
            <p style='color:red;'>
                Please change your password after first login.
            </p>
            ";
        }

        $mail->Body .= "
        <br>
        <p>Thank you,<br>
        <b>CDONHS-SHS Human Resources Office</b></p>
        ";

        $mail->send();

    } catch (Exception $e) {
        error_log('Mail Error: ' . $mail->ErrorInfo);
    }

    header("Location: ../../Website_Files/Admin_Files/admin_teacher_application_list.php");
    exit;
}
?>
