<?php
// ===============================
// Database Connection
// ===============================
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

// ===============================
// Fetch Enrollment Records
// ===============================
$sql = "SELECT * FROM enrollment_applications ORDER BY application_id DESC";
$result = $connection->query($sql);

if (!$result) {
    die("Query Failed: " . $connection->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Enrollment List</title>

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

<h2>Enrollment Applications</h2>

<table>
    <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>LRN</th>
        <th>Email</th>
        <th>PSA Birth Certificate</th>
        <th>Form 138</th>
        <th>Student ID</th>
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

                <td><?= htmlspecialchars($row['lrn']); ?></td>

                <td><?= htmlspecialchars($row['email']); ?></td>

                <!-- PSA -->
                <td>
                    <?php if (!empty($row['psa_birth_certificate'])): ?>
                        <a href="../../<?= htmlspecialchars($row['psa_birth_certificate']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- Form 138 -->
                <td>
                    <?php if (!empty($row['form_138'])): ?>
                        <a href="../../<?= htmlspecialchars($row['form_138']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- Student ID -->
                <td>
                    <?php if (!empty($row['student_id_copy'])): ?>
                        <a href="../../<?= htmlspecialchars($row['student_id_copy']); ?>"
                           target="_blank"
                           class="submitted">Submitted</a>
                    <?php else: ?>
                        <span class="missing">Not Submitted</span>
                    <?php endif; ?>
                </td>

                <!-- FORM: REMARKS + STATUS -->
                <td colspan="3">
                    <form action="../../Back_End_Files/PHP_Files/update_remarks.php" method="POST">
                        <input type="hidden" name="application_id" value="<?= $row['application_id']; ?>">

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
</table>

</body>
</html>
