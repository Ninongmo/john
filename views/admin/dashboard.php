<?php
include '../../helpers/auth.php';
include '../../controllers/AdminController.php';
checkAuth();

$adminId = $_SESSION['user_id'];
$adminController = new AdminController($pdo);
$admin = $adminController->getAdminProfile($adminId);
if (!$admin) {
    echo "<script>alert('Admin not found. Redirecting to login.');</script>";
    header('Location: ../auth/login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!empty($newPassword) && $newPassword === $confirmPassword) {
        if ($adminController->updatePassword($adminId, $newPassword)) {
            echo "<script>alert('Password updated successfully');</script>";
        } else {
            echo "<script>alert('Error updating password');</script>";
        }
    } elseif (!empty($newUsername = $_POST['new_username'])) {
        if ($adminController->updateUsername($adminId, $newUsername)) {
            echo "<script>alert('Username updated successfully');</script>";
        } else {
            echo "<script>alert('Error updating username');</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match');</script>";
    }
}
?>


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>CISM Admin</title>
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
</head>

<body>
    <div class="container-scroller">
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
                <a class="sidebar-brand brand-logo" href="../../public/index.php"><img
                        src="../../assets/images/logo.svg" alt="logo" /></a>
                <a class="sidebar-brand brand-logo-mini" href="../../public/index.php"><img
                        src="../../assets/images/logo-mini.svg" alt="logo" /></a>
            </div>
            <ul class="nav">
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../../public/index.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-speedometer"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../rooms/Room_Dashboard.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-clipboard-alert"></i>
                        </span>
                        <span class="menu-title">Room Management</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../computers/Computer_Dashboard.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-package-variant-closed"></i>
                        </span>
                        <span class="menu-title">Inventory</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../auth/logout.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-logout"></i>
                        </span>
                        <span class="menu-title">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="container-fluid page-body-wrapper">
            <nav class="navbar p-0 fixed-top d-flex flex-row">
                <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
                    <a class="navbar-brand brand-logo-mini" href="../../public/index.php"><img
                            src="../../assets/images/logo-mini.svg" alt="logo" /></a>
                </div>
                <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button"
                        data-toggle="minimize">
                        <span class="mdi mdi-menu"></span>
                    </button>
                    <ul class="navbar-nav w-100">
                        <li class="nav-item w-100">
                            <form class="nav-link mt-2 mt-md-0 d-none d-lg-flex search">
                                <input type="text" class="form-control" placeholder="Search products">
                            </form>
                        </li>
                    </ul>
                    <ul class="navbar-nav navbar-nav-right">
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link btn btn-success create-new-button" id="createbuttonDropdown"
                                href="../computers/Computer_Add.php">+
                                Add
                                Computer</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" id="profileDropdown" href="#" data-toggle="dropdown">
                                <div class="navbar-profile">
                                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center"
                                        style="width: 35px; height: 35px; font-size: 15px;">
                                        <?= htmlspecialchars(substr($admin['username'], 0, 1)) ?>
                                    </div>
                                    <p class="mb-0 d-none d-sm-block navbar-profile-name" style="margin-left: 10px;">
                                        <?= htmlspecialchars($admin['username']); ?>
                                    </p>
                                    <i class="mdi mdi-menu-down d-none d-sm-block"></i>
                                </div>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                                aria-labelledby="profileDropdown">
                                <h6 class="p-3 mb-0">Profile</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item preview-item" href="../admin/dashboard.php">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-settings text-success"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">Settings</p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item preview-item" href="../auth/logout.php">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-logout text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">Log out</p>
                                    </div>
                                </a>
                            </div>
                        </li>
                    </ul>
                    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                        data-toggle="offcanvas">
                        <span class="mdi mdi-format-line-spacing"></span>
                    </button>
                </div>
            </nav>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="content-wrapper d-flex justify-content-center align-items-center">
                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Admin Profile</h4>
                                    <form class="forms-sample" method="POST">
                                        <div class="form-group">
                                            <label for="new_username">Username</label>
                                            <input type="text" class="form-control" id="new_username"
                                                name="new_username"
                                                value="<?php echo htmlspecialchars($admin['username']); ?>"
                                                placeholder="Username">
                                        </div>
                                        <div class="form-group">
                                            <label for="new_password">Password</label>
                                            <input type="password" class="form-control" id="new_password"
                                                name="new_password" placeholder="Password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password"
                                                name="confirm_password" placeholder="Confirm Password">
                                        </div>
                                        <button type="submit" class="btn btn-primary mr-2">Update</button>
                                        <a href="../../public/index.php" class="btn btn-dark">Cancel</a>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/hoverable-collapse.js"></script>
    <script src="../../assets/js/misc.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
</body>

</html>