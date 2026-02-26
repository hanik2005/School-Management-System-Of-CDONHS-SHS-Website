<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id      = $_POST['teacher_application_id'];
    $remarks             = $_POST['remarks'];
    $status              = $_POST['application_status'];
    $advisory_assignment = $_POST['advisory_assignment'] ?? null;

    // ===============================
    // Get teacher info FIRST (before any update/delete)
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
    // IF REJECTED → Delete application and send email
    // ===============================
    if ($status === 'Rejected') {
        // Send rejection email
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
            <p>We regret to inform you that your <b>teacher application</b> has been <b>rejected</b>.</p>
            <p><b>Admin Remarks:</b></p>
            <p>$remarks</p>
            <br>
            <p>Thank you for your interest in CDONHS-SHS.</p>
            <p><br>
            <b>CDONHS-SHS Human Resources Office</b></p>
            ";

            $mail->send();

        } catch (Exception $e) {
            error_log('Mail Error: ' . $mail->ErrorInfo);
        }

        // Delete the application
        $delete = $connection->prepare(
            "DELETE FROM teacher_applications WHERE teacher_application_id = ?"
        );
        $delete->bind_param("i", $application_id);
        $delete->execute();

        header("Location: ../../Website_Files/Admin_Files/admin_teacher_application_list.php");
        exit;
    }

    // ===============================
    // Update teacher application (for Pending or Approved)
    // ===============================
    $update = $connection->prepare(
        "UPDATE teacher_applications
         SET remarks = ?, application_status = ?
         WHERE teacher_application_id = ?"
    );
    $update->bind_param("ssi", $remarks, $status, $application_id);
    $update->execute();

    if ($status === 'Approved') {

        $connection->begin_transaction();

        try {

            // ===============================
            // Check if teacher already exists
            // ===============================
            $check = $connection->prepare(
                "SELECT teacher_id, teacher_number, user_id
                 FROM teachers
                 WHERE application_id = ?"
            );
            $check->bind_param("i", $application_id);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows > 0) {

                // Already created
                $existingTeacher = $checkResult->fetch_assoc();
                $teacher_id = $existingTeacher['teacher_id'];
                $teacher_number  = $existingTeacher['teacher_number'];
                $username   = (string)$teacher_number;
                $tempPassword = "Already Created";

            } else {

                // ===============================
                // Generate teacher_number
                // ===============================
                $result = $connection->query(
                    "SELECT MAX(teacher_number) AS max_id FROM teachers"
                );
                $row = $result->fetch_assoc();
                $teacher_number = ($row['max_id'] ?? 502300) + 1;

                // ===============================
                // Get Teacher role_id
                // ===============================
                $roleResult = $connection->query(
                    "SELECT role_id FROM roles WHERE role_name = 'Teacher'"
                );
                $role_id = $roleResult->fetch_assoc()['role_id'];

                // ===============================
                // Create login account FIRST
                // ===============================
                $username = "T_" . $teacher_number;
               // $tempPassword = bin2hex(random_bytes(4)); THIS IS THE TRUE DEFAULT
                $stringNumber = (string) $teacher_number;
                $tempPassword = substr($stringNumber, -6);
                $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

                $insertUser = $connection->prepare(
                    "INSERT INTO users
                        (username, password, role_id, status)
                     VALUES (?, ?, ?, 'Active')"
                );
                $insertUser->bind_param(
                    "ssi",
                    $username,
                    $passwordHash,
                    $role_id
                );
                $insertUser->execute();

                $user_id = $connection->insert_id;

                // ===============================
                // Insert teacher WITH user_id
                // ===============================
                $insertTeacher = $connection->prepare(
                    "INSERT INTO teachers (user_id, application_id, teacher_number)
                     VALUES (?, ?, ?)"
                );
                $insertTeacher->bind_param(
                    "iii",
                    $user_id,
                    $application_id,
                    $teacher_number
                );
                $insertTeacher->execute();

                $teacher_id = $connection->insert_id;
            }

            // ===============================
            // HANDLE ADVISORY
            // ===============================
            if (!empty($advisory_assignment)) {

                list($strand_id, $grade_level, $section_id) =
                    explode("|", $advisory_assignment);

                // Check if section already assigned
                $checkSection = $connection->prepare(
                    "SELECT teacher_advisory_id
                     FROM teacher_advisory
                     WHERE strand_id = ?
                     AND grade_level = ?
                     AND section_id = ?
                     AND teacher_id != ?"
                );

                $checkSection->bind_param(
                    "iiii",
                    $strand_id,
                    $grade_level,
                    $section_id,
                    $teacher_id
                );

                $checkSection->execute();
                if ($checkSection->get_result()->num_rows > 0) {
                    throw new Exception("This section already has an adviser.");
                }

                // Insert or update advisory
                $checkTeacherAdv = $connection->prepare(
                    "SELECT teacher_advisory_id
                     FROM teacher_advisory
                     WHERE teacher_id = ?"
                );
                $checkTeacherAdv->bind_param("i", $teacher_id);
                $checkTeacherAdv->execute();

                if ($checkTeacherAdv->get_result()->num_rows > 0) {

                    $updateAdvisory = $connection->prepare(
                        "UPDATE teacher_advisory
                         SET strand_id = ?, grade_level = ?, section_id = ?
                         WHERE teacher_id = ?"
                    );
                    $updateAdvisory->bind_param(
                        "iiii",
                        $strand_id,
                        $grade_level,
                        $section_id,
                        $teacher_id
                    );
                    $updateAdvisory->execute();

                } else {

                    $insertAdvisory = $connection->prepare(
                        "INSERT INTO teacher_advisory
                         (teacher_id, strand_id, grade_level, section_id)
                         VALUES (?, ?, ?, ?)"
                    );
                    $insertAdvisory->bind_param(
                        "iiii",
                        $teacher_id,
                        $strand_id,
                        $grade_level,
                        $section_id
                    );
                    $insertAdvisory->execute();
                }
            }

            $connection->commit();

        } catch (Exception $e) {
            $connection->rollback();
            die("Transaction failed: " . $e->getMessage());
        }
    }

    // ===============================
    // SEND EMAIL
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
        <p>Your <b>teacher application</b> has been <b>$status</b>.</p>
        <p><b>Admin Remarks:</b></p>
        <p>$remarks</p>
        ";

        if ($status === 'Approved') {
            $mail->Body .= "
            <hr>
            <p><b>Your Login Credentials:</b></p>
            <p>
                Username: <b>$username</b><br>
                Temporary Password: <b>$tempPassword</b>
            </p>
            <p style='color:red;'>Please change your password after first login.</p>
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
