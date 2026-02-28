<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* Verify student session and get profile image */
$stmt = $connection->prepare("
    SELECT u.*, sa.profile_image 
    FROM users u
    INNER JOIN students s ON s.user_id = u.user_id
    INNER JOIN student_applications sa ON s.application_id = sa.application_id
    WHERE u.user_id = ? AND u.role_id = 1
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
    ? "../../uploads/Profile/student/" . htmlspecialchars($user['profile_image']) 
    : "../../Assets/profile_button.png";

// Get student info
$student_id = null;
$gradeLevel = null;
$strandId = null;
$strandName = null;
$sectionId = null;
$sectionName = null;
$isPromoted = false;
$student_school_year = null;

$stmtStudent = $connection->prepare("
    SELECT student_id, enlistment_status, school_year 
    FROM students 
    WHERE user_id = ?
");
$stmtStudent->bind_param("i", $_SESSION['user_id']);
$stmtStudent->execute();
$resStudent = $stmtStudent->get_result();
$studentRow = $resStudent->fetch_assoc();
$stmtStudent->close();

if ($studentRow) {
    $student_id = $studentRow['student_id'];
    $isPromoted = ($studentRow['enlistment_status'] === 'Promoted');
    $student_school_year = $studentRow['school_year'];
    
    // Get program info
    $stmtProgram = $connection->prepare("
        SELECT ss.grade_level, ss.strand_id, ss.section_id, s.strand_name, sec.section_name
        FROM student_strand ss
        LEFT JOIN strands s ON ss.strand_id = s.strand_id
        LEFT JOIN section sec ON ss.section_id = sec.section_id
        WHERE ss.student_id = ?
        ORDER BY ss.grade_level DESC
        LIMIT 1
    ");
    $stmtProgram->bind_param("i", $student_id);
    $stmtProgram->execute();
    $resProgram = $stmtProgram->get_result();
    if ($row = $resProgram->fetch_assoc()) {
        $gradeLevel = $row['grade_level'];
        $strandId = $row['strand_id'];
        $strandName = $row['strand_name'];
        $sectionId = $row['section_id'];
        $sectionName = $row['section_name'];
    }
    $stmtProgram->close();
}

// If not promoted, redirect to regular enlistment
if (!$isPromoted) {
    header("Location: student_enlistment.php");
    exit;
}

// Get subjects for the student's grade level and strand
$subjects = [];
if ($gradeLevel && $strandId) {
    $stmtSubjects = $connection->prepare("
        SELECT subject_id, subject_name
        FROM subject
        WHERE grade_level = ? AND strand_id = ?
        ORDER BY subject_order
    ");
    $stmtSubjects->bind_param("ii", $gradeLevel, $strandId);
    $stmtSubjects->execute();
    $subjects = $stmtSubjects->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtSubjects->close();
}

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_enlistment'])) {
    $selectedSubjects = $_POST['subjects'] ?? [];
    $allSubjectIds = $_POST['all_subjects'] ?? [];
    
    if (empty($selectedSubjects)) {
        $errorMessage = "Please select at least one subject.";
    } else {
        $connection->begin_transaction();
        
        try {
            // Use the student's existing school_year from the students table
            $school_year = $student_school_year;
            
            // Insert/Update ALL subjects - checked ones as 'Enrolled', unchecked as 'Dropped'
            // No admin validation needed for promoted students
            $stmtSubj = $connection->prepare("
                INSERT INTO student_subjects (student_id, subject_id, status, requested, school_year)
                VALUES (?, ?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    requested = 1,
                    school_year = VALUES(school_year)
            ");
            
            foreach ($allSubjectIds as $subject_id) {
                // If checked -> Enrolled, if unchecked -> Dropped
                $status = in_array($subject_id, $selectedSubjects) ? 'Enrolled' : 'Dropped';
                $stmtSubj->bind_param("iiss", $student_id, $subject_id, $status, $school_year);
                $stmtSubj->execute();
            }
            $stmtSubj->close();
            
            // Update enlistment status to Enlisted (no admin validation needed for promoted students)
            $stmtStatus = $connection->prepare("
                UPDATE students
                SET enlistment_status = 'Enlisted'
                WHERE student_id = ?
            ");
            $stmtStatus->bind_param("i", $student_id);
            $stmtStatus->execute();
            $stmtStatus->close();
            
            // Update school_year in students table
            $stmtUpdateYear = $connection->prepare("
                UPDATE students
                SET school_year = ?
                WHERE student_id = ?
            ");
            $stmtUpdateYear->bind_param("si", $school_year, $student_id);
            $stmtUpdateYear->execute();
            $stmtUpdateYear->close();
            
            $connection->commit();
            $successMessage = "Enlistment completed successfully!";
            
            // Refresh the page to show updated status
            header("Location: home.php");
            exit;
            
        } catch (Exception $e) {
            $connection->rollback();
            $errorMessage = "Error submitting enlistment: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/dashboard_design.css">
    <link rel="stylesheet" href="../../Design/student/enlistment.css">
    <title>Promoted Student Enlistment</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <style>
        .info-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .info-card h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
            border-bottom: 2px solid #fbbf24;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            width: 150px;
        }
        
        .info-value {
            color: #1e3a8a;
            font-weight: bold;
        }
        
        .subject-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .select-all-container {
            margin-bottom: 15px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 8px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }
    </style>
</head>
<body>

<!-- header -->
<div class="header">
    <div class="left">
        <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
        <span>CDONSHS-SHS</span>
    </div>

    <div class="center">
        Promoted Student Enlistment
    </div>

    <div class="right">
        <button class="profile-btn" type="button">
            <img src="<?php echo $profileImagePath; ?>">
        </button>

        <div class="profile-dropdown">
            <a href="profile_page.php">View Profile</a>
            <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="enlistment-container">
    <h2>Promoted Student Enlistment</h2>
    
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

    <form method="POST">
        <!-- Student Information Card -->
        <div class="info-card">
            <h3>Student Information</h3>
            <div class="info-row">
                <span class="info-label">Grade Level:</span>
                <span class="info-value">Grade <?php echo htmlspecialchars($gradeLevel ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Strand:</span>
                <span class="info-value"><?php echo htmlspecialchars($strandName ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Section:</span>
                <span class="info-value"><?php echo htmlspecialchars($sectionName ?? 'N/A'); ?></span>
            </div>
        </div>

        <!-- Subjects Card -->
        <div class="info-card">
            <h3>Subjects to Enroll</h3>
            
            <div class="select-all-container">
                <label>
                    <input type="checkbox" id="select_all" class="subject-checkbox" checked>
                    <strong>Select All Subjects</strong>
                </label>
            </div>
            
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Enroll</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td>
                                    <!-- Hidden input to always send the subject ID -->
                                    <input type="hidden" 
                                           name="all_subjects[]" 
                                           value="<?php echo $subject['subject_id']; ?>">
                                    <input type="checkbox" 
                                           name="subjects[]" 
                                           value="<?php echo $subject['subject_id']; ?>" 
                                           class="subject-checkbox subject-item"
                                           checked>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No subjects found for your grade level and strand.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($subjects)): ?>
                <button type="submit" name="submit_enlistment" class="submit-btn">Submit Enlistment</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- footer -->
<div class="footer">
    © 2026 Cagayan De Oro National High School - Senior High School  
    <br>
    School Management System
</div>

<script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
<script>
    // Select All functionality
    document.getElementById('select_all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.subject-item');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Update "Select All" checkbox when individual checkboxes change
    document.querySelectorAll('.subject-item').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.subject-item:checked').length === document.querySelectorAll('.subject-item').length;
            document.getElementById('select_all').checked = allChecked;
        });
    });
</script>
</body>
</html>