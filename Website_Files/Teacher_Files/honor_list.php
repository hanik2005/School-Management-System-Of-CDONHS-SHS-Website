<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

$stmt = $connection->prepare("
    SELECT u.*, ta.profile_image 
    FROM users u
    INNER JOIN teachers s ON s.user_id = u.user_id
    INNER JOIN teacher_applications ta ON s.application_id = ta.teacher_application_id
    WHERE u.user_id = ? AND u.school_id = ? AND u.role_id = 3
");

$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['school_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
include "../../Back_End_Files/PHP_Files/honor_list_backend.php";

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
    <link rel="stylesheet" href="../../Design/teacher/honor_list_design.css">
    <title>Honor List - CDONHS-SHS</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="left">
            <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
            <span>CDONSHS-SHS</span>
        </div>
        <div class="center">
            Honor List | Advisory: <?php echo htmlspecialchars($advisoryText); ?>
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
    <div class="honor-list-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="filter-row">
                    <!-- Search by Name -->
                    <div class="filter-group">
                        <label for="search_name">Search by Name:</label>
                        <input type="text" id="search_name" name="search_name" 
                               value="<?php echo htmlspecialchars($search_name); ?>" 
                               placeholder="Enter student name...">
                    </div>

                    <!-- Student Dropdown -->
                    <div class="filter-group">
                        <label for="student_id">Student:</label>
                        <select id="student_id" name="student_id">
                            <option value="">All Students</option>
                            <?php foreach ($studentOptions as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo $filter_student == $id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Quarter Dropdown -->
                    <div class="filter-group">
                        <label for="quarter">Quarter:</label>
                        <select id="quarter" name="quarter">
                            <option value="all" <?php echo $filter_quarter === 'all' ? 'selected' : ''; ?>>All (Overall)</option>
                            <option value="1" <?php echo $filter_quarter === '1' ? 'selected' : ''; ?>>1st Quarter</option>
                            <option value="2" <?php echo $filter_quarter === '2' ? 'selected' : ''; ?>>2nd Quarter</option>
                            <option value="3" <?php echo $filter_quarter === '3' ? 'selected' : ''; ?>>3rd Quarter</option>
                            <option value="4" <?php echo $filter_quarter === '4' ? 'selected' : ''; ?>>4th Quarter</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">Filter</button>
                        <a href="honor_list.php" class="btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Honor List Table -->
        <div class="honor-table-section" id="printArea">
            <div class="table-header">
                <h2>Honor List - <?php echo htmlspecialchars($quarterLabel); ?></h2>
                <p class="subtitle">Students with Average Grade of 90 and Above</p>
                <?php if (!empty($advisoryText)): ?>
                    <p class="advisory-info">Advisory: <?php echo htmlspecialchars($advisoryText); ?></p>
                <?php endif; ?>
            </div>

            <?php if (empty($honorList)): ?>
                <div class="no-results">
                    <p>No students found with honor grades (90 and above) for the selected criteria.</p>
                </div>
            <?php else: ?>
                <table class="honor-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Grade Level</th>
                            <th>Strand</th>
                            <th>Section</th>
                            <th>Average Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($honorList as $student): 
                            $fullName = $student['last_name'] . ', ' . $student['first_name'];
                            if (!empty($student['middle_name'])) {
                                $fullName .= ' ' . substr($student['middle_name'], 0, 1) . '.';
                            }
                        ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><?php echo htmlspecialchars($fullName); ?></td>
                                <td>Grade <?php echo htmlspecialchars($student['grade_level']); ?></td>
                                <td><?php echo htmlspecialchars($student['strand_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['section_name']); ?></td>
                                <td class="grade-cell"><?php echo number_format($student['average_grade'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Print Button -->
                <div class="print-section">
                    <button onclick="printCertificates()" class="btn-print">🖨 Print Certificates</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2026 Cagayan De Oro National High School - Senior High School  
        <br>
        School Management System
    </div>

    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
    <?php include '../../Back_End_Files/PHP_Files/honor_list_certificate_function.php'; ?>
</body>
</html>
