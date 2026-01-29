<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['school_id'])) {
    include "../../DB_Connection/Connection.php";
    include '../../Back_End_Files/PHP_Files/User.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';


    $sql = "SELECT * FROM teacher_applications ORDER BY teacher_application_id DESC";
    $result = $connection->query($sql);

    if (!$result) {
        die("Query Failed: " . $connection->error);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Teacher Enrollment List</title>

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            vertical-align: top;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        textarea {
            width: 95%;
            resize: vertical;
        }
        .submitted {
            color: green;
            font-weight: bold;
            text-decoration: underline;
            cursor: pointer;
        }
        .missing {
            color: red;
            font-weight: bold;
        }
        button {
            padding: 6px 12px;
            cursor: pointer;
        }
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
        <th>PRC ID </th>
        <th>Certificates(Training/Seminars)</th>
        <th>Other Supporting Documents</th>
        <th>Remarks</th>
        <th>Application Status</th>
        <th>Action</th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
        <?php $count = 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $count++; ?></td>

                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>


                <td><?= htmlspecialchars($row['email']); ?></td>

                <!-- Resume CV -->
                <td>
                    <?php if (!empty($row['resume_cv'])): ?>
                        <a href="../../<?= htmlspecialchars($row['resume_cv']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- PRC ID -->
                <td>
                    <?php if (!empty($row['prc_id_copy'])): ?>
                        <a href="../../<?= htmlspecialchars($row['prc_id_copy']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- Certificates -->
                <td>
                    <?php if (!empty($row['certificates'])): ?>
                        <a href="../../<?= htmlspecialchars($row['certificates']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- Other Documents -->
                <td>
                    <?php if (!empty($row['other_documents'])): ?>
                        <a href="../../<?= htmlspecialchars($row['other_documents']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- FORM: REMARKS + STATUS -->
                <td colspan="3">
                    <form action="../../Back_End_Files/PHP_Files/teacher_update_remarks.php" method="POST">
                        <input type="hidden" name="teacher_application_id" value="<?= $row['teacher_application_id']; ?>">

                        <textarea name="remarks" rows="3"
                            placeholder="Enter admin remarks..."><?= htmlspecialchars($row['remarks']); ?></textarea>
                        <br><br>

                        <select name="application_status" required>
                            <option value="Pending" <?= $row['application_status']=='Pending'?'selected':''; ?>>Pending</option>
                            <option value="Approved" <?= $row['application_status']=='Approved'?'selected':''; ?>>Approved</option>
                            <option value="Rejected" <?= $row['application_status']=='Rejected'?'selected':''; ?>>Rejected</option>
                        </select>
                        <br><br>

                        <button type="submit">Save</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="10">No enrollment records found.</td>
        </tr>
    <?php endif; ?>
    <?php }else {
        header("Location: ../login.php");
        exit;
    } ?>
</table>

</body>
</html>
