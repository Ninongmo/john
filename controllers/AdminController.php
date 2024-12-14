<?php
include __DIR__ . '../../config/database.php';

class AdminController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAdminProfile($adminId)
    {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updatePassword($adminId, $newPassword)
    {
        // Update the admin's password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['password' => $hashedPassword, 'id' => $adminId]);
    }

    public function updateUsername($adminId, $newUsername)
    {
        // Update the admin's username
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['username' => $newUsername, 'id' => $adminId]);
    }
    public function logActivity($user_id, $action, $action_details)
    {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, action_details, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$user_id, $action, $action_details]);
    }
}
?>