<?php
include '../../config/database.php';

$stmt = $pdo->query("
    SELECT al.id, al.user_id, al.action, al.action_details, al.created_at, u.username
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output = fopen('php://output', 'w');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="activity_logs.csv"');

fputcsv($output, ['ID', 'Username', 'Action', 'Message', 'Timestamp']);


foreach ($logs as $log) {
    fputcsv($output, [
        $log['id'],
        $log['username'] ?? 'Unknown User',
        $log['action'],
        $log['action_details'],
        $log['created_at']
    ]);
}

fclose($output);
exit;
?>