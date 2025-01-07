<?php
$items_per_page = 2;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($current_page - 1) * $items_per_page;
$total_items_query = "SELECT COUNT(*) AS count FROM orders WHERE status = '-1'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_row = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_row['count'];
$total_pages = ceil($total_items / $items_per_page);

$get_pro = "SELECT * FROM orders WHERE status = 0 ORDER BY order_date DESC LIMIT $offset, $items_per_page";
$run_pro = mysqli_query($conn, $get_pro);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">View Orders</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <tbody>
                            <?php

                            while ($row_pro = mysqli_fetch_array($run_pro)) {
                                $counter = 0;
                                $order_id = $row_pro['id'];
                                $Quantity = $row_pro['order_quantity'];
                                $Size = $row_pro['size'];
                                $status = $row_pro['status'];
                                $product_ids  = $row_pro['product_ids'];
                                $customer_id = $row_pro['user_id'];
                                $order_date = $row_pro['order_date'];

                                $getuser = mysqli_query($conn, "SELECT FName FROM user_account WHERE  id  = $customer_id");
                                $CustomerName = mysqli_num_rows($getuser) > 0 ? mysqli_fetch_assoc($getuser)['FName'] : 0;

                                $getorderinfosql = "SELECT total_amount, payment_method FROM `orders` WHERE id = ?";
                                if ($stmt = mysqli_prepare($conn, $getorderinfosql)) {
                                    mysqli_stmt_bind_param($stmt, 'i', $order_id);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_bind_result($stmt, $amount_paid, $payment_mode);
                                    mysqli_stmt_fetch($stmt);
                                    mysqli_stmt_close($stmt);

                                    $price = "₱ " . $amount_paid;
                                } else {
                                    echo "Error preparing statement: " . mysqli_error($conn);
                                }

                                $product_id_array = explode(",", $product_ids);
                                $quantity_array = explode(",", $Size);


                                $items_details = '';

                                foreach ($product_id_array as $index => $product_id) {
                                    // Get item details
                                    $run_items = mysqli_prepare($conn, "SELECT * FROM coffee_products WHERE id = ?");
                                    if ($run_items) {
                                        mysqli_stmt_bind_param($run_items, 's', $product_id);
                                        mysqli_stmt_execute($run_items);
                                        $result = mysqli_stmt_get_result($run_items);
                                        if ($row_item = mysqli_fetch_array($result)) {
                                            $id = $row_item['id'];
                                            $product_name = $row_item['product_name'];
                                            $Price = $row_item['price'];
                                            $product_image = $row_item['product_image'];

                                            $item_quantity = isset($quantity_array[$index]) ? $quantity_array[$index] : 0;
                                            $Upload = isset($upload_array[$index]) ? $upload_array[$index] : 0;

                                            $items_details .= "<div class='returnitem'>
                                            <div class='returninfo'>
                                                <div class='infos1'>
                                                    <div class='img'><img src='$product_image' alt='' width='70px'></div>
                                                    <div class='info'>
                                                        <h5>$product_name</h5>
                                                        <p>Date:" . date('F j, Y', strtotime($order_date)) . "</p>
                                                    </div>
                                                </div>
                                                <div class='infos2'>
                                                    <div class='info2'>
                                                        <h4>Size</h4>
                                                        <p>$item_quantity</p>
                                                    </div>
                                                    <div class='info2'>
                                                        <h4>Price</h4>
                                                        <p>₱ $Price</p>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>";
                                            $counter++;
                                        }
                                        mysqli_stmt_close($run_items);
                                    } else {
                                        echo "Error preparing item statement: " . mysqli_error($conn);
                                    }
                                }

                            ?>
                                <tr>
                                    <td>
                                        <div class="returnitemdiv">
                                            <div class="returnheader">
                                                <div class="textheader">
                                                    <img src="css/img/i.png" alt="" width="20px">
                                                    <h3>Order Details</h3>
                                                </div>
                                                <div class="ref">Ref #<?php echo $order_id ?></div>
                                            </div>
                                            <?php echo $items_details; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="returnitemdiv">
                                            <div class="textheader">
                                                <h4>Summary</h4>
                                            </div>
                                            <div class="div1">
                                                <div class="text">
                                                    <p>Item Details</p>
                                                </div>
                                                <div class="div2">
                                                    <p class="text1"><?= $price ?></p>
                                                    <p class="text2"><?= $Quantity ?> Item/s</p>
                                                </div>
                                            </div>
                                            <div class="div1">
                                                <div class="text">
                                                    <p>Mode of Payment</p>
                                                    <p>Customer</p>
                                                </div>
                                                <div class="div2">
                                                    <p class="text2"><?= $payment_mode ?></p>
                                                    <p class="text2"><?= $CustomerName ?></p>
                                                </div>
                                            </div>
                                            <?php if ($status == -1) : ?>
                                                <p class="text3">The order is ready for shipment. Please review the details and confirm the shipment or release the order.</p>
                                                <div class="div3">
                                                    <a href="update_status.php?status=0&orderid=<?php echo $order_id; ?>" style="color: #337ab7; text-decoration: none;">Ship Out</a>
                                                    <a href="update_status.php?status=-2&orderid=<?php echo $order_id; ?>" style="color: #337ab7; text-decoration: none;">Cancel</a>
                                                </div>
                                            <?php elseif ($status == 0) : ?>
                                                <p class="text3">The order has been shipped out. Review the details if any further action is required.</p>
                                                <div class="div3">
                                                    <a href="update_status.php?status=2&orderid=<?php echo $order_id; ?>" style="color: #337ab7; text-decoration: none;">Mark as Released</a>
                                                </div>
                                            <?php elseif ($status == 1) : ?>
                                                <p class="text3">The order has been released. No further action required at this time.</p>
                                            <?php else : ?>
                                                <p class="text3">The status of this order is not applicable for this section.</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <ul class="pagination">
                        <?php if ($current_page > 1) : ?>
                            <li><a href="admin.php?view_orders&page=<?php echo $current_page - 1; ?>">&laquo; Previous</a></li>
                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                            <li <?php if ($page == $current_page) echo 'class="active"'; ?>>
                                <a href="admin.php?view_orders&page=<?php echo $page; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <li><a href="admin.php?view_orders&page=<?php echo $current_page + 1; ?>">Next &raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination ul {
        list-style-type: none;
        padding: 0;
    }

    .pagination li {
        display: inline;
        margin: 0 5px;
    }

    .pagination li a {
        text-decoration: none;
        color: #007bff;
    }

    .pagination li.active a {
        font-weight: bold;
        text-decoration: underline;
    }

    .returninfo {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        margin: 0 50px 0 0;
    }

    .returnitemdiv {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 15px;
        background-color: white;
        border: solid 1px #ddd;
        border-radius: 5px;
        min-width: 250px;
    }

    .panel-body {
        background-color: #e7e7e7;
    }

    .infos1 {
        display: flex;
        flex-direction: row;
        gap: 10px;
        width: 350px;
    }

    .infos2 {
        display: flex;
        flex-direction: row;
        gap: 40px;
    }

    .infos2 h4 {
        color: #666;
    }

    .textheader {
        display: flex;
        align-items: center;
        gap: 3px;
        padding: 10px 0;
    }

    .textheader img {
        width: 20px;
        height: 20px;
    }

    .infos2 div {
        min-width: 100px;
    }

    .info p {
        font-size: 12px;
        color: #333;
    }

    .div1 {
        display: flex;
        justify-content: space-between;
    }

    .div2 {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .div1 .text {
        color: #666;
        font-size: 11px;
        font-weight: 700;
    }

    p.text1 {
        font-weight: 700;
        font-size: 14px;
    }

    p.text2 {
        font-weight: 700;
        font-size: 12px;
        color: #666;
    }

    p.text3 {
        font-weight: 700;
        font-size: 12px;
        color: #666;
    }

    .div3 {
        display: flex;
        gap: 15px;
    }

    .info {
        padding: 0 10px 0 0;
    }
</style>