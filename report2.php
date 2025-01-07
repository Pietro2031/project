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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Report</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Order Report</h1>
    <a href="?report1">Product</a>
    <a href="?report3">Customize</a>
    <a href="?report4">Inventory</a>
    <form method="GET" action="">
        <input type="hidden" name="report2" value="1">
        <label for="status">Filter by Status:</label>
        <select name="status" id="status">
            <option value="">All</option>
            <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Pending</option>
            <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Completed</option>
            <option value="2" <?= $statusFilter === '2' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <label for="sort">Sort by Date:</label>
        <select name="sort" id="sort">
            <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>Newest First</option>
            <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Oldest First</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <table>
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
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>