<!DOCTYPE html>
<html lang="en">
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
?>

<link rel="stylesheet" href="css/global.css">
<link rel="stylesheet" href="css/history.css">
<style>
    /* Responsive table styles */
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

<?php
$items_per_page = 4;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;
$total_items_query = "
        SELECT COUNT(DISTINCT orders.id) AS count 
        FROM orders 
        WHERE orders.user_id = '$userId'";
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
    LEFT JOIN payment ON orders.id = payment.order_id
    WHERE orders.user_id = '$userId'
    GROUP BY orders.id, orders.order_date, orders.order_quantity, orders.status, payment.payment_mode, orders.size
    ORDER BY orders.order_date DESC
    LIMIT $offset, $items_per_page";

$run_orders = mysqli_query($conn, $get_orders);
?>

<section class="center">
    <div class="Itemcart">
        <h1>Purchase History</h1>
        <div class="cart-container">
            <table class="itemtable">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Details</th>
                        <th>Favor</th>
                        <th>Toppings</th>
                        <th>Order Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_array($run_orders)) : ?>
                        <?php
                        $order_date = date('F j, Y', strtotime($row['order_date']));
                        $price = "₱ " . $row['amount_paid'];
                        $status_text = [

                            "0" => "Placed",
                            "1" => "Deliverd",
                            "2" => "Canceled"
                        ][$row['status']];
                        ?>
                        <tr>
                            <td><?= $row['order_id'] ?></td>
                            <td>
                                <div>
                                    <?php
                                    $names = explode(', ', $row['item_names']);
                                    $images = explode(', ', $row['item_images']);
                                    foreach ($images as $index => $image) :
                                    ?>
                                        <div style=" display: flex; flex-direction: row; align-items: center; ">
                                            <img src="<?= $image ?>" alt="<?= $names[$index] ?>">
                                            <?= $names[$index] ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td><?= $row['order_quantity'] ?></td>
                            <td><?= $row['size'] ?></td>
                            <td><?= $price ?> - <?= $row['payment_method'] ?></td>
                            <td><?= $row['flavor'] ?></td>
                            <td><?= $row['toppings'] ?></td>
                            <td><?= $order_date . "<br>" . $row['order_date'] ?></td>
                            <td><?= $status_text ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="pagination-container_category" class="pageno">
            <?php if ($current_page > 1) : ?>
                <div><a href="order_history.php?page=<?= $current_page - 1 ?>">&laquo; Previous</a></div>
            <?php endif; ?>
            <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                <div class="<?= $page == $current_page ? 'active' : '' ?>">
                    <a href="order_history.php?page=<?= $page ?>"><?= $page ?></a>
                </div>
            <?php endfor; ?>
            <?php if ($current_page < $total_pages) : ?>
                <div><a href="order_history.php?page=<?= $current_page + 1 ?>">Next &raquo;</a></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div id="returnModal">
    <div class="modal-content">
        <h3>Return Order</h3>
        <p>Please select the reason for returning the order:</p>
        <select id="returnReason">
            <option value="Damaged item">Damaged item</option>
            <option value="Received wrong item">Received wrong item</option>
            <option value="Item not as described">Item not as described</option>
            <option value="Changed mind">Changed mind</option>
            <option value="Other">Other</option>
        </select>
        <br><br>
        <button onclick="submitReturnOrder()">Submit</button>
        <button onclick="closeReturnModal()">Cancel</button>
    </div>
</div>