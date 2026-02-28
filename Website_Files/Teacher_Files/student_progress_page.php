<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* Check if Student Progress Page is enabled */
include_once "../../Back_End_Files/PHP_Files/check_activation.php";
if (!isFeatureEnabled('Student Progress Page')) {
    header("Location: ../access_denied.php?feature=Student Progress Page");
    exit;
}

/* VERIFY TEACHER SESSION */
$stmt = $connection->prepare("
    SELECT u.*, ta.profile_image 
    FROM users u
    INNER JOIN teachers s ON s.user_id = u.user_id
    INNER JOIN teacher_applications ta ON s.application_id = ta.teacher_application_id
    WHERE u.user_id = ? AND u.role_id = 3
");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Set profile image path
$profileImagePath = !empty($user['profile_image']) 
    ? "../../uploads/Profile/teacher/" . htmlspecialchars($user['profile_image']) 
    : "../../Assets/profile_button.png";

include "../../Back_End_Files/PHP_Files/student_progress_backend.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/teacher/student_progress_design.css">
    <title>Student Progress - CDONHS-SHS</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <style>
        /* Print Styles */
        @media print {
            .header, .footer, .back-button-container, .btn-print, .no-print,
            .profile-dropdown, .finalize-form, .action-column {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .progress-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
                box-shadow: none !important;
            }
            
            .progress-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            
            .progress-table th, .progress-table td {
                border: 1px solid #000 !important;
                padding: 8px !important;
                text-align: left !important;
            }
            
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
            
            .print-header img {
                width: 80px;
                height: 80px;
            }
            
            .print-header h1 {
                font-size: 18px;
                margin: 5px 0;
            }
            
            .print-header p {
                font-size: 14px;
                margin: 3px 0;
            }
            
            .status-promoted { color: green !important; font-weight: bold; }
            .status-retained { color: red !important; font-weight: bold; }
            .status-graduated { color: blue !important; font-weight: bold; }
            .status-pending { color: orange !important; }
            .status-incomplete { color: gray !important; }
        }
        
        .print-header {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="left">
            <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
            <span>CDONSHS-SHS</span>
        </div>
        <div class="center">
            Student Progress | Advisory: <?php echo htmlspecialchars($advisoryText); ?>
        </div>
        <div class="right">
            <button class="profile-btn" type="button">
                <img src="<?php echo $profileImagePath; ?>">
            </button>
            <div class="profile-dropdown">
                <a href="home.php">Home</a>
                <a href="profile_page.php">View Profile</a>
                <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="back-button-container">
        <a href="home.php" class="back-button">← Back to Home</a>
    </div>

    <!-- Main Content -->
    <div class="progress-container">
        <!-- Print Header (only visible when printing) -->
        <div class="print-header">
            <img src="../../Assets/LOGO.png" alt="School Logo">
            <h1>Cagayan De Oro National High School - Senior High School</h1>
            <p>Student Progress Report</p>
            <p>School Year: <?php echo isset($progressData[0]['school_year']) ? htmlspecialchars($progressData[0]['school_year']) : 'N/A'; ?></p>
            <p>Advisory: <?php echo htmlspecialchars($advisoryText); ?></p>
        </div>

        <!-- Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Table Header -->
        <div class="table-header">
            <h2>Student Progress Report</h2>
            <p class="subtitle">Student Academic Performance and Status</p>
            <?php if (!empty($advisoryText)): ?>
                <p class="advisory-info">Advisory: <?php echo htmlspecialchars($advisoryText); ?></p>
            <?php endif; ?>
        </div>

        <!-- Print Button -->
        <div class="button-row no-print">
            <button onclick="printProgress()" class="btn-print">🖨 Print Progress Report</button>
        </div>

        <?php if (empty($progressData)): ?>
            <div class="no-results">
                <p>No students found in your advisory section.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <form method="POST" id="bulkFinalizeForm">
                <table class="progress-table" id="progressTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>LRN</th>
                            <th>Student Name</th>
                            <th>School Year</th>
                            <th>Overall Average</th>
                            <th>Status</th>
                            <th class="action-column no-print">Action</th>
                            <th class="no-print" style="text-align:center; min-width:60px;">
                                <input type="checkbox" id="selectAll" class="select-all-checkbox" title="Select All">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        foreach ($progressData as $student): 
                            $fullName = $student['last_name'] . ', ' . $student['first_name'];
                            if (!empty($student['middle_name'])) {
                                $fullName .= ' ' . substr($student['middle_name'], 0, 1) . '.';
                            }
                            if (!empty($student['extension_name'])) {
                                $fullName .= ' ' . $student['extension_name'];
                            }
                            
                            $statusClass = '';
                            switch ($student['calculated_status']) {
                                case 'Promoted': $statusClass = 'status-promoted'; break;
                                case 'Retained': $statusClass = 'status-retained'; break;
                                case 'Graduated': $statusClass = 'status-graduated'; break;
                                case 'Pending': $statusClass = 'status-pending'; break;
                                case 'Incomplete': $statusClass = 'status-incomplete'; break;
                            }
                        ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><?php echo htmlspecialchars($student['lrn']); ?></td>
                                <td><?php echo htmlspecialchars($fullName); ?></td>
                                <td><?php echo htmlspecialchars($student['school_year'] ?? 'N/A'); ?></td>
                                <td class="grade-cell overall-grade">
                                    <?php echo $student['overall_avg'] !== null ? number_format($student['overall_avg'], 2) : '-'; ?>
                                </td>
                                <td class="<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($student['calculated_status']); ?>
                                </td>
                                <td class="action-column no-print">
                                    <?php if ($student['is_finalized']): ?>
                                        <span class="finalized-badge">Finalized</span>
                                    <?php else: ?>
                                        <span class="ready-badge">Ready to Finalize</span>
                                    <?php endif; ?>
                                </td>
                                <td class="no-print">
                                    <input type="checkbox" name="selected_students[]"
                                           value="<?php echo $student['student_id']; ?>"
                                           data-status="<?php echo $student['calculated_status']; ?>"
                                           data-name="<?php echo htmlspecialchars($fullName); ?>"
                                           class="student-checkbox">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="bulk_finalize" value="1">
                
                <!-- Finalize Button at Bottom -->
                <div class="finalize-button-container no-print">
                    <button type="button" id="finalizeSelectedBtn" class="btn-finalize-selected">
                        ✓ Finalize Selected Students
                    </button>
                </div>
                </form>
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
        // Print function
        function printProgress() {
            window.print();
        }
        
        // Select All checkbox functionality
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Add event listeners to all student checkboxes
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Update select all checkbox state
                const allCheckboxes = document.querySelectorAll('.student-checkbox');
                const allChecked = document.querySelectorAll('.student-checkbox:checked').length === allCheckboxes.length;
                document.getElementById('selectAll').checked = allChecked;
            });
        });
        
        // Finalize selected students
        document.getElementById('finalizeSelectedBtn')?.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                alert('Please select at least one student to finalize.');
                return;
            }
            
            // Build confirmation message
            let studentList = [];
            let promotedCount = 0;
            let retainedCount = 0;
            let graduatedCount = 0;
            
            checkedBoxes.forEach(checkbox => {
                const name = checkbox.getAttribute('data-name');
                const status = checkbox.getAttribute('data-status');
                studentList.push(`${name} (${status})`);
                
                if (status === 'Promoted') promotedCount++;
                else if (status === 'Retained') retainedCount++;
                else if (status === 'Graduated') graduatedCount++;
            });
            
            let message = `Are you sure you want to finalize the following students?\n\n`;
            message += `Promoted: ${promotedCount}\n`;
            message += `Retained: ${retainedCount}\n`;
            message += `Graduated: ${graduatedCount}\n\n`;
            message += `Students:\n${studentList.join('\n')}\n\n`;
            message += `This action cannot be undone.`;
            
            if (confirm(message)) {
                document.getElementById('bulkFinalizeForm').submit();
            }
        });
    </script>
</body>
</html>
