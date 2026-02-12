
<?php
/* GET TEACHER ADVISORY */
$getAdvisory = $connection->prepare("
    SELECT ta.section_id, ta.strand_id, ta.grade_level
    FROM teacher_advisory ta
    JOIN teachers t ON ta.teacher_id = t.teacher_id
    WHERE t.school_id = ?
");

$getAdvisory->bind_param("i", $_SESSION['school_id']);
$getAdvisory->execute();
$result = $getAdvisory->get_result();
$advisory = $result->fetch_assoc();
$getAdvisory->close();

$section_id = $advisory['section_id'];
$strand_id = $advisory['strand_id'];
$grade_level = $advisory['grade_level'];


/* DEFAULT QUARTER */
$quarter = isset($_GET['quarter']) ? intval($_GET['quarter']) : 1;


/* GET SUBJECTS */
$getSubjects = $connection->prepare("
    SELECT subject_id, subject_name
    FROM subject
    WHERE strand_id = ?
    AND grade_level = ?
    ORDER BY subject_order
");

$getSubjects->bind_param("ii", $strand_id, $grade_level);
$getSubjects->execute();
$result = $getSubjects->get_result();
$subjects = $result->fetch_all(MYSQLI_ASSOC);
$getSubjects->close();

$subject_id = isset($_GET['subject'])
    ? intval($_GET['subject'])
    : $subjects[0]['subject_id'];


/* GET STUDENTS */
$getStudents = $connection->prepare("
SELECT s.student_id,
       CONCAT(sa.last_name, ', ', sa.first_name) AS student_name,
       ge.grade

FROM student_strand ss
JOIN students s ON ss.student_id = s.student_id
JOIN student_applications sa ON s.application_id = sa.application_id

JOIN student_subjects subj ON s.student_id = subj.student_id
AND subj.subject_id = ?

LEFT JOIN grade_entry ge
ON ge.student_id = s.student_id
AND ge.subject_id = ?
AND ge.section_id = ?
AND ge.quarter = ?

WHERE ss.section_id = ?
AND subj.status = 'Enrolled'

ORDER BY sa.last_name ASC
");

$getStudents->bind_param(
    "iiiii",
    $subject_id,
    $subject_id,
    $section_id,
    $quarter,
    $section_id
);

$getStudents->execute();
$result = $getStudents->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$getStudents->close();

?>