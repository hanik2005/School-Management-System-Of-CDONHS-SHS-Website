<?php

function validateTeacherEnrollment($connection, $data) {

    $errors = [];

    // ===============================
    // CHECK DUPLICATE EMAIL
    // ===============================
    $checkEmail = $connection->prepare(
        "SELECT teacher_application_id
         FROM teacher_applications
         WHERE email = ?"
    );
    $checkEmail->bind_param("s", $data['email']);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $errors[] = "Email already exists in the system.";
    }

    // ===============================
    // CHECK DUPLICATE CONTACT NUMBER
    // ===============================
    $checkContact = $connection->prepare(
        "SELECT teacher_application_id
         FROM teacher_applications
         WHERE contact_number = ?"
    );
    $checkContact->bind_param("s", $data['contactNumber']);
    $checkContact->execute();
    $checkContact->store_result();

    if ($checkContact->num_rows > 0) {
        $errors[] = "Contact number already exists.";
    }

    return $errors;
}
