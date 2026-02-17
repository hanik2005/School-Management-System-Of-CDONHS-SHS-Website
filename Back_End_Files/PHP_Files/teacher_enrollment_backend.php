<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';
include "teacher_registration_validation.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ===============================
    // Basic Information
    // ===============================
    $firstName      = $_POST['firstName'];
    $lastName       = $_POST['lastName'];
    $middleName     = $_POST['middleName'] ?? null;
    $extensionName  = $_POST['extensionName'] ?? null;
    $dob            = $_POST['dob'];
    $gender         = $_POST['gender'];
    $civilStatus    = $_POST['civilStatus'];

    // ===============================
    // Contact Information
    // ===============================
    $contactNumber  = $_POST['contactNumber'];
    $email          = $_POST['email'];
    $facebook       = $_POST['facebookProfile'] ?? null;

    // ===============================
    // Address
    // ===============================
    $houseStreet    = $_POST['houseNumberStreet'];
    $barangay       = $_POST['barangay'];
    $cityMunicipal  = $_POST['cityMunicipality'];
    $province       = $_POST['province'];

    // ===============================
    // Professional Info
    // ===============================
    $currentSchool  = $_POST['currentSchool'];
    $education      = $_POST['highestEducation'];
    $specialization = $_POST['specialization'];

     // ===============================
    // VALIDATION
    // ===============================
    $data = [
        'email' => $email,
        'contactNumber' => $contactNumber
    ];

    $errors = validateTeacherEnrollment($connection, $data);

    if (!empty($errors)) {

        echo "<script>
                alert('" . implode("\\n", $errors) . "');
                window.history.back();
              </script>";
        exit;
    }

    // ===============================
    // Uploads
    // ===============================
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/SMS_CDONHS-SHS_WEBSITE/uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadFile($input, $prefix, $dir) {
        if (!isset($_FILES[$input]) || $_FILES[$input]['error'] !== 0) {
            return null;
        }

        $fileName = time() . "_" . $prefix . "_" . basename($_FILES[$input]['name']);
        $target   = $dir . $fileName;

        if (move_uploaded_file($_FILES[$input]['tmp_name'], $target)) {
            return "uploads/" . $fileName;
        }
        return null;
    }

    $resume       = uploadFile("resumeCV", "RESUME", $uploadDir);
    $prcId        = uploadFile("prcId", "PRC", $uploadDir);
    $certificates = uploadFile("certifications", "CERT", $uploadDir);
    $otherDocs    = uploadFile("otherDocuments", "OTHER", $uploadDir);

    // ===============================
    // Insert Teacher Application
    // ===============================
    $sql = "INSERT INTO teacher_applications (
                first_name, last_name, middle_name, extension_name,
                date_of_birth, gender, civil_status,
                contact_number, email, facebook_profile,
                house_number_street, barangay, city_municipality, province,
                current_school, highest_education, specialization,
                resume_cv, prc_id_copy, certificates, other_documents,
                application_status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending'
            )";

    $stmt = $connection->prepare($sql);

    $stmt->bind_param(
        "sssssssssssssssssssss",
        $firstName, $lastName, $middleName, $extensionName,
        $dob, $gender, $civilStatus,
        $contactNumber, $email, $facebook,
        $houseStreet, $barangay, $cityMunicipal, $province,
        $currentSchool, $education, $specialization,
        $resume, $prcId, $certificates, $otherDocs
    );

    if ($stmt->execute()) {

        // ===============================
        // Email Notification
        // ===============================
        try {
            $mail->setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS HR Office');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Teacher Application Submitted";

            $mail->Body = "
            <p>Good day <b>$firstName $lastName</b>,</p>
            <p>Your <b>teacher application</b> has been successfully submitted.</p>
            <p><b>Status:</b> Pending</p>
            <p>Please wait for further evaluation.</p>
            <br>
            <p>Regards,<br><b>CDONHS-SHS HR Office</b></p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        echo "<script>
                alert('Application submitted successfully!');
                window.location.href='../../Website_Files/thank_you.php';
              </script>";

    } else {
         echo "<script>
                alert('Error submitting teacher application.');
                window.history.back();
              </script>";
    }

    $stmt->close();
}
?>
