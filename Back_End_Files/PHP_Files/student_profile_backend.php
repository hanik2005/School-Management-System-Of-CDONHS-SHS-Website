<?php
session_start();

include "../../DB_Connection/Connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Website_Files/login.php");
    exit();
}

// Verify this is a student
if ($_SESSION['role_id'] != 1) {
    header("Location: ../../Website_Files/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $student_id = $_POST['student_id'] ?? null;
    $user_id = $_SESSION['user_id'];
    
    // Verify the student_id belongs to this user
    $verifyStmt = $connection->prepare("SELECT application_id FROM students WHERE student_id = ? AND user_id = ?");
    $verifyStmt->bind_param("ii", $student_id, $user_id);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows !== 1) {
        header("Location: ../../Website_Files/Student_Files/profile_page.php?error=unauthorized");
        exit();
    }
    
    $student = $verifyResult->fetch_assoc();
    $application_id = $student['application_id'];
    $verifyStmt->close();
    
    // Sanitize and validate input
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $civil_status = $_POST['civil_status'] ?? '';
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $facebook_profile = trim($_POST['facebook_profile'] ?? '');
    $house_number_street = trim($_POST['house_number_street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city_municipality = trim($_POST['city_municipality'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $father_guardian_name = trim($_POST['father_guardian_name'] ?? '');
    $father_guardian_contact = trim($_POST['father_guardian_contact'] ?? '');
    $mother_guardian_name = trim($_POST['mother_guardian_name'] ?? '');
    $mother_guardian_contact = trim($_POST['mother_guardian_contact'] ?? '');
    
    // Validation
    $errors = [];
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($contact_number) && !preg_match('/^[0-9+\-\s]+$/', $contact_number)) {
        $errors[] = "Invalid contact number format";
    }
    
    if (!in_array($civil_status, ['single', 'married', 'divorced', 'widowed'])) {
        $errors[] = "Invalid civil status selection";
    }
    
    if (!empty($errors)) {
        header("Location: ../../Website_Files/Student_Files/profile_page.php?error=invalid_input");
        exit();
    }
    
    // Handle file uploads
    $uploadDir = "../../uploads/Profile/student/";
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Debug: Check what's in FILES array
    error_log("FILES array: " . print_r($_FILES, true));
    
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $documentFields = ['psa_birth_certificate', 'form_138', 'student_id_copy'];
    $documentUpdates = [];
    $documentValues = [];
    $documentTypes = "";
    $profileImageUpdate = "";
    $profileImageValue = null;
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate image type
        if (in_array($fileExt, $imageTypes)) {
            // Generate unique filename
            $newFileName = time() . "_PROFILE_" . $application_id . "." . $fileExt;
            $destination = $uploadDir . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $profileImageUpdate = "profile_image = ?";
                $profileImageValue = $newFileName;
                error_log("Profile image uploaded successfully: " . $newFileName);
            } else {
                error_log("Failed to move profile image. Temp: " . $file['tmp_name'] . ", Dest: " . $destination);
            }
        } else {
            error_log("Invalid image type: " . $fileExt);
        }
    } else {
        // Debug: Log why profile image wasn't processed
        if (isset($_FILES['profile_image'])) {
            error_log("Profile image upload error code: " . $_FILES['profile_image']['error']);
        } else {
            error_log("No profile image in FILES array");
        }
    }
    
    foreach ($documentFields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$field];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileExt, $allowedTypes)) {
                continue; // Skip invalid file types
            }
            
            // Generate unique filename
            $timestamp = time();
            $newFileName = $timestamp . "_" . strtoupper(str_replace(' ', '_', $field)) . "_" . $application_id . "." . $fileExt;
            $destination = $uploadDir . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $documentUpdates[] = "$field = ?";
                $documentValues[] = $newFileName;
                $documentTypes .= "s";
            }
        }
    }
    
    // Build the update query
    $updateQuery = "
        UPDATE student_applications 
        SET date_of_birth = ?, 
            civil_status = ?, 
            contact_number = ?, 
            email = ?, 
            facebook_profile = ?, 
            house_number_street = ?, 
            barangay = ?, 
            city_municipality = ?, 
            province = ?, 
            father_guardian_name = ?, 
            father_guardian_contact = ?, 
            mother_guardian_name = ?, 
            mother_guardian_contact = ?
    ";
    
    // Add profile image update if uploaded
    if (!empty($profileImageUpdate)) {
        $updateQuery .= ", " . $profileImageUpdate;
    }
    
    // Add document updates if any
    if (!empty($documentUpdates)) {
        $updateQuery .= ", " . implode(", ", $documentUpdates);
    }
    
    $updateQuery .= " WHERE application_id = ?";
    
    $updateStmt = $connection->prepare($updateQuery);
    
    // Build parameters array
    $params = [
        $date_of_birth, 
        $civil_status, 
        $contact_number, 
        $email, 
        $facebook_profile, 
        $house_number_street, 
        $barangay, 
        $city_municipality, 
        $province, 
        $father_guardian_name, 
        $father_guardian_contact, 
        $mother_guardian_name, 
        $mother_guardian_contact
    ];
    
    // Add profile image value if uploaded
    if (!empty($profileImageValue)) {
        $params[] = $profileImageValue;
    }
    
    // Add document values
    foreach ($documentValues as $docValue) {
        $params[] = $docValue;
    }
    
    // Add application_id
    $params[] = $application_id;
    
    // Build types string
    $types = "sssssssssssss";
    if (!empty($profileImageValue)) {
        $types .= "s";
    }
    $types .= $documentTypes . "i";
    
    // Debug: Log the query and parameters
    error_log("Update Query: " . $updateQuery);
    error_log("Types: " . $types);
    error_log("Profile Image Value: " . ($profileImageValue ?? "NULL"));
    error_log("Application ID: " . $application_id);
    
    // Bind parameters dynamically
    $updateStmt->bind_param($types, ...$params);
    
    if ($updateStmt->execute()) {
        $updateStmt->close();
        header("Location: ../../Website_Files/Student_Files/profile_page.php?success=updated");
        exit();
    } else {
        $updateStmt->close();
        header("Location: ../../Website_Files/Student_Files/profile_page.php?error=update_failed");
        exit();
    }
}

$connection->close();
?>
