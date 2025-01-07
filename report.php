<?php


$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'ASC';

// Build the SQL query with optional filter and sort
$query = "SELECT p.id, p.product_name, p.product_image, p.product_description, p.price, c.category_name 
          FROM coffee_products p 
          JOIN coffee_category c ON p.category_id = c.id";

if ($categoryFilter !== '') {
    $query .= " WHERE p.category_id = ?";
}

$query .= " ORDER BY p.price $sortOrder";

$stmt = $conn->prepare($query);

if ($categoryFilter !== '') {
    $stmt->bind_param("i", $categoryFilter);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Report</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Product Report</h1>
    <a href="?report2">Orders</a>
    <a href="?report3">Customize</a>
    <a href="?report4">Inventory</a>
    <form method="GET" action="">
        <label for="category">Filter by Category:</label>
        <input type="hidden" name="report" value="1">
        <select name="category" id="category">
            <option value="">All</option>
            <?php
            $categoryResult = $conn->query("SELECT id, category_name FROM coffee_category");
            while ($row = $categoryResult->fetch_assoc()) {
                $selected = $row['id'] == $categoryFilter ? 'selected' : '';
                echo "<option value='{$row['id']}' $selected>{$row['category_name']}</option>";
            }
            ?>
        </select>
        <label for="sort">Sort by Price:</label>
        <select name="sort" id="sort">
            <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Low to High</option>
            <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>High to Low</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><img src='{$row['product_image']}' alt='{$row['product_name']}' width='100'></td>";
                    echo "<td>{$row['product_name']}</td>";
                    echo "<td>{$row['category_name']}</td>";
                    echo "<td>{$row['product_description']}</td>";
                    echo "<td>\${$row['price']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No products found</td></tr>";
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