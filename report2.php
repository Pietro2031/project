<?php

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';

// Build the SQL query with optional filter and sort
$query = "SELECT o.id, o.user_id, o.order_date, o.total_amount, o.order_quantity, o.product_ids, o.status, o.payment_method, o.flavor, o.toppings 
          FROM orders o";

if ($statusFilter !== '') {
    $query .= " WHERE o.status = ?";
}

$query .= " ORDER BY o.order_date $sortOrder";

$stmt = $conn->prepare($query);

if ($statusFilter !== '') {
    $stmt->bind_param("i", $statusFilter);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<style>
    .slideright .slidedown {
        width: 100%;
    }

    .slideright {
        gap: 10px;
        padding: 10px;
        align-items: flex-end;
    }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Order Report</h3>
                <div style="display: flex; gap: 5px;">
                    <a href="?report">Product</a>
                    <a href="?report2" class="reportselected">Orders</a>
                    <a href="?report3">Customize</a>
                    <a href="?report4">Inventory</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="GET" action="" class="slideright">
                    <input type="hidden" name="report2" value="1">
                    <div class="slidedown">
                        <label for="status">Filter by Status:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All</option>
                            <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Pending</option>
                            <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Completed</option>
                            <option value="2" <?= $statusFilter === '2' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="slidedown">
                        <label for="sort">Sort by Date:</label>
                        <select name="sort" id="sort" class="form-control">
                            <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>Newest First</option>
                            <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                    </div>
                    <button type="submit">Apply</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User ID</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Quantity</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Flavor</th>
                                <th>Toppings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>{$row['user_id']}</td>";
                                    echo "<td>{$row['order_date']}</td>";
                                    echo "<td>₱{$row['total_amount']}</td>";
                                    echo "<td>{$row['order_quantity']}</td>";
                                    echo "<td>{$row['product_ids']}</td>";
                                    echo "<td>" . ($row['status'] == 1 ? 'Completed' : ($row['status'] == 2 ? 'Cancelled' : 'Pending')) . "</td>";
                                    echo "<td>{$row['payment_method']}</td>";
                                    echo "<td>{$row['flavor']}</td>";
                                    echo "<td>{$row['toppings']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10'>No orders found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
$stmt->close();
$conn->close();
?>