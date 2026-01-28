<?php
require_once 'db_connection.php';

echo "<h2>Database Connection Test</h2>";
echo "<p>Connected to database: <strong>clinlab</strong></p>";

// Check if database exists and get all tables
$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "<h3>Tables in 'clinlab' database:</h3>";
    if ($result->num_rows > 0) {
        echo "<ol>";
        while($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ol>";
        echo "<p style='color: green;'>Total tables found: " . $result->num_rows . "</p>";
    } else {
        echo "<p style='color: red;'>No tables found in the database. You need to create the tables first.</p>";
    }
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
}

// Show expected tables based on ERD
echo "<hr>";
echo "<h3>Expected tables based on your ERD:</h3>";
$expected_tables = [
    'walk_in',
    'client',
    'client_type',
    'item_type',
    'transaction',
    'transaction_detail',
    'section',
    'activity_log',
    'employee',
    'blood_chemistry',
    'item',
    'test',
    'status_code',
    'stock_in',
    'stock_out',
    'stock_usage',
    'fecalysis'
];

echo "<ol>";
foreach($expected_tables as $table) {
    // Check if table exists
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        echo "<li style='color: green;'>✓ $table - EXISTS</li>";
    } else {
        echo "<li style='color: red;'>✗ $table - MISSING</li>";
    }
}
echo "</ol>";

$conn->close();
?>
