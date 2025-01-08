<?php
$timeFrame = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'any';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';


$query = "SELECT * FROM custom_drink";

if ($timeFrame === '3day') {
    $query .= " WHERE order_date >= NOW() - INTERVAL 3 DAY";
} elseif ($timeFrame === '7day') {
    $query .= " WHERE order_date >= NOW() - INTERVAL 7 DAY";
} elseif ($timeFrame === '1month') {
    $query .= " WHERE order_date >= NOW() - INTERVAL 1 MONTH";
}

$query .= " ORDER BY total_price $sortOrder, order_date DESC";

$result = $conn->query($query);

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
                <h3 class="panel-title">Custom Drink Report</h3>
                <div style="display: flex; gap: 5px;">
                    <a href="?report">Product</a>
                    <a href="?report2">Orders</a>
                    <a href="?report3" class="reportselected">Customize</a>
                    <a href="?report4">Inventory</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="GET" action="" class="slideright">
                    <input type="hidden" name="report2" value="1">
                    <div class="slidedown">
                        <label for="time_frame">Time Frame:</label>
                        <select name="time_frame" id="time_frame" class="form-control">
                            <option value="any" <?= $timeFrame === 'any' ? 'selected' : '' ?>>Any Time</option>
                            <option value="3day" <?= $timeFrame === '3day' ? 'selected' : '' ?>>Last 3 Days</option>
                            <option value="7day" <?= $timeFrame === '7day' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="1month" <?= $timeFrame === '1month' ? 'selected' : '' ?>>Last 1 Month</option>
                        </select>
                    </div>
                    <div class="slidedown">
                        <label for="sort_order">Sort by Price:</label>
                        <select name="sort_order" id="sort_order" class="form-control">
                            <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                            <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>Descending</option>
                        </select>
                    </div>
                    <input type="submit" value="Apply">
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer ID</th>
                                <th>Base</th>
                                <th>Ingredients</th>
                                <th>Total Price</th>
                                <th>Payment Method</th>
                                <th>Order Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>{$row['customer_id']}</td>";
                                    echo "<td>{$row['base']}</td>";
                                    echo "<td>{$row['ingredients']}</td>";
                                    echo "<td>₱{$row['total_price']}</td>";
                                    echo "<td>{$row['payment_method']}</td>";
                                    echo "<td>{$row['order_date']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No custom drinks found</td></tr>";
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
$conn->close();
?>