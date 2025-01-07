<?php
include('connection.php');

$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Retrieve filter values from GET request
$selected_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$selected_time_frame = isset($_GET['time_frame']) ? $_GET['time_frame'] : '';

// Base Query for Completed Orders
$query = "
SELECT orders.id, orders.order_date, orders.total_amount, orders.payment_method, user_account.username
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
WHERE orders.status = 1
";

// Apply Payment Method Filter
if (!empty($selected_method)) {
    $query .= " AND orders.payment_method = '" . mysqli_real_escape_string($conn, $selected_method) . "'";
}

// Apply Time Frame Filter
if (!empty($selected_time_frame)) {
    $current_date = date('Y-m-d');
    switch ($selected_time_frame) {
        case 'last_7_days':
            $query .= " AND orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'last_30_days':
            $query .= " AND orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'this_month':
            $query .= " AND MONTH(orders.order_date) = MONTH(CURDATE()) AND YEAR(orders.order_date) = YEAR(CURDATE())";
            break;
    }
}

// Get total count for pagination
$total_items_query = "SELECT COUNT(*) AS count FROM ($query) AS subquery";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_row = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_row['count'];
$total_pages = ceil($total_items / $items_per_page);

// Apply Pagination
$query .= " ORDER BY STR_TO_DATE(orders.order_date, '%Y-%m-%d') DESC LIMIT $offset, $items_per_page";
$ordersResult = mysqli_query($conn, $query);

// Fetch unique payment methods for the dropdown
$paymentMethodsQuery = "SELECT DISTINCT payment_method FROM orders WHERE status = 2";
$paymentMethodsResult = mysqli_query($conn, $paymentMethodsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        @media print {

            .filter-form,
            .pagination,
            .print-btn {
                display: none;
            }

            .payment-history-table {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <h1>Payment History</h1>

        <form method="GET" action="admin.php" class="filter-form">
            <input type="hidden" name="payment_history" value="1">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method">
                <option value="">All</option>
                <?php while ($method = mysqli_fetch_assoc($paymentMethodsResult)): ?>
                    <option value="<?php echo $method['payment_method']; ?>"
                        <?php if ($selected_method == $method['payment_method']) echo 'selected'; ?>>
                        <?php echo $method['payment_method']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="time_frame">Time Frame:</label>
            <select name="time_frame" id="time_frame">
                <option value="">All Time</option>
                <option value="last_7_days" <?php if ($selected_time_frame == 'last_7_days') echo 'selected'; ?>>Last 7 Days</option>
                <option value="last_30_days" <?php if ($selected_time_frame == 'last_30_days') echo 'selected'; ?>>Last 30 Days</option>
                <option value="this_month" <?php if ($selected_time_frame == 'this_month') echo 'selected'; ?>>This Month</option>
            </select>

            <button type="submit">Filter</button>
        </form>

        <!-- Print Button -->
        <a href="print-payment_history.php">Print Table</a>

        <div class="payment-history-table" id="printableTable">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['username']; ?></td>
                            <td><?php echo date('F j, Y', strtotime($order['order_date'])); ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo $order['payment_method']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <ul class="pagination">
                <?php if ($current_page > 1): ?>
                    <li><a href="admin.php?payment_history&page=<?php echo $current_page - 1; ?>&payment_method=<?php echo $selected_method; ?>&time_frame=<?php echo $selected_time_frame; ?>">&laquo; Previous</a></li>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <li <?php if ($page == $current_page) echo 'class="active"'; ?>>
                        <a href="admin.php?payment_history&page=<?php echo $page; ?>&payment_method=<?php echo $selected_method; ?>&time_frame=<?php echo $selected_time_frame; ?>">
                            <?php echo $page; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li><a href="admin.php?payment_history&page=<?php echo $current_page + 1; ?>&payment_method=<?php echo $selected_method; ?>&time_frame=<?php echo $selected_time_frame; ?>">Next &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>

</html>