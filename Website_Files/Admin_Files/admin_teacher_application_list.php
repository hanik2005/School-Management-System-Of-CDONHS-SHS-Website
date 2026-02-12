<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../../DB_Connection/Connection.php";

/* ===============================
   VERIFY ADMIN SESSION
================================ */
$stmt = $connection->prepare("
    SELECT * FROM users 
    WHERE user_id = ? 
    AND school_id = ? 
    AND role_id = 2
");

$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['school_id']);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

/* ===============================
   GET TEACHER APPLICATIONS
================================ */
$sql = "SELECT * FROM teacher_applications ORDER BY teacher_application_id DESC";
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
    <title>Admin Teacher Applications</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #f2f2f2; }
        textarea { width: 95%; }
        .submitted { color: green; font-weight: bold; text-decoration: underline; }
        .missing { color: red; font-weight: bold; }
    </style>
</head>

<body>
<h2>Teacher Applications List</h2>

<table>
    <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Resume CV</th>
        <th>PRC ID</th>
        <th>Certificates</th>
        <th>Other Documents</th>
        <th>Advisory Assignment</th>
        <th>Remarks / Status</th>
    </tr>

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
                        <a href="../../<?= htmlspecialchars($row['resume_cv']); ?>" target="_blank" class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- PRC -->
                <td>
                    <?php if (!empty($row['prc_id_copy'])): ?>
                        <a href="../../<?= htmlspecialchars($row['prc_id_copy']); ?>" target="_blank" class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- Certificates -->
                <td>
                    <?php if (!empty($row['certificates'])): ?>
                        <a href="../../<?= htmlspecialchars($row['certificates']); ?>" target="_blank" class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- Other Docs -->
                <td>
                    <?php if (!empty($row['other_documents'])): ?>
                        <a href="../../<?= htmlspecialchars($row['other_documents']); ?>" target="_blank" class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- FORM -->
                <td colspan="2">
                    <form action="../../Back_End_Files/PHP_Files/teacher_update_remarks.php" method="POST">

                        <input type="hidden" name="teacher_application_id" value="<?= $row['teacher_application_id']; ?>">

                        <!-- Advisory Dropdown -->
                        <select name="advisory_assignment">
                            <option value="">Select Advisory</option>

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
                                    $alreadyAssigned = '';

                                    if ($current) {
                                        if (
                                            $current['strand_id'] == $adv['strand_id'] &&
                                            $current['grade_level'] == $adv['grade_level'] &&
                                            $current['section_id'] == $adv['section_id']
                                        ) {
                                            $selected = 'selected';
                                            $alreadyAssigned = ' (Already Assigned)';
                                        }
                                    }
                            ?>
                                    <option value="<?= $value ?>" <?= $selected ?>> 
                                        Grade <?= $adv['grade_level']; ?> - 
                                        <?= htmlspecialchars($adv['strand_name']); ?> - 
                                        <?= htmlspecialchars($adv['section_name']); ?>
                                        <?= $alreadyAssigned ?>
                                    </option>
                            <?php endwhile; endif; ?>
                        </select>

                        <br><br>

                        <textarea name="remarks" rows="3" placeholder="Enter admin remarks..."><?= htmlspecialchars($row['remarks']); ?></textarea>

                        <br><br>

                        <select name="application_status" required>
                            <option value="Pending" <?= $row['application_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?= $row['application_status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?= $row['application_status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>

                        <br><br>

                        <button type="submit">Save</button>
                    </form>
                </td>

            </tr>
        <?php endwhile; ?>

    <?php else: ?>
        <tr>
            <td colspan="9">No applications found.</td>
        </tr>
    <?php endif; ?>

</table>

</body>
</html>
