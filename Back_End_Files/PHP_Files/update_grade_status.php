<?php
include "../../DB_Connection/Connection.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!$data || !isset($data['updates'])){
    echo json_encode(['success'=>false,'message'=>'No data received']);
    exit;
}

foreach($data['updates'] as $item){
    $stmt = $connection->prepare("
        UPDATE grade_entry ge
        JOIN section sec ON sec.section_id = ge.section_id
        SET ge.grade_status = ?
        WHERE sec.grade_level = ?
        AND sec.section_name = ?
        AND ge.quarter = ?
    ");
    $stmt->bind_param("sisi", $item['status'], $item['grade'], $item['section'], $item['quarter']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success'=>true,'message'=>'Grades updated successfully']);
