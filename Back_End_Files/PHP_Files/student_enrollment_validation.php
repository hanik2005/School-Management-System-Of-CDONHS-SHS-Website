<?php

function validateStudentEnrollment($connection, $data) {

    $errors = [];

    // ===============================
    // CHECK DUPLICATE LRN
    // ===============================
    $checkLRN = $connection->prepare(
        "SELECT application_id 
         FROM student_applications 
         WHERE lrn = ?"
    );
    $checkLRN->bind_param("s", $data['lrn']);
    $checkLRN->execute();
    $checkLRN->store_result();

    if ($checkLRN->num_rows > 0) {
        $errors[] = "LRN already exists in the system.";
    }

    // ===============================
    // CHECK DUPLICATE EMAIL
    // ===============================
    $checkEmail = $connection->prepare(
        "SELECT application_id 
         FROM student_applications 
         WHERE email = ?"
    );
    $checkEmail->bind_param("s", $data['email']);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $errors[] = "Email already exists in the system.";
    }

    return $errors;
}
