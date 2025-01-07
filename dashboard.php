<?php
include('connection.php');
$productQuery = "SELECT COUNT(*) AS total_products FROM coffee_products";
$productResult = $conn->query($productQuery);
$productCount = $productResult->fetch_assoc()['total_products'];
$categoryQuery = "SELECT COUNT(*) AS total_categories FROM coffee_category";
$categoryResult = $conn->query($categoryQuery);
$categoryCount = $categoryResult->fetch_assoc()['total_categories'];
$orderQuery = "SELECT COUNT(*) AS total_amount FROM orders";
$orderResult = $conn->query($orderQuery);
$orderCount = $orderResult->fetch_assoc()['total_amount'];
$recentOrdersQuery = "
SELECT orders.id, orders.order_date, orders.total_amount, user_account.username
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
ORDER BY orders.order_date DESC
LIMIT 5
";
$recentOrdersResult = $conn->query($recentOrdersQuery);
$addonsQuery = "SELECT * FROM addons";
$addonsResult = $conn->query($addonsQuery);
$topProductsQuery = "
SELECT product_name, total_sales 
FROM coffee_products
ORDER BY total_sales DESC
LIMIT 5
";
$topProductsResult = $conn->query($topProductsQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <div class="stats">
            <div class="stat-box">
                <h2><?php echo $productCount; ?></h2>
                <p>Total Products</p>
            </div>
            <div class="stat-box">
                <h2><?php echo $categoryCount; ?></h2>
                <p>Total Categories</p>
            </div>
            <div class="stat-box">
                <h2><?php echo $orderCount; ?></h2>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recentOrdersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['username']; ?></td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="top-products-section">
            <h2>Top 5 Most Sold Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $topProductsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['product_name']; ?></td>
                            <td><?php echo $product['total_sales']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="addons-section">
            <h2>Add-ons</h2>
            <div class="addons-list">
                <?php while ($addon = $addonsResult->fetch_assoc()): ?>
                    <div class="addon-item">
                        <p><strong><?php echo $addon['addon_name']; ?></strong></p>
                        <p>₱<?php echo number_format($addon['addon_price'], 2); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>
</body>

</html>