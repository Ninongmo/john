<?php
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../helpers/logActivity.php';  // Include the logActivity function

class ComputerController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Fetch all computers with room details
    public function getAllComputersWithPeripherals()
    {
        // Fetch computers along with their room details
        $stmt = $this->pdo->query("SELECT c.id, c.serial_number, c.specifications, c.purchase_date, c.conditions, c.room_id, r.name AS room_name
        FROM computers c
        LEFT JOIN rooms r ON c.room_id = r.id
    ");
        $computers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($computers as &$computer) {
            // Fetch peripherals for each computer
            $stmtPeripherals = $this->pdo->prepare("SELECT type, brand, model
            FROM peripherals
            WHERE computer_id = ?
        ");
            $stmtPeripherals->execute([$computer['id']]);
            $computer['peripherals'] = $stmtPeripherals->fetchAll(PDO::FETCH_ASSOC);
        }

        return $computers;
    }


    // Add a new computer
    public function addComputer($serial_number, $specifications, $purchase_date, $conditions, $room_id, $user_id, $peripherals)
    {
        $stmt = $this->pdo->prepare("INSERT INTO computers (serial_number, specifications, purchase_date, conditions, room_id, user_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$serial_number, $specifications, $purchase_date, $conditions, $room_id, $user_id]);

        if ($result) {
            $computerId = $this->pdo->lastInsertId();

            foreach ($peripherals as $peripheral) {
                $this->addPeripheral($computerId, $peripheral['type'], $peripheral['brand'], $peripheral['model']);
            }

            return true;
        }

        return false;
    }

    private function addPeripheral($computerId, $type, $brand, $model)
    {
        $stmt = $this->pdo->prepare("INSERT INTO peripherals (computer_id, type, brand, model)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$computerId, $type, $brand, $model]);
    }

    // Update computer details
    public function updateComputer($id, $serial_number, $specifications, $purchase_date, $conditions, $room_id, $user_id, $peripherals)
    {
        $stmt = $this->pdo->prepare("UPDATE computers
            SET serial_number = ?, specifications = ?, purchase_date = ?, conditions = ?, room_id = ?, user_id = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$serial_number, $specifications, $purchase_date, $conditions, $room_id, $user_id, $id]);

        if ($result) {
            // Remove existing peripherals
            $this->removePeripherals($id);

            // Add updated peripherals
            foreach ($peripherals as $peripheral) {
                $this->addPeripheral($id, $peripheral['type'], $peripheral['brand'], $peripheral['model']);
            }

            return true;
        }

        return false;
    }
    private function removePeripherals($computerId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM peripherals WHERE computer_id = ?");
        $stmt->execute([$computerId]);
    }

    // Delete a computer
    public function deleteComputer($id, $user_id)
    {
        // First, get the serial number for logging purposes
        $stmt = $this->pdo->prepare("SELECT serial_number FROM computers WHERE id = ?");
        $stmt->execute([$id]);
        $computer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($computer) {
            // Delete peripherals associated with this computer
            $stmtPeripherals = $this->pdo->prepare("DELETE FROM peripherals WHERE computer_id = ?");
            $stmtPeripherals->execute([$id]);

            // Now, delete the computer
            $stmt = $this->pdo->prepare("DELETE FROM computers WHERE id = ?");
            $result = $stmt->execute([$id]);

            // Log the action if the computer was deleted successfully
            if ($result) {
                logActivity($this->pdo, $user_id, 'delete', "Deleted computer with serial number " . $computer['serial_number']);
            }

            return $result;
        }

        return false;
    }

    public function getComputerById($id)
    {
        // Fetch computer details
        $stmt = $this->pdo->prepare("
        SELECT c.id, c.serial_number, c.specifications, c.purchase_date, c.conditions, c.room_id, r.name AS room_name
        FROM computers c
        LEFT JOIN rooms r ON c.room_id = r.id
        WHERE c.id = ?
    ");
        $stmt->execute([$id]);
        $computer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($computer) {
            // Fetch peripherals associated with this computer
            $stmtPeripherals = $this->pdo->prepare("
            SELECT type, brand, model
            FROM peripherals
            WHERE computer_id = ?
        ");
            $stmtPeripherals->execute([$computer['id']]);
            $computer['peripherals'] = $stmtPeripherals->fetchAll(PDO::FETCH_ASSOC);
        }

        return $computer;
    }

    public function logActivity($user_id, $action, $action_details)
    {
        $query = "INSERT INTO activity_logs (user_id, action, action_details, created_at)
              VALUES (:user_id, :action, :action_details, NOW())";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':action_details', $action_details);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


}
?>