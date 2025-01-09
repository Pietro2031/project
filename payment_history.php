<?php
include('connection.php');

$items_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$selected_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$selected_time_frame = isset($_GET['time_frame']) ? $_GET['time_frame'] : '';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$selected_date = isset($_GET['order_date']) ? $_GET['order_date'] : '';

$query = "
SELECT orders.id, orders.order_date, orders.total_amount, orders.payment_method, user_account.username
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
WHERE orders.status = 1
";

if (!empty($selected_method)) {
    $query .= " AND orders.payment_method = '" . mysqli_real_escape_string($conn, $selected_method) . "'";
}

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

// Filter by selected date
if (!empty($selected_date)) {
    // Ensure the selected date is in the format 'Y-m-d'
    $selected_date = date('Y-m-d', strtotime($selected_date));
    // Update query to compare only the date part (ignoring time)
    $query .= " AND DATE(orders.order_date) = '" . mysqli_real_escape_string($conn, $selected_date) . "'";
}

// Search functionality
if (!empty($search_keyword)) {
    $query .= " AND (user_account.username LIKE '%" . mysqli_real_escape_string($conn, $search_keyword) . "%' 
                 OR orders.id LIKE '%" . mysqli_real_escape_string($conn, $search_keyword) . "%')";
}

$total_items_query = "SELECT COUNT(*) AS count FROM ($query) AS subquery";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_row = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_row['count'];
$total_pages = ceil($total_items / $items_per_page);

$query .= " ORDER BY STR_TO_DATE(orders.order_date, '%Y-%m-%d') DESC LIMIT $offset, $items_per_page";
$ordersResult = mysqli_query($conn, $query);

$paymentMethodsQuery = "SELECT DISTINCT payment_method FROM orders";
$paymentMethodsResult = mysqli_query($conn, $paymentMethodsQuery);
?>

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

    .slideright .slidedown {
        width: 100%;
    }

    .slideright {
        gap: 10px;
        padding: 10px;
        align-items: flex-end;
    }

    .view-btn {
        text-decoration: none;
        color: #007bff;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Payment History</h3>
            </div>
            <div class="panel-body">

                <form method="GET" action="admin.php" class="filter-form">
                    <input type="hidden" name="payment_history" value="1">
                    <div class="slideright">
                        <div class="slidedown">
                            <label for="search">Search:</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search by Order ID " 
                                   value="<?php echo htmlspecialchars($search_keyword); ?>">
                        </div>
                        <div class="slidedown">
                            <label for="payment_method">Payment Method:</label>
                            <select name="payment_method" id="payment_method" class="form-control">
                                <option value="">All</option>
                                <?php while ($method = mysqli_fetch_assoc($paymentMethodsResult)): ?>
                                    <option value="<?php echo $method['payment_method']; ?>"
                                        <?php if ($selected_method == $method['payment_method']) echo 'selected'; ?>>
                                        <?php echo $method['payment_method']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="slidedown">
                            <label for="time_frame">Time Frame:</label>
                            <select name="time_frame" id="time_frame" class="form-control">
                                <option value="">All Time</option>
                                <option value="last_7_days" <?php if ($selected_time_frame == 'last_7_days') echo 'selected'; ?>>Last 7 Days</option>
                                <option value="last_30_days" <?php if ($selected_time_frame == 'last_30_days') echo 'selected'; ?>>Last 30 Days</option>
                                <option value="this_month" <?php if ($selected_time_frame == 'this_month') echo 'selected'; ?>>This Month</option>
                            </select>
                        </div>
                        <div class="slidedown">
                            <label for="date_picker">Order Date:</label>
                            <input type="date" name="order_date" id="date_picker" class="form-control" 
                                   value="<?php echo isset($_GET['order_date']) ? $_GET['order_date'] : ''; ?>">
                        </div>
                        <input type="submit" value="Apply" class="submit-btn-filter">
                        <a href="print-payment_history.php" target="_blank" class="submit-btn-filter">Print</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped payment-history-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Print</th> <!-- Added View column -->
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
                                    <td>
                                        <!-- View Button -->
                                        <a href="view-order.php?order_id=<?php echo $order['id']; ?>" class="view-btn" target="_blank">Print</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li>
                                <a href="admin.php?payment_history=1&page=<?php echo $i; ?>&payment_method=<?php echo $selected_method; ?>&time_frame=<?php echo $selected_time_frame; ?>&search=<?php echo htmlspecialchars($search_keyword); ?>&order_date=<?php echo htmlspecialchars($selected_date); ?>"
                                    class="<?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
