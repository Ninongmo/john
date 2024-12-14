<?php
include '../helpers/auth.php';
include '../config/database.php';
include '../controllers/RoomController.php';
include '../controllers/ReportController.php';
include '../controllers/AdminController.php';
checkAuth();

$adminId = $_SESSION['user_id'];
$adminController = new AdminController($pdo);
$admin = $adminController->getAdminProfile($adminId);

$roomController = new RoomController($pdo);
$roomStatus = $roomController->getRoomStatus();

$totalComputers = 0;
$working = 0;
$underMaintenance = 0;
$notWorking = 0;

foreach ($roomStatus as $room) {
    $totalComputers += $room['total_computers'];
    $working += $room['working'];
    $underMaintenance += $room['under_maintenance'];
    $notWorking += $room['not_working'];
}

$stmt = $pdo->query("
    SELECT al.id, al.user_id, al.action, al.action_details, al.created_at, u.username
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roomController = new RoomController($pdo);
$rooms = $roomController->getAllRooms();
$reportController = new ReportController($pdo);

$reportData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'] ?? null;
    $conditions = $_POST['conditions'] ?? null;
    $purchase_year = $_POST['purchase_year'] ?? null;

    $reportData = $reportController->generateReport($room_id, $conditions, $purchase_year);
} else {

    $reportData = $reportController->generateOverallReport();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>CISM Admin</title>

    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/scrollbar.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>

<body>
    <div class="container-scroller">
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
                <a class="sidebar-brand brand-logo" href="index.php"><img src="../assets/images/logo.svg"
                        alt="logo" /></a>
                <a class="sidebar-brand brand-logo-mini" href="index.php"><img src="../assets/images/logo-mini.svg"
                        alt="logo" /></a>
            </div>
            <ul class="nav">
                <li class="nav-item menu-items">
                    <a class="nav-link" href="index.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-speedometer"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../views/rooms/Room_Dashboard.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-clipboard-alert"></i>
                        </span>
                        <span class="menu-title">Room Management</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../views/computers/Computer_Dashboard.php">
                        <span class="menu-icon">
                            <i class="mdi mdi-package-variant-closed"></i>
                        </span>
                        <span class="menu-title">Inventory</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="../views/auth/logout.php">
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
                    <a class="navbar-brand brand-logo-mini" href="index.php"><img src="../assets/images/logo-mini.svg"
                            alt="logo" /></a>
                </div>
                <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button"
                        data-toggle="minimize">
                        <span class="mdi mdi-menu"></span>
                    </button>
                    <ul class="navbar-nav w-100">
                        <li class="nav-item w-100">
                            <form class="nav-link mt-2 mt-md-0 d-none d-lg-flex search">
                                <input type="text" id="reportSummarySearch" class=" form-control"
                                    placeholder="Search Report Summary">
                            </form>
                        </li>
                    </ul>
                    <ul class="navbar-nav navbar-nav-right">
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link btn btn-success create-new-button" id="createbuttonDropdown"
                                href="../views/computers/Computer_Add.php">+
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
                                <a class="dropdown-item preview-item" href="../views/admin/dashboard.php">
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
                                <a class="dropdown-item preview-item" href="../views/auth/logout.php">
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


                    <div class="row">
                        <div class="col-sm-4 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Working</h5>
                                    <div class="row">
                                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                                <h2 class="mb-0"><?= $working ?></h2>
                                                <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
                                            </div>
                                            <h6 class="text-muted font-weight-normal">11.38% Since last month</h6>
                                        </div>
                                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                                            <i class="icon-lg mdi mdi-monitor text-success ml-auto"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Under Maintenance</h5>
                                    <div class="row">
                                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                                <h2 class="mb-0"><?= $underMaintenance ?></h2>
                                                <p class="text-danger ml-2 mb-0 font-weight-medium">-2.1% </p>
                                            </div>
                                            <h6 class="text-muted font-weight-normal">2.27% Since last month</h6>
                                        </div>
                                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                                            <i class="icon-lg mdi mdi-human-greeting text-primary ml-auto"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Not Working</h5>
                                    <div class="row">
                                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                                <h2 class="mb-0"><?= $notWorking ?></h2>
                                                <p class="text-success ml-2 mb-0 font-weight-medium">+8.3%</p>
                                            </div>
                                            <h6 class="text-muted font-weight-normal">9.61% Since last month</h6>
                                        </div>
                                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                                            <i class="icon-lg mdi mdi-laptop-off text-danger ml-auto"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-4 grid-margin stretch-card">
                            <div class="card" style="background-color: #2a2b3d; color: white; height: 400px;">
                                <div class="card-body">
                                    <h4 class=" card-title">Total Computers</h4>
                                    <canvas id="total_computers" style="width: 200px; height: 200px;"
                                        class="total_computers"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-row justify-content-between">
                                        <h4 class="card-title mb-1">Activity Logs</h4>
                                        <form method="POST" action="../views/logs/download_logs.php">
                                            <button type="submit" class="nav-link btn btn-primary create-new-button">
                                                <i class="mdi mdi-download"></i> Download Logs
                                            </button>
                                        </form>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="preview-list" style="max-height: 300px; overflow-y: auto;">
                                                <?php foreach ($logs as $log): ?>
                                                <div class="preview-item border-bottom">
                                                    <div class="preview-thumbnail">
                                                        <?php if ($log['action'] == 'update'): ?>
                                                        <div class="preview-icon bg-primary">
                                                            <i class="mdi mdi-update"></i>
                                                        </div>
                                                        <?php elseif ($log['action'] == 'add'): ?>
                                                        <div class="preview-icon bg-success">
                                                            <i class="mdi mdi-package-variant"></i>
                                                        </div>
                                                        <?php elseif ($log['action'] == 'delete'): ?>
                                                        <div class="preview-icon bg-danger"><i
                                                                class="mdi mdi-delete"></i></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="preview-item-content d-sm-flex flex-grow">
                                                        <div class="flex-grow">
                                                            <h6 class="preview-subject">
                                                                <?= ucfirst(htmlspecialchars($log['action'])) ?>
                                                            </h6>
                                                            <p class="text-muted mb-0">
                                                                <?= htmlspecialchars($log['action_details']) ?>
                                                            </p>
                                                        </div>
                                                        <div class="mr-auto text-sm-right pt-2 pt-sm-0">
                                                            <p class="text-muted">
                                                                <?= htmlspecialchars($log['created_at']) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="card-title mb-0">Report Summary</h4>
                                        <form method="POST" action="../views/reports/export.php?type=csv">
                                            <button type="submit" class="nav-link btn btn-primary create-new-button">
                                                <i class="mdi mdi-download"></i> Download Report
                                            </button>
                                        </form>
                                    </div>
                                    <div class="table-responsive preview-list"
                                        style="max-height: 300px; overflow-y: auto;">
                                        <table class="table" id="reportSummaryTable">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Serial Number</th>
                                                    <th>Specifications</th>
                                                    <th>Room</th>
                                                    <th>Purchase Date</th>
                                                    <th>Condition</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reportData as $computer): ?>
                                                <tr>
                                                    <td></td>
                                                    <td><?= htmlspecialchars($computer['serial_number']) ?></td>
                                                    <td><?= htmlspecialchars($computer['specifications']) ?></td>
                                                    <td><?= htmlspecialchars($computer['room_name'] ?? 'Unassigned') ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($computer['purchase_date']) ?></td>
                                                    <td>

                                                        <?php if ($computer['conditions'] == 'working'): ?>
                                                        <div class="badge badge-outline-success">Working</div>
                                                        <?php elseif ($computer['conditions'] == 'under maintenance'):?>
                                                        <div class="badge badge-outline-primary">Under Maintenance
                                                        </div>
                                                        <?php elseif ($computer['conditions'] == 'not working'):?>
                                                        <div class="badge badge-outline-warning">Not Working</div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/chart.js/Chart.min.js"></script>
    <script src="../assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="../assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="../assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="../assets/vendors/owl-carousel-2/owl.carousel.min.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/hoverable-collapse.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const totalComputers = <?php echo $totalComputers; ?>;
        const working = <?php echo $working; ?>;
        const underMaintenance = <?php echo $underMaintenance; ?>;
        const notWorking = <?php echo $notWorking; ?>;
        const ctx = document.getElementById("total_computers").getContext("2d");
        const chart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Working", "Under Maintenance", "Not Working"],
                datasets: [{
                    label: "Computer Status",
                    data: [working, underMaintenance,
                        notWorking
                    ],
                    backgroundColor: ["#00C853", "#0090e7",
                        "#d32f2f"
                    ],
                    borderColor: "#ffffff",
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ": " + tooltipItem
                                    .raw;
                            }
                        }
                    },
                    doughnutlabel: {
                        display: true,
                        labels: [{
                            text: totalComputers,
                            font: {
                                size: 20,
                                weight: 'bold',
                            },
                            color: "#ffffff",
                        }]
                    }
                },
                layout: {
                    padding: {
                        bottom: 30,
                    }
                },
            },
        });
    });
    </script>
    <script>
    document.getElementById('reportSummarySearch').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#reportSummaryTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
    </script>

</body>

</html>