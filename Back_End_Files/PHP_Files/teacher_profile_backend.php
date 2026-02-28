<?php
session_start();

include "../../DB_Connection/Connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Website_Files/login.php");
    exit();
}

// Verify this is a teacher
if ($_SESSION['role_id'] != 3) {
    header("Location: ../../Website_Files/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $teacher_id = $_POST['teacher_id'] ?? null;
    $user_id = $_SESSION['user_id'];
    
    // Verify the teacher_id belongs to this user
    $verifyStmt = $connection->prepare("SELECT application_id FROM teachers WHERE teacher_id = ? AND user_id = ?");
    $verifyStmt->bind_param("ii", $teacher_id, $user_id);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows !== 1) {
        header("Location: ../../Website_Files/Teacher_Files/profile_page.php?error=unauthorized");
        exit();
    }
    
    $teacher = $verifyResult->fetch_assoc();
    $application_id = $teacher['application_id'];
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
    $current_school = trim($_POST['current_school'] ?? '');
    $highest_education = $_POST['highest_education'] ?? '';
    $specialization = trim($_POST['specialization'] ?? '');
    
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
    
    if (!in_array($highest_education, ['Bachelors', 'Masters', 'Doctorate', 'Other'])) {
        $errors[] = "Invalid highest education selection";
    }
    
    if (!empty($errors)) {
        header("Location: ../../Website_Files/Teacher_Files/profile_page.php?error=invalid_input");
        exit();
    }
    
    // Handle file uploads
    $uploadDir = "../../uploads/Profile/teacher/";
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Debug: Check what's in FILES array
    error_log("FILES array: " . print_r($_FILES, true));
    
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $documentFields = ['resume_cv', 'prc_id_copy', 'certificates', 'other_documents'];
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
            $newFileName = time() . "_PROFILE_TEACHER_" . $application_id . "." . $fileExt;
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
            $newFileName = $timestamp . "_" . strtoupper(str_replace(' ', '_', $field)) . "_TEACHER_" . $application_id . "." . $fileExt;
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
        UPDATE teacher_applications 
        SET date_of_birth = ?, 
            civil_status = ?, 
            contact_number = ?, 
            email = ?, 
            facebook_profile = ?, 
            house_number_street = ?, 
            barangay = ?, 
            city_municipality = ?, 
            province = ?, 
            current_school = ?, 
            highest_education = ?, 
            specialization = ?
    ";
    
    // Add profile image update if uploaded
    if (!empty($profileImageUpdate)) {
        $updateQuery .= ", " . $profileImageUpdate;
    }
    
    // Add document updates if any
    if (!empty($documentUpdates)) {
        $updateQuery .= ", " . implode(", ", $documentUpdates);
    }
    
    $updateQuery .= " WHERE teacher_application_id = ?";
    
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
        $current_school, 
        $highest_education, 
        $specialization
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
    $types = "ssssssssssss";
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
        header("Location: ../../Website_Files/Teacher_Files/profile_page.php?success=updated");
        exit();
    } else {
        $updateStmt->close();
        header("Location: ../../Website_Files/Teacher_Files/profile_page.php?error=update_failed");
        exit();
    }
}

$connection->close();
?>
