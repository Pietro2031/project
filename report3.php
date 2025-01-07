<?php
$timeFrame = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'any';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Build the SQL query with time frame filter
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Drink Report</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Custom Drink Report</h1>
    <a href="?report">Product</a>
    <a href="?report2">Orders</a>
    <a href="?report4">Inventory</a>
    <form method="GET" action="">
        <input type="hidden" name="report3" value="1">

        <label for="time_frame">Time Frame:</label>
        <select name="time_frame" id="time_frame">
            <option value="any" <?= $timeFrame === 'any' ? 'selected' : '' ?>>Any Time</option>
            <option value="3day" <?= $timeFrame === '3day' ? 'selected' : '' ?>>Last 3 Days</option>
            <option value="7day" <?= $timeFrame === '7day' ? 'selected' : '' ?>>Last 7 Days</option>
            <option value="1month" <?= $timeFrame === '1month' ? 'selected' : '' ?>>Last 1 Month</option>
        </select>

        <label for="sort_order">Sort by Price:</label>
        <select name="sort_order" id="sort_order">
            <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Ascending</option>
            <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>Descending</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <table>
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
</body>

</html>

<?php
$conn->close();
?>