<?php
include "../../DB_Connection/Connection.php";
/* CLEAR MYSQL LEFTOVER RESULTS */
while ($connection->more_results() && $connection->next_result()) {}

/* ===============================
   APPROVE GRADES
=============================== */
if (isset($_POST['approve_list'])) {

    foreach ($_POST['approve_list'] as $item) {

        list($grade, $section, $quarter) = explode('|', $item);

        $update = $connection->prepare("
            UPDATE grade_entry ge
            JOIN section sec ON sec.section_id = ge.section_id
            SET ge.grade_status = 'Approved'
            WHERE sec.grade_level = ?
            AND sec.section_name = ?
            AND ge.quarter = ?
            AND ge.grade_status = 'Submitted'
            AND NOT EXISTS (
                SELECT 1 FROM archived_student_strand ass 
                WHERE ass.student_id = ge.student_id 
                AND ass.section_id = ge.section_id
            )
        ");

        $update->bind_param("isi", $grade, $section, $quarter);
        $update->execute();
        $update->close();
    }

    header("Location: admin_grade_validation.php");
    exit;
}

/* ===============================
   FILTER VALUES
=============================== */
$gradeLevel = $_GET['grade_level'] ?? '';
$quarter    = $_GET['quarter'] ?? '';
$status     = $_GET['status'] ?? '';

/* ===============================
   BUILD WHERE
=============================== */
$where = [];
$params = [];
$types  = "";

if ($gradeLevel !== '') {
    $where[] = "sec.grade_level = ?";
    $params[] = $gradeLevel;
    $types .= "i";
}

if ($quarter !== '') {
    $where[] = "ge.quarter = ?";
    $params[] = $quarter;
    $types .= "i";
}

// Add filter to exclude students who have been archived (promoted) from this section
$where[] = "NOT EXISTS (
    SELECT 1 FROM archived_student_strand ass 
    WHERE ass.student_id = ge.student_id 
    AND ass.section_id = ge.section_id
)";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ===============================
   VALIDATION QUERY
=============================== */
$query = "
SELECT 
    sec.section_id,
    sec.grade_level,
    st.strand_name,
    sec.section_name,
    ge.quarter,
    COUNT(DISTINCT ge.subject_id) AS subject_count,
    CASE
        WHEN SUM(ge.grade_status = 'Draft') > 0 THEN 'Draft'
        WHEN SUM(ge.grade_status = 'Submitted') > 0 
             AND SUM(ge.grade_status = 'Approved') = 0 THEN 'Submitted'
        WHEN SUM(ge.grade_status = 'Approved') > 0 
             AND SUM(ge.grade_status != 'Approved') = 0 THEN 'Approved'
        ELSE 'Draft'
    END AS status
FROM grade_entry ge
JOIN section sec ON sec.section_id = ge.section_id
JOIN strands st ON st.strand_id = sec.strand_id
$whereSQL
GROUP BY 
    sec.section_id,
    sec.grade_level,
    st.strand_name,
    sec.section_name,
    ge.quarter
ORDER BY 
    sec.grade_level, 
    st.strand_name, 
    sec.section_name, 
    ge.quarter
";
$stmt = $connection->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();
$validationData = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

/* STATUS FILTER */
if ($status !== '') {
    $validationData = array_filter($validationData, function($row) use ($status) {
        return $row['status'] === $status;
    });
}
?>
