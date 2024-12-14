<?php
class ReportController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function generateReport($room_id = null, $conditions = null, $purchase_year = null)
    {
        $query = "SELECT c.serial_number, c.specifications, c.conditions, c.purchase_date, r.name AS room_name
            FROM computers c
            LEFT JOIN rooms r ON c.room_id = r.id
            WHERE 1=1
        ";

        $params = [];

        if ($room_id) {
            $query .= " AND c.room_id = ?";
            $params[] = $room_id;
        }

        if ($conditions) {
            $query .= " AND c.conditions = ?";
            $params[] = $conditions;
        }

        if ($purchase_year) {
            $query .= " AND YEAR(c.purchase_date) = ?";
            $params[] = $purchase_year;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateOverallReport()
    {
        $query = "SELECT c.serial_number, c.specifications, c.conditions, c.purchase_date, r.name AS room_name
            FROM computers c
            LEFT JOIN rooms r ON c.room_id = r.id
        ";

        $stmt = $this->pdo->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}


?>