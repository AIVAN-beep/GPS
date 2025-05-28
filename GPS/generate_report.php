<?php
include 'auth.php';
include 'db_connect.php';

// Get filters from GET or POST (GET is more suitable for report links)
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Prepare SQL with LIKE filters
$sql = "SELECT asset_id, asset_name, category, current_status, last_known_location, assigned_to, asset_status 
        FROM assets 
        WHERE asset_name LIKE ? AND category LIKE ? AND current_status LIKE ?";

$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$categoryParam = "%$category%";
$statusParam = "%$status%";
$stmt->bind_param('sss', $searchParam, $categoryParam, $statusParam);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No assets found matching your criteria.");
}

// Set headers to force download of CSV file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="asset_report_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, ['Asset ID', 'Asset Name', 'Category', 'Status', 'Last Known Location', 'Assigned To', 'Asset Standing']);

// Output data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['asset_id'],
        $row['asset_name'],
        $row['category'],
        $row['current_status'],
        $row['last_known_location'],
        $row['assigned_to'],
        $row['asset_status']
    ]);
}

fclose($output);
$conn->close();
exit;
