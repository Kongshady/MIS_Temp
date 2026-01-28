<?php
require_once '../db_connection.php';

$certificate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get certificate details
$cert = $conn->query("SELECT c.*, ct.html_layout, e.firstname as issuer_fname, e.lastname as issuer_lname,
                      v.firstname as verifier_fname, v.lastname as verifier_lname,
                      p.firstname as patient_fname, p.lastname as patient_lname,
                      eq.name as equipment_name, eq.model as equipment_model
                      FROM certificate c
                      JOIN certificate_template ct ON c.template_id = ct.template_id
                      JOIN employee e ON c.issued_by = e.employee_id
                      LEFT JOIN employee v ON c.verified_by = v.employee_id
                      LEFT JOIN patient p ON c.patient_id = p.patient_id
                      LEFT JOIN equipment eq ON c.equipment_id = eq.equipment_id
                      WHERE c.certificate_id = $certificate_id")->fetch_assoc();

if (!$cert) {
    die('Certificate not found');
}

// Prepare replacements
$patient_name = $cert['patient_fname'] ? ($cert['patient_fname'] . ' ' . $cert['patient_lname']) : 'N/A';
$equipment_info = $cert['equipment_name'] ? ($cert['equipment_name'] . ' (' . $cert['equipment_model'] . ')') : 'N/A';
$issued_by = $cert['issuer_fname'] . ' ' . $cert['issuer_lname'];
$verified_by = $cert['verifier_fname'] ? ($cert['verifier_fname'] . ' ' . $cert['verifier_lname']) : 'N/A';
$issue_date = date('F d, Y', strtotime($cert['issue_date']));

// Get certificate data
$cert_data = json_decode($cert['certificate_data'], true);

// Replace placeholders in template
$html = $cert['html_layout'];
$html = str_replace('{{certificate_number}}', $cert['certificate_number'], $html);
$html = str_replace('{{patient_name}}', $patient_name, $html);
$html = str_replace('{{equipment_info}}', $equipment_info, $html);
$html = str_replace('{{issue_date}}', $issue_date, $html);
$html = str_replace('{{issued_by}}', $issued_by, $html);
$html = str_replace('{{verified_by}}', $verified_by, $html);
$html = str_replace('{{status}}', strtoupper($cert['status']), $html);

// Replace custom data fields
if ($cert_data && is_array($cert_data)) {
    foreach ($cert_data as $key => $value) {
        $html = str_replace('{{' . $key . '}}', $value, $html);
    }
}

// Add watermark for draft/revoked
if ($cert['status'] != 'issued') {
    $watermark = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100px; color: rgba(255, 0, 0, 0.2); z-index: 9999; pointer-events: none;">' . strtoupper($cert['status']) . '</div>';
    $html = str_replace('</body>', $watermark . '</body>', $html);
}

// Output the certificate
echo $html;

// Add print button
?>
<div style="text-align: center; margin-top: 30px; page-break-before: always;">
    <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">Print Certificate</button>
    <button onclick="window.close()" style="padding: 10px 30px; font-size: 16px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Close</button>
</div>

<style>
@media print {
    button {
        display: none !important;
    }
}
</style>
