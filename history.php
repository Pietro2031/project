<!DOCTYPE html>
<html lang="en">

<?php
include("connection.php");

session_start();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
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
            border: 1px solid #ddd;
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
</head>

<body>
    <?php include("header.php"); ?>

    <?php
    $items_per_page = 4;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $items_per_page;

    $total_items_query = "
        SELECT COUNT(DISTINCT orders.id) AS count 
        FROM coffee_products 
        JOIN orders ON FIND_IN_SET(coffee_products.id, orders.id) 
        WHERE orders.user_id = '$userId'";
    $total_items_result = mysqli_query($conn, $total_items_query);
    $total_items = mysqli_fetch_assoc($total_items_result)['count'];

    $total_pages = ceil($total_items / $items_per_page);

    $get_pro = "
    SELECT 
        orders.id AS order_id, 
        orders.order_date, 
        orders.order_quantity, 
        orders.status,
        GROUP_CONCAT(coffee_products.product_name SEPARATOR ', ') AS item_names,
        GROUP_CONCAT(coffee_products.product_image SEPARATOR ', ') AS item_images,
        SUM(payment.amount_paid) AS amount_paid,
        payment.payment_mode
    FROM orders
    JOIN coffee_products ON coffee_products.id = orders.product_ids
    JOIN payment ON orders.id = payment.order_id
    WHERE orders.user_id = '$userId'
    GROUP BY orders.id, orders.order_date, orders.order_quantity, orders.status, payment.payment_mode
    ORDER BY orders.order_date DESC
    LIMIT $offset, $items_per_page";
    $run_pro = mysqli_query($conn, $get_pro);
    // echo $get_pro;
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
            <th>Details</th>
            <th>Order Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row_pro = mysqli_fetch_array($run_pro)) : ?>
            <?php
            $order_date = date('F j, Y', strtotime($row_pro['order_date']));
            $price = "₱ " . $row_pro['amount_paid'];
            $status_text = [
                "-1" => "Processing",
                "0" => "Placed",
                "1" => "Received",
                "2" => "Pending Return",
                "3" => "Return Approved",
                "4" => "Request Rejected"
            ][$row_pro['status']];
            ?>
            <tr>
                <td><?= $row_pro['order_id'] ?></td>
                <td>
                    <div>
                        <?php
                        $names = explode(', ', $row_pro['item_names']);
                        $images = explode(', ', $row_pro['item_images']);
                        foreach ($images as $index => $image) :
                        ?>
                            <div>
                                <img src="<?= $image ?>" alt="<?= $names[$index] ?>">
                                <?= $names[$index] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </td>
                <td><?= $row_pro['order_quantity'] ?></td> 
                <td><?= $price ?> - <?= $row_pro['payment_mode'] ?></td>
                <td><?= $order_date ?></td>
                <td><?= $status_text ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

            </div>

            <div id="pagination-container_category" class="pageno">
                <?php if ($current_page > 1) : ?>
                    <div><a href="history.php?page=<?= $current_page - 1 ?>">&laquo; Previous</a></div>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                    <div class="<?= $page == $current_page ? 'active' : '' ?>">
                        <a href="history.php?page=<?= $page ?>"><?= $page ?></a>
                    </div>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages) : ?>
                    <div><a href="history.php?page=<?= $current_page + 1 ?>">Next &raquo;</a></div>
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

    <script>
        function recivedorder(order_id) {
            if (confirm("Are you sure you have received the order?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "update_order_status.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert(xhr.responseText);
                        location.reload();
                    }
                };
                xhr.send("action=received&order_id=" + order_id);
            }
        }

        function returnOrder(order_id) {
            document.getElementById('returnModal').style.display = 'flex';
            currentOrderId = order_id;
        }

        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
        }

        function submitReturnOrder() {
            const reason = document.getElementById('returnReason').value;
            if (reason) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "update_order_status.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert(xhr.responseText);
                        location.reload();
                    }
                };
                xhr.send("action=return&order_id=" + currentOrderId + "&reason=" + encodeURIComponent(reason));
            } else {
                alert("Please select a reason for the return.");
            }
        }
    </script>

</body>

</html>