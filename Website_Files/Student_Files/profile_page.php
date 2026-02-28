<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

// Verify student session and get profile data
$stmt = $connection->prepare("
    SELECT s.student_id, s.student_number, s.enrollment_status, s.date_enrolled, s.enlistment_status,
           sa.first_name, sa.last_name, sa.middle_name, sa.extension_name,
           sa.lrn, sa.date_of_birth, sa.sex, sa.civil_status,
           sa.house_number_street, sa.barangay, sa.city_municipality, sa.province,
           sa.contact_number, sa.email, sa.facebook_profile,
           sa.current_school, sa.school_classification, sa.enrollment_type, sa.year_graduated,
           sa.father_guardian_name, sa.father_guardian_contact,
           sa.mother_guardian_name, sa.mother_guardian_contact,
           sa.psa_birth_certificate, sa.form_138, sa.student_id_copy,
           sa.profile_image,
           u.username, u.status
    FROM students s
    INNER JOIN users u ON s.user_id = u.user_id
    INNER JOIN student_applications sa ON s.application_id = sa.application_id
    WHERE s.user_id = ?
");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if (!$profile) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Get strand and section info
$strandInfo = null;
$stmt = $connection->prepare("
    SELECT st.strand_name, sec.section_name, ss.grade_level
    FROM student_strand ss
    INNER JOIN strands st ON ss.strand_id = st.strand_id
    INNER JOIN section sec ON ss.section_id = sec.section_id
    WHERE ss.student_id = ?
");
$stmt->bind_param("i", $profile['student_id']);
$stmt->execute();
$strandResult = $stmt->get_result();
if ($strandResult->num_rows > 0) {
    $strandInfo = $strandResult->fetch_assoc();
}

// Handle success/error messages
$message = "";
$messageType = "";
if (isset($_GET['success'])) {
    $messageType = "success";
    $message = "Profile updated successfully!";
}
if (isset($_GET['error'])) {
    $messageType = "error";
    switch ($_GET['error']) {
        case 'update_failed':
            $message = "Failed to update profile. Please try again.";
            break;
        case 'invalid_input':
            $message = "Invalid input detected.";
            break;
        default:
            $message = "An error occurred.";
    }
}

// Format full name
$fullName = $profile['first_name'];
if (!empty($profile['middle_name'])) {
    $fullName .= " " . substr($profile['middle_name'], 0, 1) . ".";
}
$fullName .= " " . $profile['last_name'];
if (!empty($profile['extension_name'])) {
    $fullName .= " " . $profile['extension_name'];
}

// Format address
$address = $profile['house_number_street'] . ", " . $profile['barangay'] . ", " . 
           $profile['city_municipality'] . ", " . $profile['province'];

// Set profile image path for header
$profileImagePath = !empty($profile['profile_image']) 
    ? "../../uploads/Profile/student/" . htmlspecialchars($profile['profile_image']) 
    : "../../Assets/profile_button.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student | CDONHS-SHS</title>
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/profile_page_design.css">
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>

    <?php include "../../Back_End_Files/PHP_Files/get_student_program.php"; ?>
    <div class="center">
         Program:
        <?php if ($isEnlisted): ?>
        <?php echo htmlspecialchars($gradeLevel); ?>, 
        <?php echo htmlspecialchars($strandName); ?>, 
        <?php echo htmlspecialchars($sectionName); ?>

        <?php elseif($isPending):?>
            Pending Enlistment
        <?php elseif($isRejected):?>
            Rejected Enlistment
        <?php elseif($Promoted):?>
            Promoted
        <?php else: ?>
            Not enrolled yet
        <?php endif; ?>
    </div>
    <div class="right">
        <button class="profile-btn" type="button">
            <img src="<?php echo $profileImagePath; ?>">
        </button>
        <div class="profile-dropdown">
            <a href="home.php">Home</a>
            <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
        </div>
    </div>
</div>

<!-- Profile Content -->
<div class="profile-container">
    <div class="profile-box">
        
        <?php if (!empty($message)): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <form action="../../Back_End_Files/PHP_Files/student_profile_backend.php" method="POST" id="profileForm" enctype="multipart/form-data">
            <input type="hidden" name="student_id" value="<?php echo $profile['student_id']; ?>">
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image-container">
                    <?php 
                    $profileImagePath = !empty($profile['profile_image']) 
                        ? "../../uploads/Profile/student/" . htmlspecialchars($profile['profile_image']) 
                        : "../../Assets/default.png"; 
                    ?>
                    <img src="<?php echo $profileImagePath; ?>" alt="Profile Image" class="profile-image" id="profileImagePreview">
                    <label for="profile_image" class="profile-image-upload" title="Click to change profile image">
                        <span>📷</span>
                    </label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="profile-image-input" onchange="previewImage(this)">
                </div>
                <div class="profile-header-info">
                    <h2><?php echo htmlspecialchars($fullName); ?></h2>
                    <p><strong>LRN:</strong> <?php echo htmlspecialchars($profile['lrn']); ?></p>
                    <p><strong>Student Number:</strong> <?php echo htmlspecialchars($profile['student_number']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
                    <span class="profile-status status-<?php echo strtolower($profile['enrollment_status']); ?>">
                        <?php echo htmlspecialchars($profile['enrollment_status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="profile-content view-mode" id="profileContent">
                
                <!-- Personal Information -->
                <div class="profile-section">
                    <h3>📋 Personal Information</h3>
                    
                    <div class="profile-field">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($profile['first_name']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($profile['last_name']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Extension Name</label>
                        <input type="text" name="extension_name" value="<?php echo htmlspecialchars($profile['extension_name'] ?? ''); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?php echo $profile['date_of_birth']; ?>">
                    </div>
                    
                    <div class="profile-field">
                        <label>Sex</label>
                        <input type="text" value="<?php echo ucfirst($profile['sex']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Civil Status</label>
                        <select name="civil_status">
                            <option value="single" <?php echo $profile['civil_status'] == 'single' ? 'selected' : ''; ?>>Single</option>
                            <option value="married" <?php echo $profile['civil_status'] == 'married' ? 'selected' : ''; ?>>Married</option>
                            <option value="divorced" <?php echo $profile['civil_status'] == 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                            <option value="widowed" <?php echo $profile['civil_status'] == 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="profile-section">
                    <h3>📞 Contact Information</h3>
                    
                    <div class="profile-field">
                        <label>Contact Number</label>
                        <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($profile['contact_number']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Facebook Profile</label>
                        <input type="text" name="facebook_profile" value="<?php echo htmlspecialchars($profile['facebook_profile'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Address -->
                <div class="profile-section full-width">
                    <h3>🏠 Address</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="profile-field">
                            <label>House No. / Street</label>
                            <input type="text" name="house_number_street" value="<?php echo htmlspecialchars($profile['house_number_street']); ?>" required>
                        </div>
                        
                        <div class="profile-field">
                            <label>Barangay</label>
                            <input type="text" name="barangay" value="<?php echo htmlspecialchars($profile['barangay']); ?>" required>
                        </div>
                        
                        <div class="profile-field">
                            <label>City / Municipality</label>
                            <input type="text" name="city_municipality" value="<?php echo htmlspecialchars($profile['city_municipality']); ?>" required>
                        </div>
                        
                        <div class="profile-field">
                            <label>Province</label>
                            <input type="text" name="province" value="<?php echo htmlspecialchars($profile['province']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Academic Information (Read-only) -->
                <div class="profile-section">
                    <h3>🎓 Academic Information</h3>
                    
                    <div class="profile-field">
                        <label>Enrollment Type</label>
                        <input type="text" value="<?php echo htmlspecialchars($profile['enrollment_type']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Previous School</label>
                        <input type="text" value="<?php echo htmlspecialchars($profile['current_school']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Year Graduated</label>
                        <input type="text" value="<?php echo htmlspecialchars($profile['year_graduated']); ?>" disabled>
                    </div>
                    
                    <?php if ($strandInfo): ?>
                    <div class="profile-field">
                        <label>Grade Level</label>
                        <input type="text" value="<?php echo htmlspecialchars($strandInfo['grade_level']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Strand</label>
                        <input type="text" value="<?php echo htmlspecialchars($strandInfo['strand_name']); ?>" disabled>
                    </div>
                    
                    <div class="profile-field">
                        <label>Section</label>
                        <input type="text" value="<?php echo htmlspecialchars($strandInfo['section_name']); ?>" disabled>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Guardian Information -->
                <div class="profile-section">
                    <h3>👨‍👩‍👧 Guardian Information</h3>
                    
                    <div class="profile-field">
                        <label>Father/Guardian Name</label>
                        <input type="text" name="father_guardian_name" value="<?php echo htmlspecialchars($profile['father_guardian_name']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Father/Guardian Contact</label>
                        <input type="tel" name="father_guardian_contact" value="<?php echo htmlspecialchars($profile['father_guardian_contact']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Mother/Guardian Name</label>
                        <input type="text" name="mother_guardian_name" value="<?php echo htmlspecialchars($profile['mother_guardian_name']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Mother/Guardian Contact</label>
                        <input type="tel" name="mother_guardian_contact" value="<?php echo htmlspecialchars($profile['mother_guardian_contact']); ?>" required>
                    </div>
                </div>

                <!-- Documents -->
                <div class="profile-section full-width">
                    <h3>📄 Uploaded Documents</h3>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                        Upload your documents if not yet submitted. Click on a document to view/download.
                    </p>
                    <div class="documents-grid">
                        
                        <!-- PSA Birth Certificate -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>PSA Birth Certificate</strong>
                                <?php if (!empty($profile['psa_birth_certificate'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/student/<?php echo htmlspecialchars($profile['psa_birth_certificate']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="psa_birth_certificate" accept=".pdf,.jpg,.jpeg,.png" class="document-upload">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Form 138 -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>Form 138 (Report Card)</strong>
                                <?php if (!empty($profile['form_138'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/student/<?php echo htmlspecialchars($profile['form_138']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="form_138" accept=".pdf,.jpg,.jpeg,.png" class="document-upload">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Student ID Copy -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>Student ID Copy</strong>
                                <?php if (!empty($profile['student_id_copy'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/student/<?php echo htmlspecialchars($profile['student_id_copy']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="student_id_copy" accept=".pdf,.jpg,.jpeg,.png" class="document-upload">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>

            <!-- Buttons -->
            <div class="profile-buttons">
                <button type="button" class="btn btn-edit" id="editBtn" onclick="toggleEdit()">Edit Profile</button>
                <button type="submit" class="btn btn-save" id="saveBtn" style="display: none;">Save Changes</button>
                <button type="button" class="btn btn-cancel" id="cancelBtn" style="display: none;" onclick="cancelEdit()">Cancel</button>
                <a href="home.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </form>

    </div>
</div>

<!-- Footer -->
<div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Management System
</div>

<script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
<script>
    let isEditing = false;

    function toggleEdit() {
        isEditing = true;
        document.getElementById('profileContent').classList.remove('view-mode');
        document.getElementById('profileContent').classList.add('edit-mode');
        document.getElementById('editBtn').style.display = 'none';
        document.getElementById('saveBtn').style.display = 'inline-block';
        document.getElementById('cancelBtn').style.display = 'inline-block';
        
        // Enable all inputs except disabled ones
        const inputs = document.querySelectorAll('#profileContent input, #profileContent select');
        inputs.forEach(input => {
            if (!input.hasAttribute('disabled')) {
                input.removeAttribute('readonly');
            }
        });
    }

    function cancelEdit() {
        isEditing = false;
        document.getElementById('profileContent').classList.add('view-mode');
        document.getElementById('profileContent').classList.remove('edit-mode');
        document.getElementById('editBtn').style.display = 'inline-block';
        document.getElementById('saveBtn').style.display = 'none';
        document.getElementById('cancelBtn').style.display = 'none';
        
        // Reset form
        document.getElementById('profileForm').reset();
        
        // Disable inputs
        const inputs = document.querySelectorAll('#profileContent input, #profileContent select');
        inputs.forEach(input => {
            if (!input.hasAttribute('disabled')) {
                input.setAttribute('readonly', true);
            }
        });
    }

    // Initialize view mode
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('#profileContent input, #profileContent select');
        inputs.forEach(input => {
            if (!input.hasAttribute('disabled')) {
                input.setAttribute('readonly', true);
            }
        });
    });

    // Preview profile image before upload
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImagePreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
            
            // Auto-enable save button when profile image is selected
            if (!isEditing) {
                toggleEdit();
            }
        }
    }
</script>

</body>
</html>
