<?php
include '../../config/database.php';
include '../../controllers/ReportController.php';

$room_id = $_GET['room_id'] ?? null;
$conditions = $_GET['conditions'] ?? null;
$purchase_year = $_GET['purchase_year'] ?? null;
$type = $_GET['type'] ?? null;


$reportController = new ReportController($pdo);
$reportData = $reportController->generateReport($room_id, $conditions, $purchase_year);

if ($type === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="computer_inventory_report.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Serial Number', 'Specifications', 'Condition', 'Purchase Date', 'Room']);

    foreach ($reportData as $computer) {
        fputcsv($output, [
            $computer['serial_number'],
            $computer['specifications'],
            $computer['conditions'],
            $computer['purchase_date'],
            $computer['room_name']
        ]);
    }

    fclose($output);
    exit;
} else {
    echo "Invalid or missing report type.";
}
?>