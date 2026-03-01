<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* ========================= */
/* GET TEACHER INFO          */
/* ========================= */
$stmt = $connection->prepare("
    SELECT u.*, ta.first_name as teacher_first_name, ta.last_name as teacher_last_name
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
/* GET SUBJECTS              */
/* ========================= */
$subjects = [];
if ($advisoryInfo) {
    $subjectSql = "SELECT subject_id, subject_name, grade_level FROM subject WHERE grade_level = ? AND strand_id = ? ORDER BY subject_id";
    $stmtSubjects = $connection->prepare($subjectSql);
    if ($stmtSubjects) {
        $stmtSubjects->bind_param("ii", $advisoryInfo['grade_level'], $advisoryInfo['strand_id']);
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

/* ========================= */
/* GET STUDENTS              */
/* ========================= */
$form_type = isset($_GET['form_type']) ? $_GET['form_type'] : '137';
$selected_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$print_all = isset($_GET['action']) && $_GET['action'] == 'print_all';

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
            sa.date_of_birth,
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
    ";
    
    if (!$print_all && !empty($selected_student_id)) {
        $studentSql .= " AND s.student_id = " . intval($selected_student_id);
    }
    
    $studentSql .= " ORDER BY sa.last_name ASC";
    
    $stmtStudents = $connection->prepare($studentSql);
    if ($stmtStudents) {
        $stmtStudents->bind_param("i", $teacher_id);
        $stmtStudents->execute();
        $studentsResult = $stmtStudents->get_result();
        
        while ($row = $studentsResult->fetch_assoc()) {
            // Get grades for this student - show all grades including Draft
            $gradeSql = "
                SELECT 
                    sub.subject_name,
                    sub.subject_id,
                    ge.grade,
                    ge.quarter
                FROM grade_entry ge
                JOIN subject sub ON ge.subject_id = sub.subject_id
                WHERE ge.student_id = ?
                ORDER BY sub.subject_id, ge.quarter
            ";
            
            $grades = [];
            $stmtGrades = $connection->prepare($gradeSql);
            if ($stmtGrades) {
                $stmtGrades->bind_param("i", $row['student_id']);
                $stmtGrades->execute();
                $gradesResult = $stmtGrades->get_result();
                
                while ($gradeRow = $gradesResult->fetch_assoc()) {
                    $grades[$gradeRow['subject_id']][$gradeRow['quarter']] = [
                        'grade' => $gradeRow['grade']
                    ];
                }
                $stmtGrades->close();
            }
            
            // Get attendance for this student - using default values since no attendance table exists
            $attendance = ['total' => 0, 'present' => 0];
            
            $row['grades'] = $grades;
            $row['attendance'] = $attendance;
            $students[] = $row;
        }
        $stmtStudents->close();
    }
}

function calculateAverage($grades, $subjects) {
    $total = 0;
    $count = 0;
    foreach ($subjects as $subject) {
        $subject_id = $subject['subject_id'];
        for ($q = 1; $q <= 4; $q++) {
            if (isset($grades[$subject_id][$q]['grade'])) {
                $total += $grades[$subject_id][$q]['grade'];
                $count++;
            }
        }
    }
    return $count > 0 ? number_format($total / $count, 2) : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $form_type == '137' ? 'Form 137 - Permanent Record' : 'Form 138 - Report Card'; ?></title>
    <link rel="stylesheet" href="../../Design/teacher/form_137_138_print_design.css">
    <style>
        
    </style>
</head>
<body>
    <div class="no-print" style="padding: 20px; text-align: center; background: #f3f4f6; border-bottom: 2px solid #ddd;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            🖨️ Print Form <?php echo $form_type == '137' ? '137' : '138'; ?>
        </button>
        <a href="form_137_138_page.php" style="margin-left: 15px; color: #2563eb; text-decoration: none;">← Back to Selection</a>
    </div>

    <a href="form_137_138_page.php" class="back-btn no-print">← Back</a>

    <?php foreach ($students as $student): ?>
    <div class="page <?php echo $form_type == '137' ? 'form137' : 'form138'; ?>">
        
        <!-- Header -->
        <div class="header-section">
            <div class="school-header">
                <img src="../../Assets/LOGO.png" alt="Logo" class="school-logo">
                <div>
                    <div class="school-name">CAGAYAN DE ORO NATIONAL HIGH SCHOOL - SENIOR HIGH SCHOOL</div>
                    <div>2nd 3rd St, Cagayan De Oro City, 9000 Misamis Oriental</div>
                </div>
            </div>
            
            <?php if ($form_type == '137'): ?>
            <div class="form-title">FORM 137 - PERMANENT RECORD</div>
            <?php else: ?>
            <div class="form-title">FORM 138 - REPORT CARD</div>
            <div class="form-subtitle">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Quarter: __________________</span>
                    <span>School Year: <?php echo htmlspecialchars($school_year); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-subtitle">School Year: <?php echo htmlspecialchars($school_year); ?></div>
        </div>
        
        <!-- Student Information -->
        <table class="student-info">
            <tr>
                <th colspan="4" style="background: #e5e7eb;">STUDENT INFORMATION</th>
            </tr>
            <tr>
                <td style="width: 25%;"><strong>LRN:</strong> <?php echo htmlspecialchars($student['lrn']); ?></td>
                <td style="width: 25%;"><strong>Sex:</strong> <?php echo htmlspecialchars(strtoupper($student['sex'])); ?></td>
                <td style="width: 25%;"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                <td style="width: 25%;"><strong>Grade Level:</strong> <?php echo htmlspecialchars($student['grade_level']); ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>Full Name:</strong> <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']); ?></td>
                <td colspan="2"><strong>Section:</strong> <?php echo htmlspecialchars($student['section_name']); ?></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Address:</strong> <?php echo htmlspecialchars($student['house_number_street'] . ', ' . $student['barangay'] . ', ' . $student['city_municipality'] . ', ' . $student['province']); ?></td>
            </tr>
        </table>
        
        <?php if ($form_type == '138'): ?>
        <!-- Attendance Record for Form 138 - Using placeholder since no attendance table exists -->
        <table class="grades-table" style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th colspan="6" style="background: #e5e7eb; text-align: left;">ATTENDANCE RECORD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left;"><strong>Days of School</strong></td>
                    <td>-</td>
                    <td style="text-align: left;"><strong>Days Present</strong></td>
                    <td>-</td>
                    <td style="text-align: left;"><strong>Days Absent</strong></td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Core Values for Form 138 - Using placeholder since no core values table exists -->
        <table class="grades-table" style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th colspan="6" style="background: #e5e7eb; text-align: left;">CORE VALUES ASSESSMENT</th>
                </tr>
                <tr>
                    <th style="text-align: left;">Core Values</th>
                    <th>1st Quarter</th>
                    <th>2nd Quarter</th>
                    <th>3rd Quarter</th>
                    <th>4th Quarter</th>
                    <th>Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left;">1. Maagap at Mapagkalingang Paglilingkod</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">2. Pakikipagkapwa-tao</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">3. Pagmamahal sa Bansa</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">4. Kalusugan at Fitness</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">5. Pagpapatuloy ng Pag-aaral</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <p style="font-size: 9px; margin-bottom: 10px;"><em>Legend: A - Outstanding (90-100) | S - Satisfactory (85-89) | D - Developing (80-84) | B - Beginning (75-79)</em></p>
        <?php endif; ?>
        
        <!-- Academic Record -->
        <table class="grades-table">
            <thead>
                <tr>
                    <th rowspan="2" class="subject-col">SUBJECTS</th>
                    <th colspan="4">QUARTER</th>
                    <th rowspan="2" class="final-col">FINAL RATING</th>
                </tr>
                <tr>
                    <th class="quarter-col">1st</th>
                    <th class="quarter-col">2nd</th>
                    <th class="quarter-col">3rd</th>
                    <th class="quarter-col">4th</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td class="subject-col"><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                    <td>
                        <?php 
                        $s_id = $subject['subject_id'];
                        echo isset($student['grades'][$s_id][1]['grade']) ? htmlspecialchars($student['grades'][$s_id][1]['grade']) : '-';
                        ?>
                    </td>
                    <td>
                        <?php 
                        echo isset($student['grades'][$s_id][2]['grade']) ? htmlspecialchars($student['grades'][$s_id][2]['grade']) : '-';
                        ?>
                    </td>
                    <td>
                        <?php 
                        echo isset($student['grades'][$s_id][3]['grade']) ? htmlspecialchars($student['grades'][$s_id][3]['grade']) : '-';
                        ?>
                    </td>
                    <td>
                        <?php 
                        echo isset($student['grades'][$s_id][4]['grade']) ? htmlspecialchars($student['grades'][$s_id][4]['grade']) : '-';
                        ?>
                    </td>
                    <td class="final-col">
                        <?php
                        $total = 0;
                        $count = 0;
                        for ($q = 1; $q <= 4; $q++) {
                            if (isset($student['grades'][$s_id][$q]['grade'])) {
                                $total += $student['grades'][$s_id][$q]['grade'];
                                $count++;
                            }
                        }
                        echo $count > 0 ? number_format($total / $count, 2) : '-';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <!-- General Average -->
                <tr style="background: #f3f4f6;">
                    <td colspan="5" style="text-align: right; font-weight: bold;">GENERAL AVERAGE</td>
                    <td class="final-col" style="font-size: 12px; font-weight: bold;">
                        <?php echo calculateAverage($student['grades'], $subjects); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Remarks -->
        <table class="student-info">
            <tr>
                <th style="width: 30%;">REMARKS</th>
                <td>
                    <?php 
                    $avg = calculateAverage($student['grades'], $subjects);
                    if ($avg >= 75) {
                        echo "PROMOTED";
                    } elseif ($avg > 0) {
                        echo "RETAINED";
                    } else {
                        echo "No grades recorded";
                    }
                    ?>
                </td>
            </tr>
        </table>
        
        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <?php echo htmlspecialchars($teacher_name); ?><br>
                    Class Adviser
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <br><br><br>
                    Principal
                </div>
            </div>
        </div>
        
    </div>
    <?php endforeach; ?>

</body>
</html>
