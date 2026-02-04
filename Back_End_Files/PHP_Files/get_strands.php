<?php
header('Content-Type: application/json');
include "../../DB_Connection/Connection.php";

try {
    // Prepare the statement
    $stmt = $connection->prepare("SELECT strand_id, strand_name FROM strands ORDER BY strand_name");
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Fetch all rows as associative array
    $strands = [];
    while ($row = $result->fetch_assoc()) {
        $strands[] = $row;
    }

    echo json_encode($strands);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
