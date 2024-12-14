<?php
function logActivity($pdo, $userId, $action, $actionDetails)
{
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, action_details)
                           VALUES (:user_id, :action, :action_details)");
    $stmt->execute([
        ':user_id' => $userId,
        ':action' => $action,
        ':action_details' => $actionDetails
    ]);
}