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
$sortOrder = isset($_GET['sort']) && $_GET['sort'] == 'asc' ? 'ASC' : 'DESC';

$topProductsQuery = "
SELECT product_name, total_sales 
FROM coffee_products
ORDER BY total_sales $sortOrder
";
$topProductsResult = $conn->query($topProductsQuery);
?>
<style>
    .dashboard-container {
        padding: 20px;
    }

    .dashboard-header h2 {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .dashboard-header p {
        font-size: 16px;
        color: #666;
    }

    .dashboard-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        width: calc(50% - 10px);
    }

    .dashcir {
        display: flex;
        background-color: #007bff;
        height: 100px;
        aspect-ratio: 1 / 1;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
    }

    .dashcir p {
        border-radius: 50%;
        background: white;
        width: 80%;
        aspect-ratio: 1/1;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .stat-card-body h5 {
        font-size: 18px;
        margin-bottom: 10px;
    }

    .stat-card-body p {
        font-size: 24px;
        font-weight: bold;
    }

    .stat-card-body {
        display: flex;
        flex-direction: row-reverse;
        align-items: center;
        gap: 10px;
        justify-content: space-between;
    }

    .stat-card-footer {
        margin-top: 15px;
    }

    .stat-card-footer a {
        text-decoration: none;
        color: #007bff;
    }

    .chart-section {
        margin-bottom: 40px;
    }

    .recent-activity h4 {
        font-size: 22px;
        margin-bottom: 20px;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f2f2f2;
    }

    .table th,
    .table td {
        padding: 15px;
        vertical-align: middle;
    }

    canvas {
        max-width: 100% !important;
        margin: 0 auto;
        height: auto;
    }

    .chart-section {
        display: flex;
    }

    #chartdiv {
        width: 40vw;
        height: 60vh;
        margin-top: 20px;
    }

    .panel.panel-default {
        margin: 0 0 5px 0;
    }

    .col-lg-6 {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .col-lg-3.col-md-6 {
        width: 15%;
    }

    .graph {
        background: #ccc;
        width: 400px;
        aspect-ratio: 2 / 1;
        margin: 5px 0;
    }

    .dashtotal {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
    }

    .dashtotal {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
    }

    div#issues p {
        padding: 5px 0;
        border-bottom: solid 1px #ccc;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Admin Dashboard</h3>
            </div>
            <div class="panel-body">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Count</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <div class="dashtotal">
                                <div class="stat-card">
                                    <div class="stat-card-body">
                                        <div class="dashcir">
                                            <p id="totalMeals"><?= $productCount ?></p>
                                        </div>
                                        <h5>Total Products</h5>
                                    </div>
                                    <div class="stat-card-footer">
                                        <a href="?view_products">View all Product</a>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-card-body">
                                        <div class="dashcir">
                                            <p id="totalCategories"><?= $categoryCount ?></p>
                                        </div>
                                        <h5>Total Categories</h5>
                                    </div>
                                    <div class="stat-card-footer">
                                        <a href="?view_category">View all categories</a>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-card-body">
                                        <div class="dashcir">
                                            <p id="totalUsers"><?= $orderCount ?></p>
                                        </div>
                                        <h5>Total Orders</h5>
                                    </div>
                                    <div class="stat-card-footer">
                                        <a href="admin.php?report2">View all Orders</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Recent Orders</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
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
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Sold Products</h3>
                <form method="GET" style="float: right;">
                    <input type="hidden" name="dashboard" value="1">
                    <label for="sort">Sort by Sales:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'asc' ? '' : 'selected' ?>>Descending</option>
                        <option value="asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'asc' ? 'selected' : '' ?>>Ascending</option>
                    </select>
                </form>
            </div>

            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
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

            </div>
        </div>
    </div>
</div>


</html>