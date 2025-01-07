<?php
session_start();


include('connection.php');


$username = "admin";
$query = "SELECT profile_picture FROM admin_account WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $profile_picture = $admin['profile_picture'];
} else {
    $profile_picture = 'default-profile.png';
}
if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information</title>
    <link rel="stylesheet" href="userinfo.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/table.css">

</head>

<body>
    <div class=" sidebar">
        <div class="profile">
            <center>
                <div class="profile-image-container">
                    <img src="<?= !empty($profile_picture) ? htmlspecialchars($profile_picture) : 'default-profile.png' ?>" alt="Admin" class="profile-image">
                </div>
            </center>
            <div class="profile-info">
                <p class="profile-name">Hello, Admin</p>
                <p class="profile-role">Administrator</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="?dashboard" <?php if (isset($_GET['dashboard'])) {echo 'class="active"';} ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="themevalidation.php"><i class="fas fa-paint-brush"></i> Theme</a></li>
                <li><a href="?view_order" <?php if (isset($_GET['order'])) {echo 'class="active"';} ?>><i class="fas fa-chart-line"></i>Orders</a></li>
                <li><a href="?view_products" <?php if (isset($_GET['view_products']) || isset($_GET['insert_products']) || isset($_GET['edit_product'])) {echo 'class="active"';} ?>><i class="fas fa-box-open"></i> Products</a></li>
                <li><a href="?view_inventory" <?php if (isset($_GET['view_inventory'])) {echo 'class="active"';} ?>><i class="fas fa-th-list"></i> Inventory</a></li>
                <li><a href="?POS" <?php if (isset($_GET['poit_of_sale'])) {echo 'class="active"';} ?>><i class="fas fa-box-open"></i> Point Of Sale</a></li>
                <li><a href="?report" <?php if (isset($_GET['report'])) {echo 'class="active"';} ?>><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="#"><i class="fas fa-receipt"></i> Payment History</a></li>
                <li><a href="userinfo.php"><i class="fas fa-user-tag"></i> User Information</a></li>
            </ul>
        </nav>
        <div class="sidebar-bottom">
            <ul>
                <li><a href="adminprofile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
            </ul>
        </div>
    </div>
    <div class="topbar">

        <a href="adminlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="dom">
        <?php
        if (isset($_GET['dashboard'])) {
            include("dashboard.php");
        }
        if (isset($_GET['view_order'])) {
            include 'view_orders.php';
        }
        if (isset($_GET['view_products'])) {
            include 'view_products.php';
        }
        if (isset($_GET['insert_products'])) {
            include 'insert_products.php';
        }
        if (isset($_GET['edit_product'])) {
            include 'edit_product.php';
        }
        if (isset($_GET['view_inventory'])) {
            include 'view_inventory.php';
        }
        if (isset($_GET['item_type'])) {
            include 'edit_inventory.php';
        }
        if (isset($_GET['view_category'])) {
            include 'view_category.php';
        }
        if (isset($_GET['insert_inventory'])) {
            include 'insert_inventory.php';
        }
        if (isset($_GET['POS'])) {
            include 'point_of_sale.php';
        }

        ?>

    </div>
</body>

</html>