<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

// Verify teacher session and get profile data
$stmt = $connection->prepare("
    SELECT t.teacher_id, t.teacher_number, t.employment_status, t.date_hired,
           ta.first_name, ta.last_name, ta.middle_name, ta.extension_name,
           ta.date_of_birth, ta.sex, ta.civil_status,
           ta.contact_number, ta.email, ta.facebook_profile,
           ta.house_number_street, ta.barangay, ta.city_municipality, ta.province,
           ta.current_school, ta.highest_education, ta.specialization,
           ta.resume_cv, ta.prc_id_copy, ta.certificates, ta.other_documents,
           ta.profile_image,
           u.username, u.status
    FROM teachers t
    INNER JOIN users u ON t.user_id = u.user_id
    INNER JOIN teacher_applications ta ON t.application_id = ta.teacher_application_id
    WHERE t.user_id = ?
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

// Get advisory info
$advisoryInfo = null;
$stmt = $connection->prepare("
    SELECT st.strand_name, sec.section_name, ta.grade_level
    FROM teacher_advisory ta
    INNER JOIN strands st ON ta.strand_id = st.strand_id
    INNER JOIN section sec ON ta.section_id = sec.section_id
    WHERE ta.teacher_id = ?
");
$stmt->bind_param("i", $profile['teacher_id']);
$stmt->execute();
$advisoryResult = $stmt->get_result();
if ($advisoryResult->num_rows > 0) {
    $advisoryInfo = $advisoryResult->fetch_assoc();
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
        case 'unauthorized':
            $message = "Unauthorized access.";
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
    ? "../../uploads/Profile/teacher/" . htmlspecialchars($profile['profile_image']) 
    : "../../Assets/profile_button.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Teacher | CDONHS-SHS</title>
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
    
    <?php include "../../Back_End_Files/PHP_Files/get_teacher_advisory.php"; ?>
    <div class="center">
        Advisory: <?php echo htmlspecialchars($advisoryText); ?>
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
        <form action="../../Back_End_Files/PHP_Files/teacher_profile_backend.php" method="POST" id="profileForm" enctype="multipart/form-data">
            <input type="hidden" name="teacher_id" value="<?php echo $profile['teacher_id']; ?>">
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image-container">
                    <?php 
                    $profileImagePath = !empty($profile['profile_image']) 
                        ? "../../uploads/Profile/teacher/" . htmlspecialchars($profile['profile_image']) 
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
                    <p><strong>School ID:</strong> <?php echo htmlspecialchars($profile['teacher_number']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($profile['specialization']); ?></p>
                    <span class="profile-status status-<?php echo strtolower($profile['employment_status']); ?>">
                        <?php echo htmlspecialchars($profile['employment_status']); ?>
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

                <!-- Professional Information -->
                <div class="profile-section">
                    <h3>🎓 Professional Information</h3>
                    
                    <div class="profile-field">
                        <label>Highest Education</label>
                        <select name="highest_education">
                            <option value="Bachelors" <?php echo $profile['highest_education'] == 'Bachelors' ? 'selected' : ''; ?>>Bachelors</option>
                            <option value="Masters" <?php echo $profile['highest_education'] == 'Masters' ? 'selected' : ''; ?>>Masters</option>
                            <option value="Doctorate" <?php echo $profile['highest_education'] == 'Doctorate' ? 'selected' : ''; ?>>Doctorate</option>
                            <option value="Other" <?php echo $profile['highest_education'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="profile-field">
                        <label>Specialization</label>
                        <input type="text" name="specialization" value="<?php echo htmlspecialchars($profile['specialization']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Current School</label>
                        <input type="text" name="current_school" value="<?php echo htmlspecialchars($profile['current_school']); ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label>Date Hired</label>
                        <input type="text" value="<?php echo date('F d, Y', strtotime($profile['date_hired'])); ?>" disabled>
                    </div>
                </div>

                <!-- Advisory Information (Read-only) -->
                <div class="profile-section">
                    <h3>📚 Advisory Information</h3>
                    
                    <?php if ($advisoryInfo): ?>
                        <div class="profile-field">
                            <label>Grade Level Advisory</label>
                            <input type="text" value="<?php echo htmlspecialchars($advisoryInfo['grade_level']); ?>" disabled>
                        </div>
                        
                        <div class="profile-field">
                            <label>Strand Advisory</label>
                            <input type="text" value="<?php echo htmlspecialchars($advisoryInfo['strand_name']); ?>" disabled>
                        </div>
                        
                        <div class="profile-field">
                            <label>Section Advisory</label>
                            <input type="text" value="<?php echo htmlspecialchars($advisoryInfo['section_name']); ?>" disabled>
                        </div>
                    <?php else: ?>
                        <div class="profile-field">
                            <label>Status</label>
                            <input type="text" value="No advisory assigned yet" disabled>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Documents -->
                <div class="profile-section full-width">
                    <h3>📄 Uploaded Documents</h3>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                        Upload your documents if not yet submitted. Click on a document to view/download.
                    </p>
                    <div class="documents-grid">
                        
                        <!-- Resume/CV -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>Resume/CV</strong>
                                <?php if (!empty($profile['resume_cv'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/teacher/<?php echo htmlspecialchars($profile['resume_cv']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="resume_cv" accept=".pdf,.doc,.docx" class="document-upload">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- PRC ID Copy -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>PRC ID Copy</strong>
                                <?php if (!empty($profile['prc_id_copy'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/teacher/<?php echo htmlspecialchars($profile['prc_id_copy']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="prc_id_copy" accept=".pdf,.jpg,.jpeg,.png" class="document-upload">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Certificates -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>Certificates</strong>
                                <?php if (!empty($profile['certificates'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/teacher/<?php echo htmlspecialchars($profile['certificates']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="certificates" accept=".pdf,.jpg,.jpeg,.png" class="document-upload">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Other Documents -->
                        <div class="document-item">
                            <div class="document-icon">
                                <img src="../../Assets/pdf.png" alt="PDF">
                            </div>
                            <div class="document-info">
                                <strong>Other Documents</strong>
                                <?php if (!empty($profile['other_documents'])): ?>
                                    <span class="document-status uploaded">✓ Uploaded</span>
                                    <a href="../../uploads/Documents/teacher/<?php echo htmlspecialchars($profile['other_documents']); ?>" target="_blank" class="btn-view">View</a>
                                <?php else: ?>
                                    <span class="document-status not-uploaded">✗ Not Uploaded</span>
                                    <input type="file" name="other_documents" accept=".pdf,.jpg,.jpeg,.png" class="document-upload">
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
