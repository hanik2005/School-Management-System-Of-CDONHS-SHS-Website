<?php
// Make sure $connection is available
if (!isset($connection)) {
    die("Database connection not found!");
}

// Get all strands
$strandQuery = $connection->query("SELECT strand_id, strand_name FROM strands ORDER BY strand_name");
$strands = [];
while ($s = $strandQuery->fetch_assoc()) {
    $strands[] = $s;
}

// Get sections (no duplicates)
$sectionSql = "SELECT DISTINCT sec.section_name, sec.section_id
               FROM section sec
               JOIN student_strand ss ON ss.section_id = sec.section_id
               WHERE 1=1";

// Filter by selected strand
if (isset($_GET['strand']) && !empty($_GET['strand'])) {
    $strand_id = (int)$_GET['strand']; // sanitize input
    $sectionSql .= " AND ss.strand_id = $strand_id";
}

$sectionSql .= " ORDER BY sec.section_name";

$sectionQuery = $connection->query($sectionSql);
$sections = [];
while ($sec = $sectionQuery->fetch_assoc()) {
    // Check if the section name is already added
    if (!in_array($sec['section_name'], array_column($sections, 'section_name'))) {
        $sections[] = $sec;
    }
}
?>
