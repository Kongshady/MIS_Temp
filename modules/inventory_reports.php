<?php
require_once '../db_connection.php';
$page_title = 'Inventory Reports';
include '../includes/header.php';

// Get filter parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'movements';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : '';
$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : '';
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';

// Get sections for filter
$sections = $conn->query("SELECT * FROM section ORDER BY label");
$items = $conn->query("SELECT * FROM item ORDER BY label");
$employees = $conn->query("SELECT * FROM employee WHERE status_code = 1 ORDER BY firstname");
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📊 Inventory Reports</h2>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <a href="inventory.php" class="btn btn-secondary">← Back to Inventory</a>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
            <button onclick="exportToCSV()" class="btn btn-success">📥 Export CSV</button>
        </div>
        
        <!-- Report Type Selection -->
        <form method="GET" style="background: #f5f5f5; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Report Type</label>
                    <select name="report_type" class="form-control" onchange="this.form.submit()">
                        <option value="movements" <?php echo $report_type == 'movements' ? 'selected' : ''; ?>>Stock Movements</option>
                        <option value="low_stock" <?php echo $report_type == 'low_stock' ? 'selected' : ''; ?>>Low Stock / Out of Stock</option>
                        <option value="usage_section" <?php echo $report_type == 'usage_section' ? 'selected' : ''; ?>>Usage by Section</option>
                        <option value="usage_item" <?php echo $report_type == 'usage_item' ? 'selected' : ''; ?>>Usage by Item</option>
                        <option value="usage_employee" <?php echo $report_type == 'usage_employee' ? 'selected' : ''; ?>>Usage by Employee</option>
                        <option value="expiry" <?php echo $report_type == 'expiry' ? 'selected' : ''; ?>>Expiry Report</option>
                        <option value="supplier" <?php echo $report_type == 'supplier' ? 'selected' : ''; ?>>Stock by Supplier</option>
                    </select>
                </div>
                
                <?php if (in_array($report_type, ['movements', 'usage_section', 'usage_item', 'usage_employee'])): ?>
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                <?php endif; ?>
                
                <?php if (in_array($report_type, ['movements', 'usage_section'])): ?>
                <div class="form-group">
                    <label>Section</label>
                    <select name="section_id" class="form-control">
                        <option value="">All Sections</option>
                        <?php 
                        $sections->data_seek(0);
                        while($section = $sections->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $section['section_id']; ?>" <?php echo $section_id == $section['section_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section['label']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($report_type, ['movements', 'usage_item'])): ?>
                <div class="form-group">
                    <label>Item</label>
                    <select name="item_id" class="form-control">
                        <option value="">All Items</option>
                        <?php 
                        $items->data_seek(0);
                        while($item = $items->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $item['item_id']; ?>" <?php echo $item_id == $item['item_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['label']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if ($report_type == 'usage_employee'): ?>
                <div class="form-group">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        <?php 
                        $employees->data_seek(0);
                        while($emp = $employees->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $emp['employee_id']; ?>" <?php echo $employee_id == $emp['employee_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Generate Report</button>
        </form>
    </div>

    <!-- Report Display -->
    <?php
    $report_title = '';
    $report_query = '';
    
    switch ($report_type) {
        case 'movements':
            $report_title = 'Stock Movement Report';
            $where_clauses = ["datetime_added BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'"];
            if ($section_id) $where_clauses[] = "section_name = (SELECT label FROM section WHERE section_id = $section_id)";
            if ($item_id) $where_clauses[] = "item_id = $item_id";
            $where_sql = implode(' AND ', $where_clauses);
            
            $report_query = "SELECT * FROM v_stock_movements WHERE $where_sql ORDER BY datetime_added DESC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Period: <?php echo date('M d, Y', strtotime($date_from)); ?> to <?php echo date('M d, Y', strtotime($date_to)); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Quantity</th>
                            <th>Performed By</th>
                            <th>Supplier</th>
                            <th>Reference</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_in = 0;
                        $total_out = 0;
                        while($row = $result->fetch_assoc()): 
                            if ($row['movement_type'] == 'IN') $total_in += $row['quantity'];
                            else $total_out += $row['quantity'];
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['datetime_added'])); ?></td>
                                <td>
                                    <?php if ($row['movement_type'] == 'IN'): ?>
                                        <span class="badge badge-success">STOCK IN</span>
                                    <?php elseif ($row['movement_type'] == 'OUT'): ?>
                                        <span class="badge badge-danger">STOCK OUT</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">USAGE</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($row['performed_by_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['supplier'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['reference_number'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['remarks'] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background: #f5f5f5;">
                            <td colspan="4">TOTALS:</td>
                            <td colspan="5">Total In: <?php echo $total_in; ?> | Total Out: <?php echo $total_out; ?> | Net: <?php echo ($total_in - $total_out); ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php else: ?>
                    <p>No movements found for the selected criteria.</p>
                <?php endif; ?>
            </div>
            <?php
            break;
            
        case 'low_stock':
            $report_title = 'Low Stock / Out of Stock Report';
            $report_query = "SELECT * FROM v_current_stock WHERE stock_status IN ('out_of_stock', 'low_stock') ORDER BY current_stock ASC, stock_status DESC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Generated: <?php echo date('M d, Y h:i A'); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Action Needed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="<?php echo $row['stock_status'] == 'out_of_stock' ? 'background: #ffcdd2;' : 'background: #ffe0b2;'; ?>">
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><strong><?php echo $row['current_stock']; ?></strong></td>
                                <td><?php echo $row['reorder_level']; ?></td>
                                <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td>
                                    <?php if ($row['stock_status'] == 'out_of_stock'): ?>
                                        <span class="badge badge-danger">OUT OF STOCK</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">LOW STOCK</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['stock_status'] == 'out_of_stock' ? 'URGENT RESTOCK' : 'Restock Soon'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color: green; font-weight: bold;">✓ All items are adequately stocked!</p>
                <?php endif; ?>
            </div>
            <?php
            break;
            
        case 'usage_section':
            $report_title = 'Usage by Section';
            $where_clauses = ["su.datetime_added BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'"];
            if ($section_id) $where_clauses[] = "i.section_id = $section_id";
            $where_sql = implode(' AND ', $where_clauses);
            
            $report_query = "SELECT s.label as section_name, i.label as item_name, 
                            SUM(su.quantity) as total_used,
                            COUNT(su.stock_usage_id) as usage_count
                            FROM stock_usage su
                            JOIN item i ON su.item_id = i.item_id
                            LEFT JOIN section s ON i.section_id = s.section_id
                            WHERE $where_sql
                            GROUP BY s.section_id, i.item_id
                            ORDER BY s.label, total_used DESC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Period: <?php echo date('M d, Y', strtotime($date_from)); ?> to <?php echo date('M d, Y', strtotime($date_to)); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Item</th>
                            <th>Total Quantity Used</th>
                            <th>Number of Usages</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><strong><?php echo $row['total_used']; ?></strong></td>
                                <td><?php echo $row['usage_count']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No usage records found for the selected period.</p>
                <?php endif; ?>
            </div>
            <?php
            break;
            
        case 'usage_item':
            $report_title = 'Usage by Item';
            $where_clauses = ["su.datetime_added BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'"];
            if ($item_id) $where_clauses[] = "su.item_id = $item_id";
            $where_sql = implode(' AND ', $where_clauses);
            
            $report_query = "SELECT i.label as item_name, s.label as section_name,
                            SUM(su.quantity) as total_used,
                            COUNT(DISTINCT su.employee_id) as unique_users,
                            COUNT(su.stock_usage_id) as usage_count
                            FROM stock_usage su
                            JOIN item i ON su.item_id = i.item_id
                            LEFT JOIN section s ON i.section_id = s.section_id
                            WHERE $where_sql
                            GROUP BY su.item_id
                            ORDER BY total_used DESC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Period: <?php echo date('M d, Y', strtotime($date_from)); ?> to <?php echo date('M d, Y', strtotime($date_to)); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Total Quantity Used</th>
                            <th>Unique Users</th>
                            <th>Usage Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><strong><?php echo $row['total_used']; ?></strong></td>
                                <td><?php echo $row['unique_users']; ?></td>
                                <td><?php echo $row['usage_count']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No usage records found for the selected period.</p>
                <?php endif; ?>
            </div>
            <?php
            break;
            
        case 'usage_employee':
            $report_title = 'Usage by Employee';
            $where_clauses = ["su.datetime_added BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'"];
            if ($employee_id) $where_clauses[] = "su.employee_id = $employee_id";
            $where_sql = implode(' AND ', $where_clauses);
            
            $report_query = "SELECT CONCAT(su.firstname, ' ', su.lastname) as employee_name,
                            i.label as item_name,
                            s.label as section_name,
                            su.quantity,
                            su.purpose,
                            su.or_number,
                            su.datetime_added
                            FROM stock_usage su
                            JOIN item i ON su.item_id = i.item_id
                            LEFT JOIN section s ON i.section_id = s.section_id
                            WHERE $where_sql
                            ORDER BY su.datetime_added DESC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Period: <?php echo date('M d, Y', strtotime($date_from)); ?> to <?php echo date('M d, Y', strtotime($date_to)); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Quantity</th>
                            <th>Purpose</th>
                            <th>OR Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['datetime_added'])); ?></td>
                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><?php echo htmlspecialchars($row['or_number']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No usage records found for the selected period.</p>
                <?php endif; ?>
            </div>
            <?php
            break;
            
        case 'expiry':
            $report_title = 'Expiry Report';
            $report_query = "SELECT * FROM v_stock_expiry ORDER BY expiry_date ASC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Generated: <?php echo date('M d, Y h:i A'); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Days Until Expiry</th>
                            <th>Supplier</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="<?php echo $row['expiry_status'] == 'expired' ? 'background: #ffcdd2;' : ($row['expiry_status'] == 'expiring_soon' ? 'background: #fff9c4;' : ''); ?>">
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></td>
                                <td><?php echo $row['days_until_expiry']; ?> days</td>
                                <td><?php echo htmlspecialchars($row['supplier'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($row['expiry_status'] == 'expired'): ?>
                                        <span class="badge badge-danger">EXPIRED</span>
                                    <?php elseif ($row['expiry_status'] == 'expiring_soon'): ?>
                                        <span class="badge badge-warning">EXPIRING SOON</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">VALID</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No items with expiry dates found.</p>
                <?php endif; ?>
            </div>
            <?php
            break;
            
        case 'supplier':
            $report_title = 'Stock by Supplier';
            $report_query = "SELECT supplier, 
                            COUNT(DISTINCT item_id) as item_count,
                            SUM(quantity) as total_quantity,
                            MAX(datetime_added) as last_delivery,
                            GROUP_CONCAT(DISTINCT reference_number) as references
                            FROM stock_in
                            WHERE supplier IS NOT NULL
                            GROUP BY supplier
                            ORDER BY total_quantity DESC";
            $result = $conn->query($report_query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $report_title; ?></h3>
                    <p>Generated: <?php echo date('M d, Y h:i A'); ?></p>
                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Distinct Items</th>
                            <th>Total Quantity Supplied</th>
                            <th>Last Delivery</th>
                            <th>Reference Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                                <td><?php echo $row['item_count']; ?></td>
                                <td><?php echo $row['total_quantity']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['last_delivery'])); ?></td>
                                <td><?php echo htmlspecialchars($row['references']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No supplier information found.</p>
                <?php endif; ?>
            </div>
            <?php
            break;
    }
    ?>
</div>

<script>
function exportToCSV() {
    var table = document.getElementById('reportTable');
    if (!table) {
        alert('No report data to export');
        return;
    }
    
    var csv = [];
    var rows = table.querySelectorAll('tr');
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (var j = 0; j < cols.length; j++) {
            var cellText = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellText + '"');
        }
        
        csv.push(row.join(','));
    }
    
    var csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    var downloadLink = document.createElement('a');
    downloadLink.download = 'inventory_report_' + new Date().toISOString().slice(0,10) + '.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Print styles
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.btn, form').forEach(el => el.style.display = 'none');
});

window.addEventListener('afterprint', function() {
    document.querySelectorAll('.btn, form').forEach(el => el.style.display = '');
});
</script>

<?php include '../includes/footer.php'; ?>
