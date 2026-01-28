<?php
require_once '../db_connection.php';

header('Content-Type: application/json');

$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

if ($item_id) {
    $query = "SELECT 
                i.item_id,
                i.label as item_name,
                i.unit,
                COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock
              FROM item i
              LEFT JOIN stock_in si ON i.item_id = si.item_id
              LEFT JOIN stock_out so ON i.item_id = so.item_id
              WHERE i.item_id = $item_id
              GROUP BY i.item_id";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['current_stock' => 0, 'unit' => 'pcs']);
    }
} else {
    echo json_encode(['error' => 'Invalid item ID']);
}
