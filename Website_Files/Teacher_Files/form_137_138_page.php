<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

$stmt = $connection->prepare("
    SELECT u.*, ta.profile_image, ta.first_name as teacher_first_name, ta.last_name as teacher_last_name
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

$profileImagePath = !empty($user['profile_image']) 
    ? "../../uploads/" . htmlspecialchars($user['profile_image']) 
    : "../../Assets/profile_button.png";

$teacher_name = $user['teacher_first_name'] . ' ' . $user['teacher_last_name'];

/* ========================= */
/* GET ADVISORY INFO         */
/* ========================= */
$teacher_id = null;
$getTeacherId = $connection->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
if ($getTeacherId) {
    $getTeacherId->bind_param("i", $_SESSION['user_id']);
    $getTeacherId->execute();
    $teacherResult = $getTeacherId->get_result();
    if ($teacherData = $teacherResult->fetch_assoc()) {
        $teacher_id = $teacherData['teacher_id'];
    }
    $getTeacherId->close();
}

$advisoryInfo = null;
if ($teacher_id) {
    $getAdvisory = $connection->prepare("
        SELECT ta.grade_level, ta.strand_id, ta.section_id, st.strand_name, sec.section_name
        FROM teacher_advisory ta
        JOIN strands st ON ta.strand_id = st.strand_id
        JOIN section sec ON ta.section_id = sec.section_id
        WHERE ta.teacher_id = ?
    ");
    if ($getAdvisory) {
        $getAdvisory->bind_param("i", $teacher_id);
        $getAdvisory->execute();
        $advisoryResult = $getAdvisory->get_result();
        $advisoryInfo = $advisoryResult->fetch_assoc();
        $getAdvisory->close();
    }
}

/* ========================= */
/* GET ALL STUDENTS          */
/* ========================= */
$students = [];
if ($advisoryInfo) {
    $studentSql = "
        SELECT 
            s.student_id,
            sa.first_name,
            sa.last_name,
            sa.middle_name,
            sa.lrn,
            sa.date_of_birth,
            sa.sex,
            sa.house_number_street,
            sa.barangay,
            sa.city_municipality,
            sa.province,
            ss.grade_level,
            st.strand_name,
            sec.section_name
        FROM teacher_advisory ta
        JOIN student_strand ss ON ta.strand_id = ss.strand_id 
            AND ta.grade_level = ss.grade_level 
            AND ta.section_id = ss.section_id
        JOIN students s ON ss.student_id = s.student_id
        JOIN student_applications sa ON s.application_id = sa.application_id
        JOIN strands st ON ta.strand_id = st.strand_id
        JOIN section sec ON ta.section_id = sec.section_id
        WHERE ta.teacher_id = ?
        AND s.enlistment_status = 'Enlisted'
        ORDER BY sa.last_name ASC
    ";
    
    $stmtStudents = $connection->prepare($studentSql);
    if ($stmtStudents) {
        $stmtStudents->bind_param("i", $teacher_id);
        $stmtStudents->execute();
        $studentsResult = $stmtStudents->get_result();
        
        while ($row = $studentsResult->fetch_assoc()) {
            $students[] = $row;
        }
        $stmtStudents->close();
    }
}

/* ========================= */
/* GET SUBJECTS              */
/* ========================= */
$subjects = [];
if ($advisoryInfo) {
    $subjectSql = "SELECT subject_id, subject_name, grade_level FROM subjects WHERE grade_level = ? ORDER BY subject_id";
    $stmtSubjects = $connection->prepare($subjectSql);
    if ($stmtSubjects) {
        $stmtSubjects->bind_param("i", $advisoryInfo['grade_level']);
        $stmtSubjects->execute();
        $subjectsResult = $stmtSubjects->get_result();
        while ($row = $subjectsResult->fetch_assoc()) {
            $subjects[] = $row;
        }
        $stmtSubjects->close();
    }
}

// Get current school year
$currentMonth = date('n');
$currentYear = date('Y');
if ($currentMonth >= 8) {
    $school_year = $currentYear . '-' . ($currentYear + 1);
} else {
    $school_year = ($currentYear - 1) . '-' . $currentYear;
}

$form_type = isset($_GET['form_type']) ? $_GET['form_type'] : '137';
$selected_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/dashboard_design.css">
    <title>Form 137/138 - Student Records</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <style>
        .selection-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .option-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .option-card h3 {
            margin-top: 0;
            color: #1e3a8a;
            border-bottom: 2px solid #fbbf24;
            padding-bottom: 10px;
        }
        
        .form-type-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-type-btn {
            flex: 1;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        
        .form-type-btn:hover, .form-type-btn.active {
            border-color: #1e3a8a;
            background: #e0e7ff;
        }
        
        .form-type-btn h4 {
            margin: 0 0 5px;
            font-size: 18px;
        }
        
        .form-type-btn p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        
        .selection-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        
        .selection-form .form-group {
            flex: 1;
        }
        
        .selection-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .selection-form select, .selection-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-action {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .btn-single {
            background: #059669;
            color: white;
        }
        
        .btn-all {
            background: #1e3a8a;
            color: white;
        }
        
        .btn-action:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="left">
            <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
            <span>CDONHS-SHS</span>
        </div>
        <div class="center">
            Advisory: <?php echo $advisoryInfo ? htmlspecialchars('Grade ' . $advisoryInfo['grade_level'] . ' - ' . $advisoryInfo['strand_name'] . ' - ' . $advisoryInfo['section_name']) : 'No Advisory'; ?>
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

    <div class="main-container">
        
        <div class="nav-links">
            <a href="home.php">← Back to Dashboard</a>
        </div>
        
        <div class="selection-container">
            
            <div class="option-card">
                <h3>Select Form Type</h3>
                <div class="form-type-buttons">
                    <a href="?form_type=137" class="form-type-btn <?php echo $form_type == '137' ? 'active' : ''; ?>">
                        <h4>Form 137</h4>
                        <p>Permanent Record (Student's Individual Record)</p>
                    </a>
                    <a href="?form_type=138" class="form-type-btn <?php echo $form_type == '138' ? 'active' : ''; ?>">
                        <h4>Form 138</h4>
                        <p>Report Card (Student's Grade Report)</p>
                    </a>
                </div>
            </div>
            
            <div class="option-card">
                <h3><?php echo $form_type == '137' ? 'Print Form 137 - Permanent Record' : 'Print Form 138 - Report Card'; ?></h3>
                <form method="GET" class="selection-form">
                    <input type="hidden" name="form_type" value="<?php echo htmlspecialchars($form_type); ?>">
                    
                    <div class="form-group">
                        <label for="student_id">Select Student:</label>
                        <select name="student_id" id="student_id">
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['student_id']; ?>" <?php echo $selected_student_id == $student['student_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' (' . $student['lrn'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" formtarget="_blank" class="btn-action btn-single">Print Selected Student</button>
                    </div>
                </form>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <p style="font-weight: 600; margin-bottom: 10px;">Or print all students:</p>
                    <a href="form_137_138_print.php?form_type=<?php echo $form_type; ?>&action=print_all" target="_blank" class="btn-action btn-all" style="display: inline-block; text-decoration: none;">Print All Students</a>
                </div>
            </div>
            
            <div class="option-card">
                <h3>Class List - <?php echo htmlspecialchars($school_year); ?></h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #1e3a8a; color: white;">
                            <th style="padding: 10px; text-align: left;">#</th>
                            <th style="padding: 10px; text-align: left;">LRN</th>
                            <th style="padding: 10px; text-align: left;">Student Name</th>
                            <th style="padding: 10px; text-align: left;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php foreach ($students as $student): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><?php echo $count++; ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($student['lrn']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                            <td style="padding: 10px;">
                                <a href="form_137_138_print.php?form_type=<?php echo $form_type; ?>&student_id=<?php echo $student['student_id']; ?>" target="_blank" style="color: #1e3a8a;">Print</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>

    <div class="footer">
        © 2026 Cagayan De Oro National High School - Senior High School  
        <br>
        School Management System
    </div>
    
    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>
