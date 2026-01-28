<?php
require_once '../db_connection.php';
$page_title = 'Audit & Compliance Reports';
include '../includes/header.php';

// Get filter parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : '';
$equipment_id = isset($_GET['equipment_id']) ? $_GET['equipment_id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$export = isset($_GET['export']) ? $_GET['export'] : '';

// Generate report based on type
$report_data = null;
$report_title = '';

if ($report_type) {
    switch ($report_type) {
        case 'maintenance':
            $report_title = 'Equipment Maintenance Report';
            $query = "SELECT mh.*, e.name as equipment_name, e.model, emp.firstname, emp.lastname, s.label as section_name
                     FROM maintenance_history mh
                     JOIN equipment e ON mh.equipment_id = e.equipment_id
                     JOIN employee emp ON mh.performed_by = emp.employee_id
                     LEFT JOIN section s ON e.section_id = s.section_id
                     WHERE mh.maintenance_date BETWEEN '$start_date' AND '$end_date'";
            
            if ($equipment_id) $query .= " AND mh.equipment_id = $equipment_id";
            if ($section_id) $query .= " AND e.section_id = $section_id";
            if ($status) $query .= " AND mh.maintenance_type = '$status'";
            
            $query .= " ORDER BY mh.maintenance_date DESC";
            $report_data = $conn->query($query);
            break;
            
        case 'calibration':
            $report_title = 'Calibration Records Report';
            $query = "SELECT cr.*, e.name as equipment_name, e.model, cp.procedure_name, emp.firstname, emp.lastname
                     FROM calibration_record cr
                     JOIN equipment e ON cr.equipment_id = e.equipment_id
                     JOIN calibration_procedure cp ON cr.procedure_id = cp.procedure_id
                     JOIN employee emp ON cr.performed_by = emp.employee_id
                     WHERE cr.calibration_date BETWEEN '$start_date' AND '$end_date'";
            
            if ($equipment_id) $query .= " AND cr.equipment_id = $equipment_id";
            if ($status) $query .= " AND cr.result_status = '$status'";
            
            $query .= " ORDER BY cr.calibration_date DESC";
            $report_data = $conn->query($query);
            break;
            
        case 'inventory':
            $report_title = 'Inventory Movement Report';
            $query = "(SELECT 'Stock In' as movement_type, i.label as item_name, s.label as section_name, si.quantity, '' as remarks, si.datetime_added as movement_date
                      FROM stock_in si
                      JOIN item i ON si.item_id = i.item_id
                      LEFT JOIN section s ON i.section_id = s.section_id
                      WHERE DATE(si.datetime_added) BETWEEN '$start_date' AND '$end_date'";
            
            if ($section_id) $query .= " AND i.section_id = $section_id";
            
            $query .= ")
                      UNION ALL
                      (SELECT 'Stock Out' as movement_type, i.label as item_name, s.label as section_name, so.quantity, so.remarks, so.datetime_added as movement_date
                      FROM stock_out so
                      JOIN item i ON so.item_id = i.item_id
                      LEFT JOIN section s ON i.section_id = s.section_id
                      WHERE DATE(so.datetime_added) BETWEEN '$start_date' AND '$end_date'";
            
            if ($section_id) $query .= " AND i.section_id = $section_id";
            
            $query .= ")
                      ORDER BY movement_date DESC";
            $report_data = $conn->query($query);
            break;
            
        case 'low_stock':
            $report_title = 'Low Stock & Out of Stock Report';
            $query = "SELECT * FROM v_current_stock WHERE stock_status IN ('out_of_stock', 'low_stock')";
            
            if ($section_id) {
                $query .= " AND section_id = $section_id";
            }
            
            $query .= " ORDER BY current_stock ASC";
            $report_data = $conn->query($query);
            break;
            
        case 'lab_results':
            $report_title = 'Laboratory Results Report';
            $query = "SELECT lr.*, p.firstname, p.lastname, p.patient_type, e.firstname as emp_fname, e.lastname as emp_lname, t.label as test_name
                     FROM lab_result lr
                     JOIN patient p ON lr.patient_id = p.patient_id
                     JOIN employee e ON lr.performed_by = e.employee_id
                     JOIN test t ON lr.test_id = t.test_id
                     WHERE DATE(lr.result_date) BETWEEN '$start_date' AND '$end_date'";
            
            if ($status) $query .= " AND lr.result_status = '$status'";
            
            $query .= " ORDER BY lr.result_date DESC";
            $report_data = $conn->query($query);
            break;
    }
}

// Handle CSV export
if ($export == 'csv' && $report_data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $report_title) . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write headers based on report type
    $headers = array();
    if ($report_type == 'maintenance') {
        $headers = array('Date', 'Equipment', 'Model', 'Type', 'Performed By', 'Section', 'Notes');
    } elseif ($report_type == 'calibration') {
        $headers = array('Date', 'Equipment', 'Model', 'Procedure', 'Performed By', 'Result', 'Notes');
    } elseif ($report_type == 'inventory') {
        $headers = array('Date', 'Movement Type', 'Item', 'Section', 'Quantity', 'Remarks');
    } elseif ($report_type == 'low_stock') {
        $headers = array('Item', 'Section', 'Unit', 'Current Stock', 'Reorder Level', 'Status');
    } elseif ($report_type == 'lab_results') {
        $headers = array('Date', 'Patient Name', 'Patient Type', 'Test', 'Result Value', 'Unit', 'Performed By', 'Status');
    }
    
    fputcsv($output, $headers);
    
    // Write data
    while ($row = $report_data->fetch_assoc()) {
        $data = array();
        if ($report_type == 'maintenance') {
            $data = array(
                $row['maintenance_date'],
                $row['equipment_name'],
                $row['model'],
                $row['maintenance_type'],
                $row['firstname'] . ' ' . $row['lastname'],
                $row['section_name'],
                $row['notes']
            );
        } elseif ($report_type == 'calibration') {
            $data = array(
                $row['calibration_date'],
                $row['equipment_name'],
                $row['model'],
                $row['procedure_name'],
                $row['firstname'] . ' ' . $row['lastname'],
                $row['result_status'],
                $row['notes']
            );
        } elseif ($report_type == 'inventory') {
            $data = array(
                $row['movement_date'],
                $row['movement_type'],
                $row['item_name'],
                $row['section_name'],
                $row['quantity'],
                $row['remarks']
            );
        } elseif ($report_type == 'low_stock') {
            $data = array(
                $row['item_name'],
                $row['section_name'],
                $row['unit'],
                $row['current_stock'],
                $row['reorder_level'],
                $row['stock_status']
            );
        } elseif ($report_type == 'lab_results') {
            $data = array(
                $row['result_date'],
                $row['firstname'] . ' ' . $row['lastname'],
                $row['patient_type'],
                $row['test_name'],
                $row['result_value'],
                $row['result_unit'],
                $row['emp_fname'] . ' ' . $row['emp_lname'],
                $row['result_status']
            );
        }
        fputcsv($output, $data);
    }
    
    fclose($output);
    exit;
}
?>

<div class="container">
    <h1>📊 Audit & Compliance Reports</h1>
    
    <!-- Report Generator -->
    <div class="card">
        <div class="card-header">
            <h2>🔍 Generate Report</h2>
        </div>
        
        <form method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Report Type *</label>
                    <select name="report_type" class="form-control" required onchange="updateFilters(this.value)">
                        <option value="">Select Report Type</option>
                        <option value="maintenance" <?php echo $report_type == 'maintenance' ? 'selected' : ''; ?>>Equipment Maintenance</option>
                        <option value="calibration" <?php echo $report_type == 'calibration' ? 'selected' : ''; ?>>Calibration Records</option>
                        <option value="inventory" <?php echo $report_type == 'inventory' ? 'selected' : ''; ?>>Inventory Movement</option>
                        <option value="low_stock" <?php echo $report_type == 'low_stock' ? 'selected' : ''; ?>>Low Stock Alert</option>
                        <option value="lab_results" <?php echo $report_type == 'lab_results' ? 'selected' : ''; ?>>Laboratory Results</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>End Date *</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
                </div>
                
                <div class="form-group" id="section_filter">
                    <label>Section (Optional)</label>
                    <select name="section_id" class="form-control">
                        <option value="">All Sections</option>
                        <?php 
                        $sections = $conn->query("SELECT * FROM section");
                        while($sec = $sections->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $sec['section_id']; ?>" <?php echo $section_id == $sec['section_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec['label']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group" id="equipment_filter" style="display: none;">
                    <label>Equipment (Optional)</label>
                    <select name="equipment_id" class="form-control">
                        <option value="">All Equipment</option>
                        <?php 
                        $equipment = $conn->query("SELECT * FROM equipment");
                        while($eq = $equipment->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $eq['equipment_id']; ?>" <?php echo $equipment_id == $eq['equipment_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($eq['name'] . ' (' . $eq['model'] . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group" id="status_filter" style="display: none;">
                    <label>Status (Optional)</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pass" <?php echo $status == 'pass' ? 'selected' : ''; ?>>Pass</option>
                        <option value="fail" <?php echo $status == 'fail' ? 'selected' : ''; ?>>Fail</option>
                        <option value="conditional" <?php echo $status == 'conditional' ? 'selected' : ''; ?>>Conditional</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <?php if ($report_data): ?>
                    <button type="button" onclick="exportReport('csv')" class="btn btn-success">Export to CSV</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Report Results -->
    <?php if ($report_data && $report_data->num_rows > 0): ?>
    <div class="card">
        <div class="card-header">
            <h2><?php echo $report_title; ?></h2>
            <p>Period: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></p>
            <p>Total Records: <strong><?php echo $report_data->num_rows; ?></strong></p>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <?php if ($report_type == 'maintenance'): ?>
                            <th>Date</th>
                            <th>Equipment</th>
                            <th>Model</th>
                            <th>Type</th>
                            <th>Performed By</th>
                            <th>Section</th>
                            <th>Notes</th>
                        <?php elseif ($report_type == 'calibration'): ?>
                            <th>Date</th>
                            <th>Equipment</th>
                            <th>Model</th>
                            <th>Procedure</th>
                            <th>Performed By</th>
                            <th>Result</th>
                            <th>Notes</th>
                        <?php elseif ($report_type == 'inventory'): ?>
                            <th>Date</th>
                            <th>Movement Type</th>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Quantity</th>
                            <th>Remarks</th>
                        <?php elseif ($report_type == 'low_stock'): ?>
                            <th>Item</th>
                            <th>Section</th>
                            <th>Unit</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                        <?php elseif ($report_type == 'lab_results'): ?>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Test</th>
                            <th>Result</th>
                            <th>Unit</th>
                            <th>Performed By</th>
                            <th>Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $report_data->data_seek(0);
                    while($row = $report_data->fetch_assoc()): 
                    ?>
                        <tr>
                            <?php if ($report_type == 'maintenance'): ?>
                                <td><?php echo date('M d, Y', strtotime($row['maintenance_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['model']); ?></td>
                                <td><?php echo strtoupper($row['maintenance_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                            <?php elseif ($report_type == 'calibration'): ?>
                                <td><?php echo date('M d, Y', strtotime($row['calibration_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['model']); ?></td>
                                <td><?php echo htmlspecialchars($row['procedure_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                <td style="<?php echo $row['result_status'] == 'pass' ? 'color: green;' : ($row['result_status'] == 'fail' ? 'color: red;' : 'color: orange;'); ?> font-weight: bold;">
                                    <?php echo strtoupper($row['result_status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                            <?php elseif ($report_type == 'inventory'): ?>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['movement_date'])); ?></td>
                                <td style="<?php echo $row['movement_type'] == 'Stock In' ? 'color: green;' : 'color: red;'; ?> font-weight: bold;">
                                    <?php echo $row['movement_type']; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                            <?php elseif ($report_type == 'low_stock'): ?>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td style="font-weight: bold;"><?php echo $row['current_stock']; ?></td>
                                <td><?php echo $row['reorder_level']; ?></td>
                                <td style="<?php echo $row['stock_status'] == 'out_of_stock' ? 'color: red;' : 'color: orange;'; ?> font-weight: bold;">
                                    <?php echo strtoupper(str_replace('_', ' ', $row['stock_status'])); ?>
                                </td>
                            <?php elseif ($report_type == 'lab_results'): ?>
                                <td><?php echo date('M d, Y', strtotime($row['result_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                <td><?php echo strtoupper($row['patient_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['result_value']); ?></td>
                                <td><?php echo htmlspecialchars($row['result_unit']); ?></td>
                                <td><?php echo htmlspecialchars($row['emp_fname'] . ' ' . $row['emp_lname']); ?></td>
                                <td><?php echo strtoupper($row['result_status']); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php elseif ($report_data): ?>
    <div class="card">
        <p>No records found for the selected filters.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function updateFilters(reportType) {
    const equipmentFilter = document.getElementById('equipment_filter');
    const statusFilter = document.getElementById('status_filter');
    
    // Reset visibility
    equipmentFilter.style.display = 'none';
    statusFilter.style.display = 'none';
    
    // Show relevant filters
    if (reportType == 'maintenance' || reportType == 'calibration') {
        equipmentFilter.style.display = 'block';
    }
    
    if (reportType == 'calibration' || reportType == 'lab_results') {
        statusFilter.style.display = 'block';
        
        // Update status options based on report type
        if (reportType == 'lab_results') {
            statusFilter.querySelector('select').innerHTML = 
                '<option value="">All Statuses</option>' +
                '<option value="draft">Draft</option>' +
                '<option value="final">Final</option>' +
                '<option value="revised">Revised</option>';
        } else {
            statusFilter.querySelector('select').innerHTML = 
                '<option value="">All Statuses</option>' +
                '<option value="pass">Pass</option>' +
                '<option value="fail">Fail</option>' +
                '<option value="conditional">Conditional</option>';
        }
    }
}

function exportReport(format) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('export', format);
    window.location.href = currentUrl.toString();
}

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.querySelector('select[name="report_type"]').value;
    if (reportType) {
        updateFilters(reportType);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
