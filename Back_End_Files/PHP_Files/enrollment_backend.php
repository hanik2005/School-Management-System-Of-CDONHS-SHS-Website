<?php
// ===============================
// Use existing DB Connection
// ===============================
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

// ===============================
// Check if form submitted
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ===============================
    // Collect Form Data
    // ===============================
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleName = $_POST['middleName'] ?? null;
    $extensionName = $_POST['extensionName'] ?? null;
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
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
    $yearGraduated = $_POST['yearGraduated'];

    $fatherGuardianName = $_POST['fatherGuardianName'];
    $fatherGuardianContact = $_POST['fatherGuardianContact'];
    $motherGuardianName = $_POST['motherGuardianName'];
    $motherGuardianContact = $_POST['motherGuardianContact'];

    // ===============================
    // File Upload Setup (OPTIONAL)
    // ===============================
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
    $sql = "INSERT INTO enrollment_applications (
                first_name, last_name, middle_name, extension_name,
                date_of_birth, gender, civil_status,
                house_number_street, barangay, city_municipality, province,
                contact_number, email, facebook_profile,
                current_school, school_classification, year_graduated,
                father_guardian_name, father_guardian_contact,
                mother_guardian_name, mother_guardian_contact,
                psa_birth_certificate, form_138, student_id_copy,
                application_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $stmt = $connection->prepare($sql);

    $stmt->bind_param(
        "ssssssssssssssssisssssss",
        $firstName, $lastName, $middleName, $extensionName,
        $dob, $gender, $civilStatus,
        $houseNumberStreet, $barangay, $cityMunicipality, $province,
        $contactNumber, $email, $facebookName,
        $currentSchool, $schoolClassification, $yearGraduated,
        $fatherGuardianName, $fatherGuardianContact,
        $motherGuardianName, $motherGuardianContact,
        $psaBirthCertificate, $form138, $studentID
    );

    if ($stmt->execute()) {

        $to = $email;
        $subject = "CDONHS-SHS Enrollment Application Submitted";

        $message = "
            Good day $firstName $lastName,

            Your enrollment application has been successfully submitted.

            Current Status: PENDING

            Please submit all required documents on or before the deadline.
            You may check your enrollment status online.

            Thank you,
            CDONHS-SHS Enrollment Office
            ";

        $headers = "From: cdonhs-shs@school.edu.ph\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8";

        mail($to, $subject, $message, $headers);

        echo "<script>
                alert('Enrollment submitted successfully. Please submit required documents before the deadline.');
                window.location.href='../../Website_Files/thank_you.php';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
