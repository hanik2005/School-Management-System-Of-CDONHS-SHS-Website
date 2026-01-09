<?php 

function getUserById($id, $db){
    $sql = "SELECT * FROM users WHERE user_id = ?";
	$stmt = $db->prepare($sql);
	$stmt->execute([$id]);
    
    if($stmt->rowCount() == 1){
        $user = $stmt->fetch();
        return $user;
    }else {
        return 0;
    }
}
 

function getStudentUserById($id, $db){
    $sql = "SELECT 
                u.user_id,
                u.username,
                u.school_id,
                u.role_id,
                u.status,

                s.student_id,
                s.application_id,
                s.enrollment_status,
                s.date_enrolled,

                e.first_name,
                e.last_name,
                e.middle_name,
                e.extension_name,
                e.lrn,
                e.gender,
                e.date_of_birth,
                e.house_number_street,
                e.barangay,
                e.city_municipality,
                e.province,
                e.contact_number,
                e.email,
                e.facebook_profile
            FROM users u
            LEFT JOIN students s 
                ON u.school_id = s.school_id
            LEFT JOIN enrollment_applications e 
                ON s.application_id = e.application_id
            WHERE u.user_id = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id); // Bind the ID as integer
    $stmt->execute();
    $result = $stmt->get_result(); // Get result set

    if ($result->num_rows == 1) {
        return $result->fetch_assoc(); // Fetch as associative array
    } else {
        return null;
    }
}
function getTeacherUserById($id, $db){
    
}



 ?>