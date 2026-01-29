<?php
require_once '../db_connection.php';
$page_title = 'Certificate Generation';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_template') {
            $template_name = $_POST['template_name'];
            $template_type = $_POST['template_type'];
            $html_layout = $_POST['html_layout'];
            
            $stmt = $conn->prepare("INSERT INTO certificate_template (template_name, template_type, html_layout, version, status) VALUES (?, ?, ?, '1.0', 'active')");
            $stmt->bind_param("sss", $template_name, $template_type, $html_layout);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Created certificate template: $template_name (Type: $template_type)", 1);
                $message = '<div class="alert alert-success">Certificate template added!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'generate_certificate') {
            $template_id = $_POST['template_id'];
            $certificate_type = $_POST['certificate_type'];
            $linked_record_id = !empty($_POST['linked_record_id']) ? $_POST['linked_record_id'] : NULL;
            $linked_table = !empty($_POST['linked_table']) ? $_POST['linked_table'] : NULL;
            $patient_id = !empty($_POST['patient_id']) ? $_POST['patient_id'] : NULL;
            $equipment_id = !empty($_POST['equipment_id']) ? $_POST['equipment_id'] : NULL;
            $issued_by = $_POST['issued_by'];
            $verified_by = !empty($_POST['verified_by']) ? $_POST['verified_by'] : NULL;
            $certificate_data = $_POST['certificate_data'];
            
            // Generate unique certificate number
            $year = date('Y');
            $month = date('m');
            $last_cert = $conn->query("SELECT certificate_number FROM certificate WHERE certificate_number LIKE 'CERT-$year$month-%' ORDER BY certificate_id DESC LIMIT 1")->fetch_assoc();
            
            if ($last_cert) {
                $last_num = intval(substr($last_cert['certificate_number'], -4));
                $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $new_num = '0001';
            }
            
            $certificate_number = "CERT-$year$month-$new_num";
            
            $stmt = $conn->prepare("INSERT INTO certificate (certificate_number, template_id, certificate_type, linked_record_id, linked_table, patient_id, equipment_id, issue_date, issued_by, verified_by, status, certificate_data) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, 'draft', ?)");
            $stmt->bind_param("sissiiiiis", $certificate_number, $template_id, $certificate_type, $linked_record_id, $linked_table, $patient_id, $equipment_id, $issued_by, $verified_by, $certificate_data);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Generated certificate: $certificate_number (Type: $certificate_type)", 1);
                $message = '<div class="alert alert-success">Certificate generated! Certificate Number: ' . $certificate_number . '</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update_status') {
            $certificate_id = $_POST['certificate_id'];
            $new_status = $_POST['new_status'];
            $cert_info = $conn->query("SELECT certificate_number FROM certificate WHERE certificate_id = $certificate_id")->fetch_assoc();
            
            $conn->query("UPDATE certificate SET status = '$new_status' WHERE certificate_id = $certificate_id");
            log_activity($conn, get_user_id(), "Updated certificate {$cert_info['certificate_number']} status to $new_status", 1);
            $message = '<div class="alert alert-success">Certificate status updated to ' . strtoupper($new_status) . '</div>';
        }
    }
}

// Get templates
$templates = $conn->query("SELECT * FROM certificate_template WHERE status = 'active' ORDER BY template_name");

// Get certificates
$certificates = $conn->query("SELECT c.*, ct.template_name, e.firstname, e.lastname, p.firstname as patient_fname, p.lastname as patient_lname, eq.name as equipment_name
                             FROM certificate c
                             JOIN certificate_template ct ON c.template_id = ct.template_id
                             JOIN employee e ON c.issued_by = e.employee_id
                             LEFT JOIN patient p ON c.patient_id = p.patient_id
                             LEFT JOIN equipment eq ON c.equipment_id = eq.equipment_id
                             ORDER BY c.issue_date DESC");
?>

<div class="container">
    <?php echo $message; ?>
    
    <h1><i class="fas fa-certificate"></i> Certificate Generation & Management</h1>
    
    <!-- Certificate Templates -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> Certificate Templates</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddTemplateModal()" style="margin-bottom: 1rem;">Create New Template</button>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $templates->data_seek(0);
                while($tmpl = $templates->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tmpl['template_name']); ?></td>
                        <td><?php echo strtoupper($tmpl['template_type']); ?></td>
                        <td><?php echo htmlspecialchars($tmpl['version']); ?></td>
                        <td><?php echo strtoupper($tmpl['status']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="useTemplate(<?php echo $tmpl['template_id']; ?>, '<?php echo htmlspecialchars($tmpl['template_name']); ?>', '<?php echo $tmpl['template_type']; ?>')">Use Template</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Generated Certificates -->
    <div class="card">
        <div class="card-header">
            <h2>📑 Generated Certificates</h2>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Certificate #</th>
                    <th>Type</th>
                    <th>Patient/Equipment</th>
                    <th>Issue Date</th>
                    <th>Issued By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($cert = $certificates->fetch_assoc()): 
                    $subject = '';
                    if ($cert['patient_fname']) {
                        $subject = $cert['patient_fname'] . ' ' . $cert['patient_lname'];
                    } elseif ($cert['equipment_name']) {
                        $subject = $cert['equipment_name'];
                    } else {
                        $subject = 'N/A';
                    }
                    
                    $status_color = '';
                    if ($cert['status'] == 'issued') $status_color = 'style="color: green; font-weight: bold;"';
                    elseif ($cert['status'] == 'draft') $status_color = 'style="color: orange;"';
                    elseif ($cert['status'] == 'revoked') $status_color = 'style="color: red;"';
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cert['certificate_number']); ?></td>
                        <td><?php echo strtoupper($cert['certificate_type']); ?></td>
                        <td><?php echo htmlspecialchars($subject); ?></td>
                        <td><?php echo date('M d, Y', strtotime($cert['issue_date'])); ?></td>
                        <td><?php echo htmlspecialchars($cert['firstname'] . ' ' . $cert['lastname']); ?></td>
                        <td <?php echo $status_color; ?>><?php echo strtoupper($cert['status']); ?></td>
                        <td>
                            <a href="view_certificate.php?id=<?php echo $cert['certificate_id']; ?>" class="btn btn-sm btn-primary" target="_blank">View</a>
                            <?php if ($cert['status'] == 'draft'): ?>
                                <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $cert['certificate_id']; ?>, 'issued')">Issue</button>
                            <?php endif; ?>
                            <?php if ($cert['status'] == 'issued'): ?>
                                <button class="btn btn-sm btn-danger" onclick="updateStatus(<?php echo $cert['certificate_id']; ?>, 'revoked')">Revoke</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Template Modal -->
<div id="addTemplateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 800px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Create Certificate Template</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_template">
            
            <div class="form-group">
                <label>Template Name *</label>
                <input type="text" name="template_name" class="form-control" required placeholder="e.g., Lab Result Certificate">
            </div>
            
            <div class="form-group">
                <label>Template Type *</label>
                <select name="template_type" class="form-control" required>
                    <option value="lab_result">Lab Result</option>
                    <option value="calibration">Calibration</option>
                    <option value="compliance">Compliance</option>
                    <option value="training">Training</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>HTML Layout *</label>
                <textarea name="html_layout" class="form-control" rows="10" required placeholder="HTML template with placeholders: {{certificate_number}}, {{patient_name}}, {{issue_date}}, etc."><!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .certificate { border: 5px solid #4CAF50; padding: 30px; text-align: center; }
        h1 { color: #4CAF50; }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Certificate of Laboratory Result</h1>
        <p>Certificate Number: <strong>{{certificate_number}}</strong></p>
        <p>This certifies that <strong>{{patient_name}}</strong> has completed laboratory testing.</p>
        <p>Date: {{issue_date}}</p>
        <p>Issued by: {{issued_by}}</p>
    </div>
</body>
</html></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Create Template</button>
                <button type="button" class="btn btn-danger" onclick="closeAddTemplateModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Generate Certificate Modal -->
<div id="generateCertModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Generate Certificate</h3>
        <div id="template-info" style="margin-bottom: 1rem; padding: 1rem; background: #f0f0f0; border-radius: 5px;"></div>
        <form method="POST">
            <input type="hidden" name="action" value="generate_certificate">
            <input type="hidden" name="template_id" id="gen_template_id">
            <input type="hidden" name="certificate_type" id="gen_certificate_type">
            
            <div class="form-group">
                <label>Linked Record ID (Optional)</label>
                <input type="number" name="linked_record_id" class="form-control" placeholder="Lab Result ID, Calibration Record ID, etc.">
            </div>
            
            <div class="form-group">
                <label>Linked Table (Optional)</label>
                <input type="text" name="linked_table" class="form-control" placeholder="e.g., lab_result, calibration_record">
            </div>
            
            <div class="form-group">
                <label>Patient</label>
                <select name="patient_id" class="form-control">
                    <option value="">Select Patient (if applicable)</option>
                    <?php 
                    $patients = $conn->query("SELECT * FROM patient ORDER BY lastname, firstname");
                    while($pat = $patients->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $pat['patient_id']; ?>"><?php echo htmlspecialchars($pat['firstname'] . ' ' . $pat['lastname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Equipment</label>
                <select name="equipment_id" class="form-control">
                    <option value="">Select Equipment (if applicable)</option>
                    <?php 
                    $equipment = $conn->query("SELECT * FROM equipment");
                    while($eq = $equipment->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $eq['equipment_id']; ?>"><?php echo htmlspecialchars($eq['name'] . ' (' . $eq['model'] . ')'); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Issued By *</label>
                <select name="issued_by" class="form-control" required>
                    <option value="">Select Employee</option>
                    <?php 
                    $employees = $conn->query("SELECT * FROM employee WHERE status_code = 1");
                    while($emp = $employees->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Verified By</label>
                <select name="verified_by" class="form-control">
                    <option value="">Select Employee</option>
                    <?php 
                    $employees = $conn->query("SELECT * FROM employee WHERE status_code = 1");
                    while($emp = $employees->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Certificate Data (JSON)</label>
                <textarea name="certificate_data" class="form-control" rows="5" placeholder='{"test_results": "Normal", "notes": "All parameters within range"}'>{"data": "Additional certificate data in JSON format"}</textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Generate Certificate</button>
                <button type="button" class="btn btn-danger" onclick="closeGenerateCertModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Status Form (hidden) -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="certificate_id" id="status_cert_id">
    <input type="hidden" name="new_status" id="status_new_status">
</form>

<script>
function showAddTemplateModal() {
    document.getElementById('addTemplateModal').style.display = 'block';
}

function closeAddTemplateModal() {
    document.getElementById('addTemplateModal').style.display = 'none';
}

function useTemplate(templateId, templateName, templateType) {
    document.getElementById('gen_template_id').value = templateId;
    document.getElementById('gen_certificate_type').value = templateType;
    document.getElementById('template-info').innerHTML = '<strong>Template:</strong> ' + templateName + '<br><strong>Type:</strong> ' + templateType.toUpperCase();
    document.getElementById('generateCertModal').style.display = 'block';
}

function closeGenerateCertModal() {
    document.getElementById('generateCertModal').style.display = 'none';
}

function updateStatus(certificateId, newStatus) {
    if (confirm('Are you sure you want to change status to ' + newStatus.toUpperCase() + '?')) {
        document.getElementById('status_cert_id').value = certificateId;
        document.getElementById('status_new_status').value = newStatus;
        document.getElementById('statusForm').submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
