<?php
require_once '../db_connection.php';

header('Content-Type: application/json');

$test_id = isset($_GET['test_id']) ? intval($_GET['test_id']) : 0;

if (!$test_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid test ID']);
    exit;
}

// Get price history with employee names
$query = $conn->prepare("
    SELECT 
        ph.previous_price,
        ph.new_price,
        ph.updated_at,
        CONCAT(e.firstname, ' ', e.lastname) as updated_by
    FROM test_price_history ph
    LEFT JOIN employee e ON ph.updated_by = e.employee_id
    WHERE ph.test_id = ?
    ORDER BY ph.updated_at DESC
");

$query->bind_param("i", $test_id);
$query->execute();
$result = $query->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = [
        'previous_price' => $row['previous_price'],
        'new_price' => $row['new_price'],
        'updated_at' => date('M d, Y h:i A', strtotime($row['updated_at'])),
        'updated_by' => $row['updated_by'] ?? 'Unknown'
    ];
}

echo json_encode([
    'success' => true,
    'history' => $history
]);
?>
