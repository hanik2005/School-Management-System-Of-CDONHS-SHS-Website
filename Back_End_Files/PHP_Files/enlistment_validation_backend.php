<?php
session_start();
include "../../DB_Connection/Connection.php";

// Check session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

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

/* ================= FILTER ================= */

$grade = $_GET['grade_level'] ?? '';
$strand = $_GET['strand_id'] ?? '';
$section = $_GET['section_id'] ?? '';
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

/* ----------------- GET FILTER OPTIONS ----------------- */

// Get all strands
$strandQuery = $connection->query("SELECT strand_id, strand_name FROM strands ORDER BY strand_name");
$strands = [];
while ($s = $strandQuery->fetch_assoc()) $strands[] = $s;

// Get sections: if strand selected, only sections for that strand
$sectionSql = "SELECT DISTINCT sec.section_id, sec.section_name
               FROM section sec
               JOIN student_strand ss ON ss.section_id = sec.section_id
               WHERE 1=1";
if (!empty($strand)) {
    $strand_id = (int)$strand;
    $sectionSql .= " AND ss.strand_id = $strand_id";
}
$sectionSql .= " ORDER BY sec.section_name";

$sectionQuery = $connection->query($sectionSql);
$sections = [];
while ($sec = $sectionQuery->fetch_assoc()) $sections[] = $sec;

/* ----------------- FETCH STUDENTS ----------------- */

$sql = "
SELECT s.student_id,
       sa.lrn,
       sa.first_name,
       sa.last_name,
       sa.enrollment_type,
       ss.grade_level,
       st.strand_name,
       sec.section_name,
       s.enlistment_status
FROM students s
JOIN student_applications sa ON s.application_id = sa.application_id
LEFT JOIN student_strand ss ON s.student_id = ss.student_id
LEFT JOIN strands st ON ss.strand_id = st.strand_id
LEFT JOIN section sec ON ss.section_id = sec.section_id
WHERE s.enlistment_status = 'Pending'
";

$params = [];
$types = "";

// Search by name filter
if (!empty($search_name)) {
    $sql .= " AND (sa.first_name LIKE ? OR sa.last_name LIKE ? OR CONCAT(sa.first_name, ' ', sa.last_name) LIKE ?)";
    $searchParam = "%" . $search_name . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

// Grade filter
if (!empty($grade)) {
    $sql .= " AND ss.grade_level = ?";
    $params[] = $grade;
    $types .= "s";
}

// Strand filter
if (!empty($strand)) {
    $sql .= " AND st.strand_id = ?";
    $params[] = $strand;
    $types .= "i";

    // Section filter only if strand is selected
    if (!empty($section)) {
        $sql .= " AND sec.section_id = ?";
        $params[] = $section;
        $types .= "i";
    }
}

$stmt = $connection->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
?>
