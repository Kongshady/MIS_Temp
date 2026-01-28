<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Clinical Laboratory Management System'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <h1>🏥 Clinical Laboratory Management System</h1>
        <ul class="nav-menu">
            <li><a href="../modules/patients.php">Patient Profile</a></li>
            <li><a href="../modules/physicians.php">Add Physician</a></li>
            <li><a href="../modules/employees.php">Users/Employees</a></li>
            <li><a href="../modules/items.php">Items</a></li>
            <li><a href="../modules/sections.php">Sections</a></li>
            <li><a href="../modules/tests.php">Tests</a></li>
            <li><a href="../modules/transactions.php">Transactions</a></li>
            <li><a href="../modules/lab_results.php">Lab Results</a></li>
            <li><a href="../modules/inventory.php">Inventory</a></li>
            <li><a href="../modules/equipment.php">Equipment</a></li>
            <li><a href="../modules/calibration.php">Calibration</a></li>
            <li><a href="../modules/certificates.php">Certificates</a></li>
            <li><a href="../modules/logs.php">Activity Logs</a></li>
            <li><a href="../reports/compliance_reports.php">Reports</a></li>
        </ul>
    </nav>
