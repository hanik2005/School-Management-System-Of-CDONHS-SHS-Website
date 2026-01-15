<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['school_id'])) {
    include "../../DB_Connection/Connection.php";
    include '../../Back_End_Files/PHP_Files/User.php';
    //$user = getUserById($_SESSION['user_id'], $connection);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
</head>
<body>
    <a href="admin_teacher_application_list.php">Teacher Application List</a>
    <a href="admin_student_application_list.php">Student Application List</a>    
    <a href="../Teacher_Online_Form.php">Enrollment Teacher</a>
    <a href="../Student_Online_Form.php">Enrollment Student</a>
</body>
</html>
<?php }else {
        header("Location: ../login.php");
        exit;
    } ?>