<?php

// Helper function to validate name (allows letters, spaces, hyphens, apostrophes, and periods for abbreviations)
function validateName($name, $fieldName = 'Name') {
    $errors = [];
    
    // Trim whitespace
    $name = trim($name);
    
    // Check if empty
    if (empty($name)) {
        $errors[] = "$fieldName is required.";
        return $errors;
    }
    
    // Check minimum length (at least 2 characters)
    if (strlen($name) < 2) {
        $errors[] = "$fieldName must be at least 2 characters long.";
    }
    
    // Check maximum length (100 characters)
    if (strlen($name) > 100) {
        $errors[] = "$fieldName must not exceed 100 characters.";
    }
    
    // Check for valid characters: letters, spaces, hyphens, apostrophes, and periods
    if (!preg_match('/^[a-zA-Z][a-zA-Z \-\'.]+$/', $name)) {
        $errors[] = "$fieldName contains invalid characters. Only letters, spaces, hyphens, apostrophes, and periods are allowed.";
    }
    
    // Check for at least one letter (no numbers, symbols only)
    if (!preg_match('/[a-zA-Z]/', $name)) {
        $errors[] = "$fieldName must contain at least one letter.";
    }
    
    // Check for multiple consecutive spaces
    if (preg_match('/\s{2,}/', $name)) {
        $errors[] = "$fieldName must not contain multiple consecutive spaces.";
    }
    
    // Check for leading/trailing spaces
    if ($name !== trim($name)) {
        $errors[] = "$fieldName must not have leading or trailing spaces.";
    }
    
    return $errors;
}

function validateStudentEnrollment($connection, $data) {

    $errors = [];

    // ===============================
    // VALIDATE NAME FIELDS
    // ===============================
    $nameFields = [
        'firstName' => 'First Name',
        'lastName' => 'Last Name',
        'middleName' => 'Middle Name',
        'extensionName' => 'Extension Name',
        'fatherGuardianName' => 'Father/Guardian Name',
        'motherGuardianName' => 'Mother/Guardian Name'
    ];
    
    foreach ($nameFields as $field => $displayName) {
        if (isset($data[$field]) && !empty($data[$field])) {
            $nameErrors = validateName($data[$field], $displayName);
            $errors = array_merge($errors, $nameErrors);
        }
    }

    // ===============================
    // VALIDATE LRN (Numbers Only)
    // ===============================
    if (isset($data['lrn'])) {
        $lrn = $data['lrn'];
        
        // Check if LRN contains only numbers
        if (!preg_match('/^[0-9]+$/', $lrn)) {
            $errors[] = "LRN must contain numbers only.";
        }
        
        // Check exact length (12 digits for Philippine LRN)
        if (strlen($lrn) !== 12) {
            $errors[] = "LRN must be exactly 12 digits.";
        }
    }

    // ===============================
    // CHECK DUPLICATE NAME (Same first, last, middle, extension)
    // ===============================
    if (isset($data['firstName'], $data['lastName'])) {
        $firstName = trim($data['firstName']);
        $lastName = trim($data['lastName']);
        $middleName = isset($data['middleName']) ? trim($data['middleName']) : null;
        $extensionName = isset($data['extensionName']) ? trim($data['extensionName']) : null;
        
        // Check for exact match in student_applications
        $checkDuplicateName = $connection->prepare(
            "SELECT application_id, first_name, last_name 
             FROM student_applications 
             WHERE LOWER(first_name) = LOWER(?) 
               AND LOWER(last_name) = LOWER(?)
               AND (LOWER(middle_name) = LOWER(?) OR (middle_name IS NULL AND ? IS NULL))
               AND (LOWER(extension_name) = LOWER(?) OR (extension_name IS NULL AND ? IS NULL))"
        );
        $checkDuplicateName->bind_param("ssssss", $firstName, $lastName, $middleName, $middleName, $extensionName, $extensionName);
        $checkDuplicateName->execute();
        $checkDuplicateName->store_result();

        if ($checkDuplicateName->num_rows > 0) {
            $errors[] = "A student with the same name (first name, last name, middle name, and extension name) already exists in the system.";
        }
    }
    if (isset($data['contactNumber'])) {
        $contactNumber = preg_replace('/[^0-9]/', '', $data['contactNumber']);
        
        // Check if it's exactly 11 digits and starts with 09
        if (!preg_match('/^09[0-9]{9}$/', $contactNumber)) {
            $errors[] = "Contact number must be an 11-digit Philippine mobile number (e.g., 09123456789).";
        } else {
            // Update data with normalized number
            $data['contactNumber'] = $contactNumber;
        }
    }

    // ===============================
    // VALIDATE GUARDIAN CONTACT NUMBERS
    // ===============================
    if (isset($data['fatherGuardianContact'])) {
        $fatherContact = preg_replace('/[^0-9]/', '', $data['fatherGuardianContact']);
        if (!preg_match('/^09[0-9]{9}$/', $fatherContact)) {
            $errors[] = "Father/Guardian contact must be an 11-digit Philippine mobile number.";
        }
    }

    if (isset($data['motherGuardianContact'])) {
        $motherContact = preg_replace('/[^0-9]/', '', $data['motherGuardianContact']);
        if (!preg_match('/^09[0-9]{9}$/', $motherContact)) {
            $errors[] = "Mother/Guardian contact must be an 11-digit Philippine mobile number.";
        }
    }

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
    // VALIDATE EMAIL FORMAT
    // ===============================
    if (isset($data['email'])) {
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        
        // Check valid email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        } else {
            // Check for valid domain (basic check)
            if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
                $errors[] = "Please enter a valid email address with a proper domain.";
            }
            $data['email'] = $email;
        }
    }

    // ===============================
    // CHECK DUPLICATE EMAIL (across both tables)
    // ===============================
    if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        // Check in student_applications
        $checkStudentEmail = $connection->prepare(
            "SELECT application_id 
             FROM student_applications 
             WHERE email = ?"
        );
        $checkStudentEmail->bind_param("s", $data['email']);
        $checkStudentEmail->execute();
        $checkStudentEmail->store_result();

        // Check in teacher_applications
        $checkTeacherEmail = $connection->prepare(
            "SELECT teacher_application_id 
             FROM teacher_applications 
             WHERE email = ?"
        );
        $checkTeacherEmail->bind_param("s", $data['email']);
        $checkTeacherEmail->execute();
        $checkTeacherEmail->store_result();

        if ($checkStudentEmail->num_rows > 0 || $checkTeacherEmail->num_rows > 0) {
            $errors[] = "Email already exists in the system (used by another student or teacher).";
        }
    }

    // ===============================
    // CHECK DUPLICATE CONTACT NUMBER (across both tables)
    // ===============================
    if (isset($data['contactNumber'])) {
        // Check in student_applications
        $checkStudentContact = $connection->prepare(
            "SELECT application_id 
             FROM student_applications 
             WHERE contact_number = ?"
        );
        $checkStudentContact->bind_param("s", $data['contactNumber']);
        $checkStudentContact->execute();
        $checkStudentContact->store_result();

        // Check in teacher_applications
        $checkTeacherContact = $connection->prepare(
            "SELECT teacher_application_id 
             FROM teacher_applications 
             WHERE contact_number = ?"
        );
        $checkTeacherContact->bind_param("s", $data['contactNumber']);
        $checkTeacherContact->execute();
        $checkTeacherContact->store_result();

        if ($checkStudentContact->num_rows > 0 || $checkTeacherContact->num_rows > 0) {
            $errors[] = "Contact number already exists in the system.";
        }
    }

    return $errors;
}
