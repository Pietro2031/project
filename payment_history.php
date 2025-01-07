<?php
include('connection.php'); 

$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$total_items_query = "SELECT COUNT(*) AS count FROM orders WHERE status = 2";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_row = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_row['count'];
$total_pages = ceil($total_items / $items_per_page);

$ordersQuery = "
SELECT orders.id, orders.order_date, orders.total_amount, orders.payment_method, user_account.username
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
WHERE orders.status = 1
ORDER BY orders.order_date DESC
LIMIT $offset, $items_per_page
";
$ordersResult = mysqli_query($conn, $ordersQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <h1>Payment History</h1>
        <div class="payment-history-table">
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

        <div class="pagination">
            <ul class="pagination">
                <?php if ($current_page > 1): ?>
                    <li><a href="payment_history.php?page=<?php echo $current_page - 1; ?>">&laquo; Previous</a></li>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <li <?php if ($page == $current_page) echo 'class="active"'; ?>>
                        <a href="payment_history.php?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li><a href="payment_history.php?page=<?php echo $current_page + 1; ?>">Next &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>

</html>