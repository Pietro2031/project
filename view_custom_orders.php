<?php
$items_per_page = 3;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($current_page - 1) * $items_per_page;
$total_items_query = "SELECT COUNT(*) AS count FROM orders WHERE status = '0'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_row = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_row['count'];
$total_pages = ceil($total_items / $items_per_page);

$get_pro = "SELECT * FROM custom_drink WHERE status = 0 ORDER BY created_at ASC LIMIT $offset, $items_per_page";
$run_pro = mysqli_query($conn, $get_pro);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">In Queue Custom orders</h3>
                <div style="display: flex; gap: 5px;">
                    <a href="?view_order">Regular orders</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <tbody>
                            <?php
                            while ($row_pro = mysqli_fetch_array($run_pro)) : ?>
                                <?php
                                $counter = 0;
                                $order_id = $row_pro['id'];
                                $Quantity = 1;
                                $status = $row_pro['status'];
                                $base_name  = $row_pro['base_name'];
                                $size_name = $row_pro['size_name'];
                                $order_date = $row_pro['created_at'];
                                $username = $row_pro['username'];
                                $total_price = $row_pro['total_price'];
                                $topping_names  = $row_pro['topping_names'];
                                $flavor_name  = $row_pro['flavor_name'];
                                $payment_method  = $row_pro['payment_method'];
                                $topping_names_array = explode(",", $topping_names);

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
                                            <div class="order-card">

                                                <div class="order-details">
                                                    <div class='infos2'>
                                                        <div class='info2'>
                                                            <h4>Size</h4>
                                                            <p><?= $size_name ?></p>
                                                        </div>
                                                        <div class='info2'>
                                                            <h4>Price</h4>
                                                            <p>₱ <?= $total_price ?></p>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class='infos2'>
                                                        <div class='info2'>
                                                            <h4>Base</h4>
                                                            <p><?= $base_name ?></p>
                                                        </div>
                                                        <div class='info2'>
                                                            <h4>Flavor</h4>
                                                            <p><?= $flavor_name ?></p>
                                                        </div>
                                                        <div class='info2'>
                                                            <h4>Toppings</h4>
                                                            <p> <?php echo !empty($topping_names_array) ? implode(', ', $topping_names_array) : 'None'; ?></p>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class='infos2'>
                                                        <p>Date:<?php echo date('F j, Y, g:i a', strtotime($order_date)); ?></p>
                                                    </div>
                                                </div>
                                            </div>
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
                                                    <p class="text1"><?= $total_price ?></p>
                                                    <p class="text2"><?= $Quantity ?> Item/s</p>
                                                </div>
                                            </div>
                                            <div class="div1">
                                                <div class="text">
                                                    <p>Mode of Payment</p>
                                                    <p>Customer</p>
                                                </div>
                                                <div class="div2">
                                                    <p class="text2"><?= $payment_method ?></p>
                                                    <p class="text2"><?= $username ?></p>
                                                </div>
                                            </div>
                                            <?php if ($status == 0) : ?>
                                                <p class="text3">A new order has been placed. Please review the details and to release the order.</p>
                                                <div class="div3">
                                                    <a href="update_custom_status.php?status=1&orderid=<?php echo $order_id; ?>" style="color: #337ab7; text-decoration: none;">Mark as Released</a>
                                                    <a href="update_custom_status.php?status=2&orderid=<?php echo $order_id; ?>" style="color: #337ab7; text-decoration: none;">Cancel</a>
                                                </div>
                                            <?php else : ?>
                                                <p class="text3">The status of this order is not applicable for this section.</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <ul class="pagination">
                        <?php if ($current_page > 1) : ?>
                            <li><a href="admin.php?view_custom_orders&page=<?php echo $current_page - 1; ?>">&laquo; Previous</a></li>
                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                            <li <?php if ($page == $current_page) echo 'class="active"'; ?>>
                                <a href="admin.php?view_custom_orders&page=<?php echo $page; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <li><a href="admin.php?view_custom_orders&page=<?php echo $current_page + 1; ?>">Next &raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>