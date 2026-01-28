<?php
require_once 'db_connection.php';

if ($conn) {
    echo "<h2 style='color: green;'>✓ Database connection successful!</h2>";
    echo "<p>Connected to database: <strong>product_sales</strong></p>";
    echo "<p>Server info: " . $conn->server_info . "</p>";
} else {
    echo "<h2 style='color: red;'>✗ Connection failed</h2>";
}

$conn->close();
?>
