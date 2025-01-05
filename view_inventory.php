<?php
// Connect to the database
include 'connection.php';

// Get selected category and type from GET request
$selected_category = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$selected_type = isset($_GET['item_type']) ? $_GET['item_type'] : '';

// Items per page and current page setup
$items_per_page = 8;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Queries for fetching items for each table (with category filter applied)
$where_clause_base = $selected_category ? "WHERE base_name LIKE '%" . mysqli_real_escape_string($conn, $selected_category) . "%'" : '';
$where_clause_flavor = $selected_category ? "WHERE flavor_name LIKE '%" . mysqli_real_escape_string($conn, $selected_category) . "%'" : '';
$where_clause_topping = $selected_category ? "WHERE topping_name LIKE '%" . mysqli_real_escape_string($conn, $selected_category) . "%'" : '';
$where_clause_cup_size = $selected_category ? "WHERE size LIKE '%" . mysqli_real_escape_string($conn, $selected_category) . "%'" : '';

// Queries to fetch data with limit and filter
$baseQuery = "SELECT * FROM coffee_base $where_clause_base LIMIT $offset, $items_per_page";
$flavorQuery = "SELECT * FROM coffee_flavors $where_clause_flavor LIMIT $offset, $items_per_page";
$toppingQuery = "SELECT * FROM coffee_toppings $where_clause_topping LIMIT $offset, $items_per_page";
$cupSizeQuery = "SELECT * FROM cup_size $where_clause_cup_size LIMIT $offset, $items_per_page";

// Fetch the data for each table
$baseResult = mysqli_query($conn, $baseQuery);
$flavorResult = mysqli_query($conn, $flavorQuery);
$toppingResult = mysqli_query($conn, $toppingQuery);
$cupSizeResult = mysqli_query($conn, $cupSizeQuery);

// Fetch total count of records for pagination
$totalBasesQuery = "SELECT COUNT(*) AS count FROM coffee_base $where_clause_base";
$totalFlavorsQuery = "SELECT COUNT(*) AS count FROM coffee_flavors $where_clause_flavor";
$totalToppingsQuery = "SELECT COUNT(*) AS count FROM coffee_toppings $where_clause_topping";
$totalCupSizesQuery = "SELECT COUNT(*) AS count FROM cup_size $where_clause_cup_size";

$totalBasesResult = mysqli_query($conn, $totalBasesQuery);
$totalFlavorsResult = mysqli_query($conn, $totalFlavorsQuery);
$totalToppingsResult = mysqli_query($conn, $totalToppingsQuery);
$totalCupSizesResult = mysqli_query($conn, $totalCupSizesQuery);

$totalBases = mysqli_fetch_assoc($totalBasesResult)['count'];
$totalFlavors = mysqli_fetch_assoc($totalFlavorsResult)['count'];
$totalToppings = mysqli_fetch_assoc($totalToppingsResult)['count'];
$totalCupSizes = mysqli_fetch_assoc($totalCupSizesResult)['count'];

// Calculate the total number of pages
$total_items = max($totalBases, $totalFlavors, $totalToppings, $totalCupSizes);
$total_pages = ceil($total_items / $items_per_page);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">View Inventory</h3>
            </div>
            <div class="panel-body">
                <!-- Filter Form -->
                <form method="GET" action="admin.php">
                    <div class="form-group">
                        <input type="hidden" name="view_inventory">
                        <label for="item_type">Select Item Type:</label>
                        <select name="item_type" id="item_type" class="form-control" onchange="this.form.submit()">
                            <option value="">Select Item Type</option>
                            <option value="coffee_base" <?php if ($selected_type == 'coffee_base') echo 'selected'; ?>>Coffee Base</option>
                            <option value="coffee_flavors" <?php if ($selected_type == 'coffee_flavors') echo 'selected'; ?>>Coffee Flavors</option>
                            <option value="coffee_toppings" <?php if ($selected_type == 'coffee_toppings') echo 'selected'; ?>>Coffee Toppings</option>
                            <option value="cup_size" <?php if ($selected_type == 'cup_size') echo 'selected'; ?>>Cup Size</option>
                        </select>
                    </div>

                    <?php if ($selected_type) : ?>
                        <div class="form-group">
                            <label for="category">Filter by Category:</label>
                            <?php
                            if ($selected_type == 'coffee_base') {
                                $query = "SELECT DISTINCT base_name FROM coffee_base";
                            } elseif ($selected_type == 'coffee_flavors') {
                                $query = "SELECT DISTINCT flavor_name FROM coffee_flavors";
                            } elseif ($selected_type == 'coffee_toppings') {
                                $query = "SELECT DISTINCT topping_name FROM coffee_toppings";
                            } elseif ($selected_type == 'cup_size') {
                                $query = "SELECT DISTINCT size FROM cup_size";
                            }

                            $result = mysqli_query($conn, $query);
                            ?>
                            <select name="category_id" id="category" class="form-control" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                    <option value="<?php echo $row['base_name'] ?: $row['flavor_name'] ?: $row['topping_name'] ?: $row['size']; ?>"
                                        <?php if ($selected_category == $row['base_name'] || $selected_category == $row['flavor_name'] || $selected_category == $row['topping_name'] || $selected_category == $row['size']) echo 'selected'; ?>>
                                        <?php echo $row['base_name'] ?: $row['flavor_name'] ?: $row['topping_name'] ?: $row['size']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </form>

                <!-- Display Inventory Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Coffee Base Items -->
                            <?php if ($selected_type == 'coffee_base' || !$selected_type) {
                                $i = $offset;
                                while ($row_base = mysqli_fetch_array($baseResult)) {
                                    $base_id = $row_base['id'];
                                    $base_name = $row_base['base_name'];
                                    $base_image = $row_base['img'];
                                    $base_price = $row_base['price'];
                                    $base_quantity = $row_base['quantity'];
                                    $i++;
                            ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $base_name; ?></td>
                                        <td><img src="<?php echo $base_image; ?>" width="60" height="60"></td>
                                        <td>₱ <?php echo $base_price; ?></td>
                                        <td><?php echo $base_quantity; ?></td>
                                        <td><a href="admin.php?edit_base&id=<?php echo $base_id; ?>" style="color: #337ab7; text-decoration: none;">Edit</a></td>
                                    </tr>
                            <?php }
                            } ?>

                            <!-- Coffee Flavor Items -->
                            <?php if ($selected_type == 'coffee_flavors' || !$selected_type) {
                                $i = $offset;
                                while ($row_flavor = mysqli_fetch_array($flavorResult)) {
                                    $flavor_id = $row_flavor['id'];
                                    $flavor_name = $row_flavor['flavor_name'];
                                    $flavor_image = $row_flavor['img'];
                                    $flavor_price = $row_flavor['price'];
                                    $flavor_quantity = $row_flavor['quantity'];
                                    $i++;
                            ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $flavor_name; ?></td>
                                        <td><img src="<?php echo $flavor_image; ?>" width="60" height="60"></td>
                                        <td>₱ <?php echo $flavor_price; ?></td>
                                        <td><?php echo $flavor_quantity; ?></td>
                                        <td><a href="admin.php?edit_flavor&id=<?php echo $flavor_id; ?>" style="color: #337ab7; text-decoration: none;">Edit</a></td>
                                    </tr>
                            <?php }
                            } ?>

                            <!-- Coffee Topping Items -->
                            <?php if ($selected_type == 'coffee_toppings' || !$selected_type) {
                                $i = $offset;
                                while ($row_topping = mysqli_fetch_array($toppingResult)) {
                                    $topping_id = $row_topping['id'];
                                    $topping_name = $row_topping['topping_name'];
                                    $topping_image = $row_topping['img'];
                                    $topping_price = $row_topping['price'];
                                    $topping_quantity = $row_topping['quantity'];
                                    $i++;
                            ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $topping_name; ?></td>
                                        <td><img src="<?php echo $topping_image; ?>" width="60" height="60"></td>
                                        <td>₱ <?php echo $topping_price; ?></td>
                                        <td><?php echo $topping_quantity; ?></td>
                                        <td><a href="admin.php?edit_topping&id=<?php echo $topping_id; ?>" style="color: #337ab7; text-decoration: none;">Edit</a></td>
                                    </tr>
                            <?php }
                            } ?>

                            <!-- Cup Size Items -->
                            <?php if ($selected_type == 'cup_size' || !$selected_type) {
                                $i = $offset;
                                while ($row_cup_size = mysqli_fetch_array($cupSizeResult)) {
                                    $cup_size_id = $row_cup_size['id'];
                                    $cup_size_name = $row_cup_size['size'];
                                    $cup_size_image = $row_cup_size['img'];
                                    $cup_size_price = $row_cup_size['price'];
                                    $cup_size_quantity = $row_cup_size['quantity'];
                                    $i++;
                            ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $cup_size_name; ?></td>
                                        <td><img src="<?php echo $cup_size_image; ?>" width="60" height="60"></td>
                                        <td>₱ <?php echo $cup_size_price; ?></td>
                                        <td><?php echo $cup_size_quantity; ?></td>
                                        <td><a href="admin.php?edit_cup_size&id=<?php echo $cup_size_id; ?>" style="color: #337ab7; text-decoration: none;">Edit</a></td>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <ul class="pagination">
                        <?php if ($current_page > 1) : ?>
                            <li><a href="?page=<?php echo $current_page - 1; ?>&category_id=<?php echo $selected_category; ?>&item_type=<?php echo $selected_type; ?>">&laquo; Previous</a></li>
                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
                            <li <?php if ($page == $current_page) echo 'class="active"'; ?>>
                                <a href="?page=<?php echo $page; ?>&category_id=<?php echo $selected_category; ?>&item_type=<?php echo $selected_type; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <li><a href="?page=<?php echo $current_page + 1; ?>&category_id=<?php echo $selected_category; ?>&item_type=<?php echo $selected_type; ?>">Next &raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>