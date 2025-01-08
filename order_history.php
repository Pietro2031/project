<?php
include("connection.php");
session_start();

// Get user ID based on the logged-in username
$stmt = $conn->prepare("SELECT id FROM user_account WHERE userName = ?");
if ($stmt) {
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userId = $result->fetch_assoc()['id'];
    }
    $stmt->close();
}

// Handle status update when Cancel button is clicked
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $update_status_query = "UPDATE orders SET status = 2 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_status_query);
    $stmt->bind_param("ii", $order_id, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: order_history.php");
    exit();
}

// Handle filters
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$time_filter = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'any';

$time_conditions = "";
if ($time_filter === '3d') {
    $time_conditions = "AND orders.order_date >= NOW() - INTERVAL 3 DAY";
} elseif ($time_filter === '7d') {
    $time_conditions = "AND orders.order_date >= NOW() - INTERVAL 7 DAY";
} elseif ($time_filter === '1m') {
    $time_conditions = "AND orders.order_date >= NOW() - INTERVAL 1 MONTH";
}

$status_conditions = $status_filter >= 0 ? "AND orders.status = $status_filter" : "";

// Pagination
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$total_items_query = "
    SELECT COUNT(DISTINCT orders.id) AS count 
    FROM orders 
    WHERE orders.user_id = '$userId' $status_conditions $time_conditions";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['count'];
$total_pages = ceil($total_items / $items_per_page);

$get_orders = "
    SELECT 
        orders.id AS order_id,
        orders.order_date,
        orders.order_quantity,
        orders.status,
        orders.size,
        orders.flavor,
        orders.toppings,
        GROUP_CONCAT(coffee_products.product_name SEPARATOR ', ') AS item_names,
        GROUP_CONCAT(coffee_products.product_image SEPARATOR ', ') AS item_images,
        SUM(orders.total_amount) AS amount_paid,
        orders.payment_method
    FROM orders
    LEFT JOIN coffee_products ON FIND_IN_SET(coffee_products.id, orders.product_ids) > 0
    WHERE orders.user_id = '$userId' $status_conditions $time_conditions
    GROUP BY orders.id
    ORDER BY orders.order_date DESC
    LIMIT $offset, $items_per_page";

$run_orders = mysqli_query($conn, $get_orders);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/history.css">
    <style>
        .filter-container {
            display: flex;
            justify-content: flex-end;
        }

        .cart-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-container th,
        .cart-container td {
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #ddd;
        }

        .cart-container th {
            background-color: #f4f4f4;
        }

        .cart-container .itemtable img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .cart-container .status-container {
            display: flex;
            flex-direction: column;
        }

        .status-container .status-step {
            margin: 5px 0;
        }

        /* Pagination styles */
        .pageno {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .pageno div {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .pageno .active {
            background-color: #007bff;
            color: #fff;
        }

        .pageno a {
            text-decoration: none;
            color: inherit;
        }

        .pageno a:hover {
            color: #007bff;
        }

        /* Modal styles */
        #returnModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        #returnModal .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        #returnModal select {
            margin-top: 10px;
            padding: 5px;
            width: 80%;
        }

        #returnModal button {
            margin: 10px 5px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #returnModal button:first-child {
            background-color: #007bff;
            color: #fff;
        }

        #returnModal button:last-child {
            background-color: #f44336;
            color: #fff;
        }
    </style>
    <?php include("header.php"); ?>
</head>

<body>
    <section class="center">
        <div class="Itemcart">
            <h1>Purchase History</h1>

            <div class="filter-container">
                <form method="GET" action="order_history.php">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="-1" <?= $status_filter === -1 ? 'selected' : '' ?>>All</option>
                        <option value="0" <?= $status_filter === 0 ? 'selected' : '' ?>>Placed</option>
                        <option value="1" <?= $status_filter === 1 ? 'selected' : '' ?>>Delivered</option>
                        <option value="2" <?= $status_filter === 2 ? 'selected' : '' ?>>Canceled</option>
                        <option value="3" <?= $status_filter === 3 ? 'selected' : '' ?>>Canceled by User</option>
                    </select>

                    <label for="time_frame">Time Frame:</label>
                    <select name="time_frame" id="time_frame">
                        <option value="any" <?= $time_filter === 'any' ? 'selected' : '' ?>>Any</option>
                        <option value="3d" <?= $time_filter === '3d' ? 'selected' : '' ?>>Last 3 Days</option>
                        <option value="7d" <?= $time_filter === '7d' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="1m" <?= $time_filter === '1m' ? 'selected' : '' ?>>Last 1 Month</option>
                    </select>

                    <button type="submit">Filter</button>
                </form>
            </div>

            <div class="cart-container">
                <table class="itemtable">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Size</th>
                            <th>Details</th>
                            <th>Flavor</th>
                            <th>Toppings</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_array($run_orders)) :
                            $order_date = date('F j, Y', strtotime($row['order_date']));
                            $status_text = [
                                "0" => "Placed",
                                "1" => "Delivered",
                                "2" => "Canceled",
                                "3" => "Canceled by User"
                            ][$row['status']];
                        ?>
                            <tr>
                                <td><?= $row['order_id'] ?></td>
                                <td>
                                    <?php
                                    $names = explode(', ', $row['item_names']);
                                    $images = explode(', ', $row['item_images']);
                                    foreach ($images as $index => $image) {
                                        echo "<div style='display: flex; align-items: center;'>
                                            <img src='$image' alt='{$names[$index]}' style='width: 50px; height: 50px; object-fit: cover; margin-right: 10px;'>
                                            {$names[$index]}
                                          </div>";
                                    }
                                    ?>
                                </td>
                                <td><?= $row['order_quantity'] ?></td>
                                <td><?= $row['size'] ?></td>
                                <td>₱ <?= number_format($row['amount_paid'], 2) ?> - <?= $row['payment_method'] ?></td>
                                <td><?= $row['flavor'] ?></td>
                                <td><?= $row['toppings'] ?></td>
                                <td><?= $order_date ?></td>
                                <td><?= $status_text ?></td>
                                <td>
                                    <?php if ($row['status'] == 0) : ?>
                                        <form method="POST" onsubmit="return confirmCancel()">
                                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                            <button type="submit" name="cancel_order" class="cancel-btn">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div id="pagination-container_category" class="pageno">
                <?php if ($current_page > 1) : ?>
                    <div><a href="order_history.php?page=<?= $current_page - 1 ?>&status=<?= $status_filter ?>&time_frame=<?= $time_filter ?>">&laquo; Previous</a></div>
                <?php endif; ?>
                <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                    <div class="<?= $page == $current_page ? 'active' : '' ?>">
                        <a href="order_history.php?page=<?= $page ?>&status=<?= $status_filter ?>&time_frame=<?= $time_filter ?>"><?= $page ?></a>
                    </div>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages) : ?>
                    <div><a href="order_history.php?page=<?= $current_page + 1 ?>&status=<?= $status_filter ?>&time_frame=<?= $time_filter ?>">Next &raquo;</a></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        function confirmCancel() {
            return confirm("Are you sure you want to cancel this order?");
        }
    </script>
</body>

</html>