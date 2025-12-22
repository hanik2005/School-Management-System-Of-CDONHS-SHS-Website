<?php
include $_SERVER['DOCUMENT_ROOT'] . '/SMS_CDONHS-SHS_WEBSITE/DB_Connection/Connection.php';

$result = $connection->query("SELECT * FROM enrollment_applications ORDER BY date_submitted DESC");
?>

<h2>Enrollment Applications</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Remarks</th>
        <th>Action</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
            <td><?= $row['application_status']; ?></td>
            <td><?= $row['remarks'] ?? '—'; ?></td>
            <td>
                <form action="update_remarks.php" method="POST">
                    <input type="hidden" name="application_id" value="<?= $row['application_id']; ?>">
                    
                    <textarea name="remarks" rows="3" cols="30"
                        placeholder="Enter admin remarks here..."><?= $row['remarks']; ?></textarea><br><br>

                    <select name="application_status" required>
                        <option value="Pending" <?= $row['application_status']=='Pending'?'selected':''; ?>>Pending</option>
                        <option value="Approved" <?= $row['application_status']=='Approved'?'selected':''; ?>>Approved</option>
                        <option value="Rejected" <?= $row['application_status']=='Rejected'?'selected':''; ?>>Rejected</option>
                    </select><br><br>

                    <button type="submit">Save</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>
