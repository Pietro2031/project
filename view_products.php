<?php
$categories_query = "SELECT DISTINCT category_id FROM coffee_products";
$categories_result = mysqli_query($conn, $categories_query);

$selected_category = isset($_GET['category_id']) ? $_GET['category_id'] : '';

$items_per_page = 8;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($current_page - 1) * $items_per_page;

$where_clause = '';
if ($selected_category) {
    $where_clause = "WHERE category_id = '" . mysqli_real_escape_string($conn, $selected_category) . "'";
}

$total_items_query = "SELECT COUNT(*) AS count FROM coffee_products $where_clause";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_row = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_row['count'];

$total_pages = ceil($total_items / $items_per_page);

$get_pro = "SELECT * FROM coffee_products $where_clause LIMIT $offset, $items_per_page";
$run_pro = mysqli_query($conn, $get_pro);

if (isset($_POST['delete_selected'])) {
    if (isset($_POST['delete_ids']) && !empty($_POST['delete_ids'])) {
        $delete_ids = implode(",", $_POST['delete_ids']);
        header("Location: delete_product.php?ItemIDs=$delete_ids");
        exit();
    } else {
        echo "No products selected.";
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">View Products</h3>
                <div style="display: flex; gap: 5px;">
                    <a href="?insert_products">Add Products</a>
                    <a href="?view_inventory">Inventory</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="GET" action="admin.php">
                    <input type="hidden" name="view_products" value="1">
                    <div class="form-group">
                        <label for="category">Filter by Category:</label>
                        <select name="category_id" id="category" class="form-control" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php while ($category_row = mysqli_fetch_assoc($categories_result)) : ?>
                                <option value="<?php echo $category_row['category_id']; ?>" <?php if ($selected_category == $category_row['category_id']) echo 'selected'; ?>>
                                    <?php echo $category_row['category_id']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>

                <form method="GET" action="delete_product.php">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onclick="selectAllProducts()"></th>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Image</th>
                                    <th>Price</th>
                                    <th>Sold</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = $offset;
                                while ($row_pro = mysqli_fetch_array($run_pro)) {
                                    $pro_id = $row_pro['id'];
                                    $pro_title = $row_pro['product_name'];
                                    $pro_image = $row_pro['product_image'];
                                    $pro_price = $row_pro['price'];
                                    $total_sales = $row_pro['total_sales'];
                                    $i++;
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="delete_ids[]" value="<?php echo $pro_id; ?>"></td>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $pro_title; ?></td>
                                        <td><img src="<?php echo $pro_image; ?>" width="60" height="60"></td>
                                        <td>₱ <?php echo $pro_price; ?></td>
                                        <td><?php echo $total_sales; ?></td>
                                        <td><a href="admin.php?edit_product&id=<?php echo $pro_id; ?>" style="color: #337ab7; text-decoration: none;">Edit</a></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected</button>
                </form>
                <div class="pagination">
                    <ul class="pagination">
                        <?php if ($current_page > 1) : ?>
                            <li><a href="?view_products&category_id=<?php echo $selected_category; ?>&page=<?php echo $current_page - 1; ?>">&laquo; Previous</a></li>
                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                            <li <?php if ($page == $current_page) echo 'class="active"'; ?>>
                                <a href="?view_products&category_id=<?php echo $selected_category; ?>&page=<?php echo $page; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <li><a href="?view_products&category_id=<?php echo $selected_category; ?>&page=<?php echo $current_page + 1; ?>">Next &raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this product?');
    }

    function selectAllProducts() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
</script>