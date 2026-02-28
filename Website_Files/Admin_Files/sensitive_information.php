<?php
include "../../Back_End_Files/PHP_Files/sensitive_information_backend.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensitive Information - CDONHS-SHS Admin</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/admin/application_list_design.css">
    <link rel="stylesheet" href="../../Design/admin/sensitive_information_design.css">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="left">
            <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
            <span>CDONHS-SHS</span>
        </div>
        <div class="center">
            Admin - Sensitive Information
        </div>
        <div class="right">
            <button class="profile-btn" type="button">
                <img src="../../Assets/admin_profile.png">
            </button>
            <div class="profile-dropdown">
                <a href="application_page.php">Dashboard</a>
                <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <!-- Page Title -->
    <div class="page-title">
        <h1>Sensitive Information</h1>
    </div>

    <!-- Navigation -->
    <div class="nav-links">
        <a href="home.php">← Back to Dashboard</a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search_name">Search by Name:</label>
                    <input type="text" id="search_name" name="search_name" 
                           placeholder="Enter name..." 
                           value="<?= htmlspecialchars($search_name); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="filter_user_type">User Type:</label>
                    <select id="filter_user_type" name="filter_user_type">
                        <option value="">All Users</option>
                        <option value="Student" <?= $filter_user_type == 'Student' ? 'selected' : ''; ?>>Student</option>
                        <option value="Teacher" <?= $filter_user_type == 'Teacher' ? 'selected' : ''; ?>>Teacher</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_status">Enrollment Status:</label>
                    <select id="filter_status" name="filter_status">
                        <option value="">All Status</option>
                        <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?= $filter_status == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?= $filter_status == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="Conditionally Approved" <?= $filter_status == 'Conditionally Approved' ? 'selected' : ''; ?>>Conditionally Approved</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-filter">🔍 Search</button>
                    <a href="sensitive_information.php" class="btn btn-reset">↻ Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="sensitive-info-container">
        <?php if (!empty($allResults)): ?>
            <?php foreach ($allResults as $record): ?>
                <div class="detail-card">
                    <button class="expand-btn" onclick="toggleDetails(this)">
                        <span>
                            <span class="user-type-badge <?= $record['user_type'] === 'Student' ? 'badge-student' : 'badge-teacher'; ?>">
                                <?= htmlspecialchars($record['user_type']); ?>
                            </span>
                            <?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                            (<?= htmlspecialchars($record['email']); ?>)
                        </span>
                        <span class="expand-icon">▼</span>
                    </button>
                    
                    <div class="detail-content">
                        <!-- Personal Information -->
                        <div class="detail-section">
                            <h3>📋 Personal Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">First Name</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['first_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Last Name</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['last_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Middle Name</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['middle_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Extension Name</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['extension_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date of Birth</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['date_of_birth'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Sex</span>
                                    <span class="detail-value"><?= htmlspecialchars(ucfirst($record['sex'] ?? 'N/A')); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Civil Status</span>
                                    <span class="detail-value"><?= htmlspecialchars(ucfirst($record['civil_status'] ?? 'N/A')); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="detail-section">
                            <h3>🏠 Address Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">House Number & Street</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['house_number_street'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Barangay</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['barangay'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">City/Municipality</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['city_municipality'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Province</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['province'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="detail-section">
                            <h3>📞 Contact Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Contact Number</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['contact_number'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['email'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Facebook Profile</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['facebook_profile'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Education Information -->
                        <div class="detail-section">
                            <h3>🎓 Education Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Current School</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['current_school'] ?? 'N/A'); ?></span>
                                </div>
                                <?php if ($record['user_type'] === 'Student'): ?>
                                <div class="detail-item">
                                    <span class="detail-label">School Classification</span>
                                    <span class="detail-value"><?= htmlspecialchars(ucfirst($record['school_classification'] ?? 'N/A')); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Enrollment Type</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['enrollment_type'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Year Graduated</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['year_graduated'] ?? 'N/A'); ?></span>
                                </div>
                                <?php else: ?>
                                <div class="detail-item">
                                    <span class="detail-label">Highest Education</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['highest_education'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Specialization</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['specialization'] ?? 'N/A'); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Guardian Information (Student Only) -->
                        <?php if ($record['user_type'] === 'Student'): ?>
                        <div class="detail-section">
                            <h3>👨‍👩‍👧 Guardian Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Father/Guardian Name</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['father_guardian_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Father/Guardian Contact</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['father_guardian_contact'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Mother/Guardian Name</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['mother_guardian_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Mother/Guardian Contact</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['mother_guardian_contact'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Documents -->
                        <div class="detail-section">
                            <h3>📁 Documents</h3>
                            <?php if ($record['user_type'] === 'Student'): ?>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">PSA Birth Certificate</span>
                                        <?php if (!empty($record['psa_birth_certificate'])): ?>
                                            <a href="../../uploads/<?= htmlspecialchars($record['psa_birth_certificate']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Form 138</span>
                                        <?php if (!empty($record['form_138'])): ?>
                                            <a href="../../uploads/Documents/student/<?= htmlspecialchars($record['form_138']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Student ID Copy</span>
                                        <?php if (!empty($record['student_id_copy'])): ?>
                                            <a href="../../uploads/Documents/student/<?= htmlspecialchars($record['student_id_copy']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Profile Image</span>
                                        <?php if (!empty($record['profile_image'])): ?>
                                            <a href="../../uploads/Documents/student/<?= htmlspecialchars($record['profile_image']); ?>" target="_blank" class="doc-link">✓ View Image</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">Resume/CV</span>
                                        <?php if (!empty($record['resume_cv'])): ?>
                                            <a href="../../uploads/Documents/teacher/<?= htmlspecialchars($record['resume_cv']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">PRC ID Copy</span>
                                        <?php if (!empty($record['prc_id_copy'])): ?>
                                            <a href="../../uploads/Documents/teacher/<?= htmlspecialchars($record['prc_id_copy']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Certificates</span>
                                        <?php if (!empty($record['certificates'])): ?>
                                            <a href="../../uploads/Documents/teacher/<?= htmlspecialchars($record['certificates']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Other Documents</span>
                                        <?php if (!empty($record['other_documents'])): ?>
                                            <a href="../../uploads/Documents/teacher/<?= htmlspecialchars($record['other_documents']); ?>" target="_blank" class="doc-link">✓ View Document</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Profile Image</span>
                                        <?php if (!empty($record['profile_image'])): ?>
                                            <a href="../../uploads/Documents/teacher/<?= htmlspecialchars($record['profile_image']); ?>" target="_blank" class="doc-link">✓ View Image</a>
                                        <?php else: ?>
                                            <span class="doc-missing">Not Submitted</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Application Status -->
                        <div class="detail-section">
                            <h3>📊 Application Status</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <?php 
                                    $statusClass = 'status-pending';
                                    if ($record['application_status'] == 'Approved') $statusClass = 'status-approved';
                                    if ($record['application_status'] == 'Rejected') $statusClass = 'status-rejected';
                                    if ($record['application_status'] == 'Conditionally Approved') $statusClass = 'status-conditionally';
                                    ?>
                                    <span class="status-badge <?= $statusClass; ?>">
                                        <?= htmlspecialchars($record['application_status']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date Submitted</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['date_submitted']); ?></span>
                                </div>
                                <div class="detail-item" style="grid-column: span 2;">
                                    <span class="detail-label">Admin Remarks</span>
                                    <span class="detail-value"><?= htmlspecialchars($record['remarks'] ?? 'No remarks'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="detail-card">
                <p style="text-align: center; padding: 40px; color: #666;">
                    No records found matching your criteria.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2026 Cagayan De Oro National High School - Senior High School  
        <br>
        School Management System
    </div>

    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
    <script>
        function toggleDetails(btn) {
            const content = btn.nextElementSibling;
            const icon = btn.querySelector('.expand-icon');
            
            content.classList.toggle('show');
            icon.classList.toggle('rotated');
        }
    </script>
</body>
</html>
