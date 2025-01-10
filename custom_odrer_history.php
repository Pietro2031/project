<?php
include("connection.php");
session_start();

// Retrieve the logged-in user's username
$userId = null;
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT userName FROM user_account WHERE userName = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userId = $result->fetch_assoc()['userName'];
    }
    $stmt->close();
}

if (!$userId) {
    die("User not logged in.");
}

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $update_status_query = "UPDATE custom_drink SET status = 2 WHERE id = ? AND username = ?";
    $stmt = $conn->prepare($update_status_query);
    $stmt->bind_param("is", $order_id, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: custom_odrer_history.php");
    exit();
}

// Handle marking order as received
if (isset($_POST['mark_received'])) {
    $order_id = $_POST['order_id'];
    $update_status_query = "UPDATE custom_drink SET status = 3 WHERE id = ? AND username = ?";
    $stmt = $conn->prepare($update_status_query);
    $stmt->bind_param("is", $order_id, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: custom_odrer_history.php");
    exit();
}

// Filters for status and time frame
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$time_filter = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'any';

$time_conditions = "";
if ($time_filter === '3d') {
    $time_conditions = "AND custom_drink.created_at >= NOW() - INTERVAL 3 DAY";
} elseif ($time_filter === '7d') {
    $time_conditions = "AND custom_drink.created_at >= NOW() - INTERVAL 7 DAY";
} elseif ($time_filter === '1m') {
    $time_conditions = "AND custom_drink.created_at >= NOW() - INTERVAL 1 MONTH";
}

$status_conditions = $status_filter >= 0 ? "AND custom_drink.status = $status_filter" : "";

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Total items query
$total_items_query = "
    SELECT COUNT(DISTINCT custom_drink.id) AS count 
    FROM custom_drink 
    WHERE custom_drink.username = ? $status_conditions $time_conditions";
$stmt = $conn->prepare($total_items_query);
$stmt->bind_param("s", $userId);
$stmt->execute();
$total_items_result = $stmt->get_result();
$total_items = $total_items_result->fetch_assoc()['count'];
$total_pages = ceil($total_items / $items_per_page);
$stmt->close();

// Fetch custom drink orders
$get_custom_drink = "
    SELECT 
        custom_drink.id AS order_id,
        custom_drink.created_at,
        custom_drink.order_quantity,
        custom_drink.status,
        custom_drink.topping_names,
        size_name,
        flavor_name,
        custom_drink.toppings,
        GROUP_CONCAT(coffee_base.base_name SEPARATOR ', ') AS item_names,
        GROUP_CONCAT(coffee_base.img SEPARATOR ', ') AS item_images,
        custom_drink.total_price AS amount_paid,
        custom_drink.payment_method
    FROM custom_drink
    LEFT JOIN coffee_base ON FIND_IN_SET(coffee_base.id, custom_drink.base_id)
    WHERE custom_drink.username = ? $status_conditions $time_conditions
    GROUP BY custom_drink.id
    ORDER BY custom_drink.created_at DESC
    LIMIT ?, ?";
$stmt = $conn->prepare($get_custom_drink);
$stmt->bind_param("sii", $userId, $offset, $items_per_page);
$stmt->execute();
$run_custom_drink = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/history.css">
    <style>
        .slideright2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <section class="center">
        <div class="Itemcart">
            <div class="slideright2">
                <h1>Custom Drink History</h1>
                <a href="order_history.php"> View regular orders</a>
            </div>
            <div class="filter-container">
                <form method="GET" action="custom_drink_history.php">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="-1" <?= $status_filter === -1 ? 'selected' : '' ?>>All</option>
                        <option value="0" <?= $status_filter === 0 ? 'selected' : '' ?>>Placed</option>
                        <option value="2" <?= $status_filter === 2 ? 'selected' : '' ?>>Canceled</option>
                        <option value="3" <?= $status_filter === 3 ? 'selected' : '' ?>>Delivered</option>
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
                        <?php while ($row = $run_custom_drink->fetch_assoc()) :
                            $created_at = date('F j, Y', strtotime($row['created_at']));
                            $status_text = [
                                "0" => "Placed",
                                "1" => "To Receive",
                                "2" => "Canceled",
                                "3" => "Delivered"
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
                                <td><?= $row['size_name'] ?></td>
                                <td>₱ <?= number_format($row['amount_paid'], 2) ?> - <?= $row['payment_method'] ?></td>
                                <td><?= $row['flavor_name'] ?></td>
                                <td><?= $row['topping_names'] ?></td>
                                <td><?= $created_at ?></td>
                                <td><?= $status_text ?></td>
                                <td>
                                    <?php if ($row['status'] == 0) : ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                            <button type="submit" name="cancel_order" class="cancel-btn">Cancel</button>
                                        </form>
                                    <?php elseif ($row['status'] == 1) : ?>
                                        <form method="POST">
                                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                            <button type="submit" name="mark_received" class="received-btn">Received</button>
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
                    <div><a href="custom_drink_history.php?page=<?= $current_page - 1 ?>&status=<?= $status_filter ?>&time_frame=<?= $time_filter ?>">&laquo; Previous</a></div>
                <?php endif; ?>
                <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                    <div class="<?= $page == $current_page ? 'active' : '' ?>">
                        <a href="custom_drink_history.php?page=<?= $page ?>&status=<?= $status_filter ?>&time_frame=<?= $time_filter ?>"><?= $page ?></a>
                    </div>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages) : ?>
                    <div><a href="custom_drink_history.php?page=<?= $current_page + 1 ?>&status=<?= $status_filter ?>&time_frame=<?= $time_filter ?>">Next &raquo;</a></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>

</html>