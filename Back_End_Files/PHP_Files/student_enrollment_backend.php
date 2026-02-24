<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/mailer_details.php';
include "student_enrollment_validation.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleName = $_POST['middleName'] ?? null;
    $extensionName = $_POST['extensionName'] ?? null;
    $lrn = $_POST['lrn'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $civilStatus = $_POST['civilStatus'];

    $houseNumberStreet = $_POST['houseNumberStreet'];
    $barangay = $_POST['barangay'];
    $cityMunicipality = $_POST['cityMunicipality'];
    $province = $_POST['province'];

    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $facebookName = $_POST['facebookName'] ?? null;

    $currentSchool = $_POST['currentSchool'];
    $schoolClassification = $_POST['schoolClassification'];
    $enrollmentType = $_POST['enrollmentType'];
    $yearGraduated = $_POST['yearGraduated'];

    $fatherGuardianName = $_POST['fatherGuardianName'];
    $fatherGuardianContact = $_POST['fatherGuardianContact'];
    $motherGuardianName = $_POST['motherGuardianName'];
    $motherGuardianContact = $_POST['motherGuardianContact'];

     // ===============================
    // VALIDATION
    // ===============================
    $data = [
        'lrn' => $lrn,
        'email' => $email
    ];

    $errors = validateStudentEnrollment($connection, $data);

    if (!empty($errors)) {

        echo "<script>
                alert('" . implode("\\n", $errors) . "');
                window.history.back();
              </script>";
        exit;
    }


    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/SMS_CDONHS-SHS_WEBSITE/uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadOptionalFile($inputName, $prefix, $uploadDir) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== 0) {
            return null; // File not uploaded yet
        }

        $fileName = time() . "_" . $prefix . "_" . basename($_FILES[$inputName]['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
            return "uploads/" . $fileName; // Save relative path
        }
        return null;
    }

    $psaBirthCertificate = uploadOptionalFile("psaBirthCertificate", "PSA", $uploadDir);
    $form138 = uploadOptionalFile("form138", "FORM138", $uploadDir);
    $studentID = uploadOptionalFile("studentID", "STUDENTID", $uploadDir);

    // ===============================
    // Insert Enrollment Data
    // ===============================
    $sql = "INSERT INTO student_applications (
                first_name, last_name, middle_name, extension_name, lrn,
                date_of_birth, sex, civil_status,
                house_number_street, barangay, city_municipality, province,
                contact_number, email, facebook_profile,
                current_school, school_classification, enrollment_type, year_graduated,
                father_guardian_name, father_guardian_contact,
                mother_guardian_name, mother_guardian_contact,
                psa_birth_certificate, form_138, student_id_copy,
                application_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $stmt = $connection->prepare($sql);

    $stmt->bind_param(
        "ssssisssssssssssssisssssss",
        $firstName, $lastName, $middleName, $extensionName, $lrn,
        $dob, $sex, $civilStatus,
        $houseNumberStreet, $barangay, $cityMunicipality, $province,
        $contactNumber, $email, $facebookName,
        $currentSchool, $schoolClassification, $enrollmentType, $yearGraduated,
        $fatherGuardianName, $fatherGuardianContact,
        $motherGuardianName, $motherGuardianContact,
        $psaBirthCertificate, $form138, $studentID
    );

    if ($stmt->execute()) {


    try{

      $mail-> setFrom('cdonhsshsacc@gmail.com', 'CDONHS-SHS Enrollment Office');
      $mail->addAddress($email);
      $mail->isHTML(true);
      $mail->Subject = "CDONHS-SHS Enrollment Application Submitted";

      $mail->Body = "
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; color: #333; line-height: 1.5; }
    .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
    .header { font-size: 18px; font-weight: bold; color: #0056b3; margin-bottom: 15px; }
    .status { font-weight: bold; color: #ff6600; }
    .footer { margin-top: 30px; font-size: 14px; color: #777; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>Good day $firstName $lastName,</div>
    
    <p>Your enrollment application has been <b>successfully submitted</b>.</p>
    
    <p>Current Status: <span class='status'>PENDING</span></p>
    
    <p>Please submit all required documents on or before the deadline. You may check your enrollment status online at any time.</p>
    
    <p>Thank you,<br>
    <b>CDONHS-SHS Enrollment Office</b></p>
    
    <div class='footer'>&copy; " . date("Y") . " CDONHS-SHS. All rights reserved.</div>
  </div>
</body>
</html>
";
        $mail->send();
    }catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
    }
        echo "<script>
                alert('Enrollment submitted successfully. Please submit required documents before the deadline.');
                window.location.href='../../Website_Files/thank_you.php';
              </script>";
    } else {
        echo "<script>
                alert('Error submitting enrollment.');
                window.history.back();
              </script>";
    }

    $stmt->close();
}
?>
