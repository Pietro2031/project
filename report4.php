<?php


// Get filter values
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$quantityFilter = isset($_GET['quantity_filter']) ? $_GET['quantity_filter'] : 'all';

// Build the SQL queries
$queries = [];
if ($category === 'all' || $category === 'base') {
    $queries[] = "SELECT 'Base' AS category, base_name AS item_name, quantity, price, img FROM coffee_base";
}
if ($category === 'all' || $category === 'flavors') {
    $queries[] = "SELECT 'Flavor' AS category, flavor_name AS item_name, quantity, price, img FROM coffee_flavors";
}
if ($category === 'all' || $category === 'toppings') {
    $queries[] = "SELECT 'Topping' AS category, topping_name AS item_name, quantity, price, img FROM coffee_toppings";
}

// Combine queries
$query = implode(" UNION ", $queries);

// Apply quantity filter
if ($quantityFilter !== 'all') {
    $quantityCondition = "";
    if ($quantityFilter === 'low') {
        $quantityCondition = "quantity < 50";
    } elseif ($quantityFilter === 'medium') {
        $quantityCondition = "quantity BETWEEN 50 AND 200";
    } elseif ($quantityFilter === 'high') {
        $quantityCondition = "quantity > 200";
    }

    // Apply filter to all queries if 'all' category is selected
    if ($category === 'all') {
        $query = "SELECT * FROM (" . $query . ") AS combined WHERE " . $quantityCondition;
    } else {
        $query .= " WHERE " . $quantityCondition;
    }
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Report</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Inventory Report</h1>
    <form method="GET" action="">
        <input type="hidden" name="report4" value="1">

        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All</option>
            <option value="base" <?= $category === 'base' ? 'selected' : '' ?>>Base</option>
            <option value="flavors" <?= $category === 'flavors' ? 'selected' : '' ?>>Flavors</option>
            <option value="toppings" <?= $category === 'toppings' ? 'selected' : '' ?>>Toppings</option>
        </select>

        <label for="quantity_filter">Quantity Level:</label>
        <select name="quantity_filter" id="quantity_filter">
            <option value="all" <?= $quantityFilter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="low" <?= $quantityFilter === 'low' ? 'selected' : '' ?>>Low (Less than 50)</option>
            <option value="medium" <?= $quantityFilter === 'medium' ? 'selected' : '' ?>>Medium (50 to 200)</option>
            <option value="high" <?= $quantityFilter === 'high' ? 'selected' : '' ?>>High (More than 200)</option>
        </select>

        <input type="submit" value="Apply">
    </form>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['category']}</td>";
                    echo "<td>{$row['item_name']}</td>";
                    echo "<td>{$row['quantity']}</td>";
                    echo "<td>₱{$row['price']}</td>";
                    echo "<td><img src='{$row['img']}' alt='{$row['item_name']}' width='50'></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No items found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>

</html>

<?php
$conn->close();
?>