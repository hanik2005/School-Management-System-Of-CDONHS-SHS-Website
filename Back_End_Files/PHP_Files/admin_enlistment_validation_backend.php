<?php
include "../../DB_Connection/Connection.php";

if (isset($_POST['confirm']) && isset($_POST['status'])) {

    foreach ($_POST['status'] as $student_id => $status) {

        // Update student's enlistment status
        $stmt = $connection->prepare("
            UPDATE students 
            SET enlistment_status=? 
            WHERE student_id=?
        ");

        $stmt->bind_param("si", $status, $student_id);
        $stmt->execute();
        $stmt->close();

        // If admin confirms as "Enlisted", update subject statuses
        if ($status === 'Enlisted') {
            // Update subjects that student requested to "Enrolled"
            $stmtEnrolled = $connection->prepare("
                UPDATE student_subjects 
                SET status = 'Enrolled' 
                WHERE student_id = ? AND requested = 1 AND status = 'Pending'
            ");
            $stmtEnrolled->bind_param("i", $student_id);
            $stmtEnrolled->execute();
            $stmtEnrolled->close();

            // Update subjects that student did NOT request to "Dropped"
            $stmtDropped = $connection->prepare("
                UPDATE student_subjects 
                SET status = 'Dropped' 
                WHERE student_id = ? AND requested = 0 AND status = 'Pending'
            ");
            $stmtDropped->bind_param("i", $student_id);
            $stmtDropped->execute();
            $stmtDropped->close();
        }

        // If admin rejects, delete pending subjects or keep them as rejected
        if ($status === 'Rejected') {
            $stmtReject = $connection->prepare("
                DELETE FROM student_subjects 
                WHERE student_id = ? AND status = 'Pending'
            ");
            $stmtReject->bind_param("i", $student_id);
            $stmtReject->execute();
            $stmtReject->close();
        }
    }

    header("Location: ../../Website_Files/Admin_Files/enlistment_validation_page.php");
    exit;
}
?>
