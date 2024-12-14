<?php
include '../../config/database.php';
include '../../controllers/RoomController.php';

$roomController = new RoomController($pdo);

if (isset($_GET['id'])) {
    $roomController->deleteRoom($_GET['id']);
    header("Location: ../../public/index.php");
    exit;
}
?>