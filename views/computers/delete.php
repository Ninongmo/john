<?php
include '../../config/database.php';
include '../../controllers/ComputerController.php';
include '../../helpers/auth.php';

$computerController = new ComputerController($pdo);

checkAuth();
$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $computer_id = $_GET['id'];
    if ($computerController->deleteComputer($computer_id, $user_id)) {
        header("Location: ../../public/index.php");
        exit;
    } else {
        $error = "Failed to delete computer.";
    }
} else {
    $error = "No computer ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Delete Computer</title>
</head>

<body>
    <h1>Delete Computer</h1>
    <?php if (isset($error))
        echo "<p style='color: red;'>$error</p>"; ?>
    <p>Computer has been deleted successfully. <a href="../../public/index.php">Go back to the inventory</a>.</p>
</body>

</html>