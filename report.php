<?php
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'ASC';


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
                <h3 class="panel-title">Product Report</h3>
                <div style="display: flex; gap: 5px;">
                    <a href="?report" class="reportselected">Product</a>
                    <a href="?report2">Orders</a>
                    <a href="?report3">Customize</a>
                    <a href="?report4">Inventory</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="GET" action="" class="slideright">
                    <input type="hidden" name="report" value="1">
                    <div class="slidedown">
                        <label for="category">Filter by Category:</label>
                        <select name="category" id="category" class="form-control">
                            <option value="">All</option>
                            <?php
                            $categoryResult = $conn->query("SELECT id, category_name FROM coffee_category");
                            while ($row = $categoryResult->fetch_assoc()) {
                                $selected = $row['id'] == $categoryFilter ? 'selected' : '';
                                echo "<option value='{$row['id']}' $selected>{$row['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="slidedown">
                        <label for="sort">Sort by Price:</label>
                        <select name="sort" id="sort" class="form-control">
                            <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Low to High</option>
                            <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>High to Low</option>
                        </select>
                    </div>
                    <input type="submit" value="Apply" class="submit-btn-filter">
                    <a href="print-product.php" target="_blank" class="submit-btn-filter">Print</a>
                </form>

                <form method="GET" action="delete_product.php">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
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
                                        echo "<td>₱{$row['price']}</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No products found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$stmt->close();
$conn->close();
?>