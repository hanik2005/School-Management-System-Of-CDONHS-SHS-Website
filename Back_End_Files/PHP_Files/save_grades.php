<?php
/* SAVE GRADES */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    foreach ($_POST['grades'] as $student_id => $grade) {

        $grade_status = "Draft"; // ✅ ALWAYS DRAFT

        /* CHECK EXISTING */
        $check = $connection->prepare("
            SELECT entry_id FROM grade_entry
            WHERE student_id=? AND subject_id=? AND section_id=? AND quarter=?
        ");

        $check->bind_param("iiii", $student_id, $subject_id, $section_id, $quarter);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $update = $connection->prepare("
                UPDATE grade_entry
                SET grade=?, grade_status=?
                WHERE student_id=? AND subject_id=? AND section_id=? AND quarter=?
            ");

            $update->bind_param(
                "dsiiii",
                $grade,
                $grade_status,
                $student_id,
                $subject_id,
                $section_id,
                $quarter
            );

            $update->execute();
            $update->close();

        } else {

            $insert = $connection->prepare("
                INSERT INTO grade_entry
                (student_id, subject_id, section_id, quarter, grade, grade_status)
                VALUES (?,?,?,?,?,?)
            ");

            $insert->bind_param(
                "iiiids",
                $student_id,
                $subject_id,
                $section_id,
                $quarter,
                $grade,
                $grade_status
            );

            $insert->execute();
            $insert->close();
        }

        $check->close();
    }

    header("Location: grading_page.php?subject=$subject_id&quarter=$quarter&success=1");
    exit;
}
?>