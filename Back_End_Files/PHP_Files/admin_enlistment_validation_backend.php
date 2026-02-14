<?php
include "../../DB_Connection/Connection.php";

if (isset($_POST['confirm']) && isset($_POST['status'])) {

    foreach ($_POST['status'] as $student_id => $status) {

        $stmt = $connection->prepare("
            UPDATE students 
            SET enlistment_status=? 
            WHERE student_id=?
        ");

        $stmt->bind_param("si", $status, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../../Website_Files/Admin_Files/enlistment_validation_page.php");
    exit;
}
?>
