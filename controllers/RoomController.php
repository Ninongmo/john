<?php
include __DIR__ . '/../config/database.php';

class RoomController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Fetch all rooms with computer count
    public function getAllRooms()
    {
        $stmt = $this->pdo->query("SELECT r.id, r.name, r.description, r.created_at, r.updated_at,
                   COUNT(c.id) AS computer_count
            FROM rooms r
            LEFT JOIN computers c ON r.id = c.room_id
            GROUP BY r.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a new room
    public function addRoom($name, $description)
    {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }

    // Get a single room by ID
    public function getRoomById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update room details
    public function updateRoom($id, $name, $description)
    {
        $stmt = $this->pdo->prepare("UPDATE rooms SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $id]);
    }

    // Delete a room
    public function deleteRoom($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function getRoomStatus()
    {
        $stmt = $this->pdo->prepare("SELECT r.id, r.name, r.description,
                   COUNT(c.id) AS total_computers,
                   SUM(c.conditions = 'working') AS working,
                   SUM(c.conditions = 'under maintenance') AS under_maintenance,
                   SUM(c.conditions = 'not working') AS not_working
            FROM rooms r
            LEFT JOIN computers c ON r.id = c.room_id
            GROUP BY r.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getPreviousMonthValues($pdo)
    {
        $firstDayCurrentMonth = date('Y-m-01');
        $firstDayPreviousMonth = date('Y-m-01', strtotime('first day of last month'));

        $sql = "
        SELECT
            SUM(CASE WHEN status = 'working' THEN 1 ELSE 0 END) AS working,
            SUM(CASE WHEN status = 'under_maintenance' THEN 1 ELSE 0 END) AS under_maintenance,
            SUM(CASE WHEN status = 'not_working' THEN 1 ELSE 0 END) AS not_working
        FROM rooms
        WHERE created_at < :firstDayCurrentMonth
        AND created_at >= :firstDayPreviousMonth
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':firstDayCurrentMonth' => $firstDayCurrentMonth,
            ':firstDayPreviousMonth' => $firstDayPreviousMonth
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>