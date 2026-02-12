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
    // IF APPROVED OR PENDING
    // ===============================
    if ($status === 'Approved' || $status === 'Pending') {

        $connection->begin_transaction();

        try {

            // ===============================
            // Check if teacher already exists
            // ===============================
            $check = $connection->prepare(
                "SELECT teacher_id, school_id 
                 FROM teachers 
                 WHERE application_id = ?"
            );
            $check->bind_param("i", $application_id);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows > 0) {

                $existingTeacher = $checkResult->fetch_assoc();
                $teacher_id = $existingTeacher['teacher_id'];
                $school_id  = $existingTeacher['school_id'];

                $username = (string)$school_id;
                $tempPassword = "Already Created";

            } else {

                // ===============================
                // Generate school_id
                // ===============================
                $result = $connection->query(
                    "SELECT MAX(school_id) AS max_id FROM teachers"
                );
                $row = $result->fetch_assoc();
                $school_id = ($row['max_id'] ?? 502300) + 1;

                // ===============================
                // Insert teacher
                // ===============================
                $insertTeacher = $connection->prepare(
                    "INSERT INTO teachers (application_id, school_id)
                     VALUES (?, ?)"
                );
                $insertTeacher->bind_param("ii", $application_id, $school_id);
                $insertTeacher->execute();

                $teacher_id = $connection->insert_id;

                // ===============================
                // Create login account
                // ===============================
                $username = (string)$school_id;

                $tempPassword = bin2hex(random_bytes(4));
                $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

                $insertAccount = $connection->prepare(
                    "INSERT INTO users 
                        (school_id, username, password, role_id, status)
                     VALUES (?, ?, ?, 3, 'Active')"
                );
                $insertAccount->bind_param("iss", $school_id, $username, $passwordHash);
                $insertAccount->execute();
            }

            // ===============================
            // INSERT TEACHER ADVISORY
            // ===============================
            if (!empty($advisory_assignment) && $status === 'Approved') {

                list($strand_id, $grade_level, $section_id) = explode("|", $advisory_assignment);

                // 🔥 Check if another teacher already handles this section
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
                $sectionExists = $checkSection->get_result();

                if ($sectionExists->num_rows > 0) {
                    throw new Exception("This section already has an adviser.");
                }

                // 🔥 Check if THIS teacher already has advisory
                $checkTeacherAdv = $connection->prepare(
                    "SELECT teacher_advisory_id 
                    FROM teacher_advisory 
                    WHERE teacher_id = ?"
                );

                $checkTeacherAdv->bind_param("i", $teacher_id);
                $checkTeacherAdv->execute();
                $teacherAdvResult = $checkTeacherAdv->get_result();

                if ($teacherAdvResult->num_rows > 0) {

                    // 👉 UPDATE advisory
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

                    // 👉 INSERT new advisory
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

        if ($status === 'Approved' || $status === 'Pending') {
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
