<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* ========================= */
/* VERIFY ADMIN SESSION      */
/* ========================= */
$user_id = $_SESSION['user_id'];

// Prepare statement
$stmt = mysqli_prepare($connection, "
    SELECT * FROM users 
    WHERE user_id = ?  
    AND role_id = 2
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

/* ========================= */
/* FILTER PARAMETERS         */
/* ========================= */
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

/* ===============================
   GET TEACHER APPLICATIONS WITH FILTERS
================================ */
$sql = "SELECT * FROM teacher_applications WHERE application_status = 'Pending'";

if (!empty($search_name)) {
    $sql .= " AND (first_name LIKE '%$search_name%' OR last_name LIKE '%$search_name%' OR CONCAT(first_name, ' ', last_name) LIKE '%$search_name%')";
}

$sql .= " ORDER BY teacher_application_id DESC";

$result = $connection->query($sql);

if (!$result) {
    die("Query failed: " . $connection->error);
}

/* ===============================
   GET ALL ADVISORY OPTIONS
================================ */
$advisoryQuery = "
    SELECT DISTINCT 
        ss.strand_id,
        st.strand_name,
        ss.grade_level,
        ss.section_id,
        sec.section_name
    FROM student_strand ss
    JOIN strands st ON ss.strand_id = st.strand_id
    JOIN section sec ON ss.section_id = sec.section_id
    ORDER BY ss.grade_level, st.strand_name, sec.section_name
";
$advisoryResult = $connection->query($advisoryQuery);

/* ===============================
   GET ALL ALREADY ASSIGNED ADVISORIES
================================ */
$assignedAdvisories = [];
$assignedQuery = "
    SELECT ta.strand_id, ta.grade_level, ta.section_id, t.application_id
    FROM teacher_advisory ta
    JOIN teachers t ON ta.teacher_id = t.teacher_id
";
$assignedResult = $connection->query($assignedQuery);
while ($assigned = $assignedResult->fetch_assoc()) {
    $key = $assigned['strand_id'] . "|" . $assigned['grade_level'] . "|" . $assigned['section_id'];
    $assignedAdvisories[$key] = $assigned['application_id'];
}

/* ===============================
   PREPARE CURRENT ADVISORY STATEMENT
================================ */
$currentAdvisoryStmt = $connection->prepare("
    SELECT ta.strand_id, ta.grade_level, ta.section_id
    FROM teacher_advisory ta
    JOIN teachers t ON ta.teacher_id = t.teacher_id
    WHERE t.application_id = ?
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Applications - CDONHS-SHS Admin</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/admin/application_list_design.css">
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
            Admin
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
        <h1>Teacher Application List</h1>
    </div>

    <!-- Navigation -->
    <div class="nav-links">
        <a href="application_page.php">← Back to Dashboard</a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search_name">Search by Name:</label>
                    <input type="text" id="search_name" name="search_name" 
                           placeholder="Enter teacher name..." 
                           value="<?= htmlspecialchars($search_name); ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-filter">🔍 Search</button>
                    <a href="admin_teacher_application_list.php" class="btn btn-reset">↻ Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Resume</th>
                    <th>PRC ID</th>
                    <th>Certificates</th>
                    <th>Other Docs</th>
                    <th>Status</th>
                    <th>Advisory & Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $count = 1; ?>

                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++; ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>

                            <!-- Resume -->
                            <td>
                                <?php if (!empty($row['resume_cv'])): ?>
                                    <a href="../../uploads/<?= htmlspecialchars($row['resume_cv']); ?>" target="_blank" class="doc-submitted">✓ View</a>
                                <?php else: ?>
                                    <span class="doc-missing">✗ Missing</span>
                                <?php endif; ?>
                            </td>

                            <!-- PRC -->
                            <td>
                                <?php if (!empty($row['prc_id_copy'])): ?>
                                    <a href="../../uploads/<?= htmlspecialchars($row['prc_id_copy']); ?>" target="_blank" class="doc-submitted">✓ View</a>
                                <?php else: ?>
                                    <span class="doc-missing">✗ Missing</span>
                                <?php endif; ?>
                            </td>

                            <!-- Certificates -->
                            <td>
                                <?php if (!empty($row['certificates'])): ?>
                                    <a href="../../uploads/<?= htmlspecialchars($row['certificates']); ?>" target="_blank" class="doc-submitted">✓ View</a>
                                <?php else: ?>
                                    <span class="doc-missing">✗ Missing</span>
                                <?php endif; ?>
                            </td>

                            <!-- Other Docs -->
                            <td>
                                <?php if (!empty($row['other_documents'])): ?>
                                    <a href="../../uploads/<?= htmlspecialchars($row['other_documents']); ?>" target="_blank" class="doc-submitted">✓ View</a>
                                <?php else: ?>
                                    <span class="doc-missing">✗ Missing</span>
                                <?php endif; ?>
                            </td>

                            <!-- Current Status -->
                            <td>
                                <?php 
                                $statusClass = 'status-pending';
                                if ($row['application_status'] == 'Approved') $statusClass = 'status-approved';
                                if ($row['application_status'] == 'Rejected') $statusClass = 'status-rejected';
                                ?>
                                <span class="status-badge <?= $statusClass; ?>">
                                    <?= htmlspecialchars($row['application_status']); ?>
                                </span>
                            </td>

                            <!-- FORM -->
                            <td colspan="2">
                                <form action="../../Back_End_Files/PHP_Files/teacher_update_remarks.php" method="POST" class="form-inline">

                                    <input type="hidden" name="teacher_application_id" value="<?= $row['teacher_application_id']; ?>">

                                    <!-- Advisory Dropdown -->
                                    <select name="advisory_assignment">
                                        <option value="">-- Select Advisory --</option>

                                        <?php
                                        if ($advisoryResult->num_rows > 0):
                                            $advisoryResult->data_seek(0);

                                            // Get current advisory for this teacher
                                            $currentAdvisoryStmt->bind_param("i", $row['teacher_application_id']);
                                            $currentAdvisoryStmt->execute();
                                            $current = $currentAdvisoryStmt->get_result()->fetch_assoc();

                                            while ($adv = $advisoryResult->fetch_assoc()):
                                                $value = $adv['strand_id'] . "|" . $adv['grade_level'] . "|" . $adv['section_id'];
                                                $selected = '';
                                                $disabled = '';
                                                $label = '';

                                                // Check if this is the current assignment
                                                if ($current) {
                                                    if (
                                                        $current['strand_id'] == $adv['strand_id'] &&
                                                        $current['grade_level'] == $adv['grade_level'] &&
                                                        $current['section_id'] == $adv['section_id']
                                                    ) {
                                                        $selected = 'selected';
                                                        $label = ' (Assigned)';
                                                    }
                                                }

                                                // Check if already assigned to another teacher
                                                if (!$selected && isset($assignedAdvisories[$value])) {
                                                    $disabled = 'disabled';
                                                    $label = ' (Taken)';
                                                }
                                        ?>
                                                <option value="<?= $value ?>" <?= $selected ?> <?= $disabled ?>> 
                                                    Grade <?= $adv['grade_level']; ?> - 
                                                    <?= htmlspecialchars($adv['strand_name']); ?> - 
                                                    <?= htmlspecialchars($adv['section_name']); ?>
                                                    <?= $label ?>
                                                </option>
                                        <?php endwhile; endif; ?>
                                    </select>

                                    <textarea name="remarks" rows="2" class="remarks-small" placeholder="Enter remarks..."><?= htmlspecialchars($row['remarks'] ?? ''); ?></textarea>

                                    <select name="application_status" required>
                                        <option value="Pending" <?= $row['application_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?= $row['application_status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?= $row['application_status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>

                                    <button type="submit" class="btn btn-save">Save</button>
                                </form>
                            </td>

                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 30px;">
                            No teacher applications found.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2026 Cagayan De Oro National High School - Senior High School  
        <br>
        School Management System
    </div>

    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
</body>
</html>
