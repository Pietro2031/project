<?php
session_start();
include('connection.php');
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$categoryQuery = "SELECT * FROM coffee_category";
$categoryResult = $conn->query($categoryQuery);
$bestSellersQuery = "
SELECT 
 coffee_products.*, 
 coffee_base.base_name, 
 coffee_flavors.flavor_name, 
 coffee_toppings.topping_name 
FROM coffee_products
LEFT JOIN coffee_base ON coffee_products.drink_bases = coffee_base.id
LEFT JOIN coffee_flavors ON coffee_products.flavor_id = coffee_flavors.id
LEFT JOIN coffee_toppings ON coffee_products.toppings_id = coffee_toppings.id
ORDER BY coffee_products.total_sales DESC
LIMIT 6
";
$bestSellersResult = $conn->query($bestSellersQuery);
$bestStampLimit = 3;
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $addons = isset($_POST['addons']) ? $_POST['addons'] : [];
    $size = isset($_POST['size']) ? $_POST['size'] : null;
    $quantity = $_POST['quantity'];
    if (!isset($_SESSION['username'])) {
        die("Error: You must be logged in to add items to the cart.");
    }
    if (!$size) {
        die("Error: Please select a size.");
    }
    $username = $_SESSION['username'];
    $userQuery = "SELECT id FROM user_account WHERE username = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("s", $username);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    if ($userResult->num_rows === 0) {
        die("Error: User not found.");
    }
    $user = $userResult->fetch_assoc();
    $userId = $user['id'];
    $userStmt->close();
    $addonsJson = json_encode($addons);
    $checkCartQuery = "
SELECT * FROM cart 
WHERE user_id = ? 
AND product_id = ? 
AND size = ? 
AND addons = ?
";
    $stmt = $conn->prepare($checkCartQuery);
    $stmt->bind_param("iiss", $userId, $productId, $size, $addonsJson);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $updateCartQuery = "
UPDATE cart 
SET quantity = quantity + ? 
WHERE user_id = ? 
AND product_id = ? 
AND size = ? 
AND addons = ?
";
        $updateStmt = $conn->prepare($updateCartQuery);
        $updateStmt->bind_param("iisss", $quantity, $userId, $productId, $size, $addonsJson);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        $insertCartQuery = "
INSERT INTO cart (user_id, product_id, quantity, size, addons) 
VALUES (?, ?, ?, ?, ?)
";
        $insertStmt = $conn->prepare($insertCartQuery);
        $insertStmt->bind_param("iiiss", $userId, $productId, $quantity, $size, $addonsJson);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $stmt->close();
}
$productQuery = "
SELECT 
 coffee_products.*, 
 coffee_base.base_name, 
 coffee_flavors.flavor_name, 
 coffee_toppings.topping_name 
FROM coffee_products
LEFT JOIN coffee_base ON coffee_products.drink_bases = coffee_base.id
LEFT JOIN coffee_flavors ON coffee_products.flavor_id = coffee_flavors.id
LEFT JOIN coffee_toppings ON coffee_products.toppings_id = coffee_toppings.id
";
if ($selectedCategory && $selectedCategory != 'best') {
    $productQuery .= " WHERE category_id = ?";
}
$stmt = $conn->prepare($productQuery);
if ($selectedCategory && $selectedCategory != 'best') {
    $stmt->bind_param("i", $selectedCategory);
}
$stmt->execute();
$productResult = $stmt->get_result();
$stmt->close();
$addonQuery = "SELECT * FROM addons";
$addonResult = $conn->query($addonQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Grid</title>
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div id="overlay" class="overlay close"></div>
    <div id="quantityModal" class="modal">
        <form id="quantityForm" action="menu.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="product_name" id="modalProductName">
            <input type="hidden" name="product_description" id="modalProductDesc">
            <input type="hidden" name="product_price" id="modalProductPrice">
            <input type="hidden" name="product_img" id="modalProductImage">
            <div class="modal-confirm-order">
                <div class="div-item-info">
                    <img class="itemimg" src="" id="modalProductImageDisplay" />
                    <div class="div-item-text-info">
                        <div class="itemname" id="modalProductNameDisplay"></div>
                        <div class="iteminfo" id="modalProductDescription"></div>
                        <div class="iteminfo" id="modalSold"></div>
                        <div class="slidedown">
                            <div class="iteminfo" id="drinkBasesDisplay"></div>,
                            <div class="iteminfo" id="flavorIdDisplay"></div>,
                            <div class="iteminfo" id="toppingsIdDisplay"></div>
                        </div>
                        <div class="div-price">
                            <div class="price" id="modalPrice"></div>
                        </div>
                    </div>
                </div>
                <div class="div-inputs">
                    <div class="div-2">
                        <div class="label-size">Quantity</div>
                        <input type="number" name="quantity" id="quantityInput" min="1" value="1">
                    </div>
                </div>
                <div class="div-inputs">
                    <div class="div-22">
                        <div class="label-size">Size</div>
                        <div class="div-size">
                            <?php
                            $cupSizesQuery = "SELECT id, size, quantity, price, img FROM cup_size";
                            $cupSizesResult = $conn->query($cupSizesQuery);
                            if ($cupSizesResult && $cupSizesResult->num_rows > 0):
                                while ($cupSize = $cupSizesResult->fetch_assoc()):
                                    $isDisabled = $cupSize['quantity'] == 0 ? 'disabled' : '';
                            ?>
                                    <div class="div-size-info">
                                        <input type="radio" name="size"
                                            value="<?php echo $cupSize['id']; ?>"
                                            id="size-<?php echo $cupSize['id']; ?>"
                                            <?php echo $isDisabled; ?> required>
                                        <label for="size-<?php echo $cupSize['id']; ?>">
                                            <?php echo $cupSize['size']; ?>
                                        </label>
                                        <p><?php echo $cupSize['quantity'] > 0 ? '+₱' . number_format($cupSize['price'], 2) : 'Out of Stock'; ?></p>
                                    </div>
                            <?php
                                endwhile;
                            endif;
                            ?>
                        </div>
                    </div>
                    <div class="div-23">
                        <div class="label-size">Add-ons</div>
                        <?php
                        $flavorsQuery = "SELECT id, flavor_name, quantity, price FROM coffee_flavors";
                        $flavorsResult = $conn->query($flavorsQuery);

                        $toppingsQuery = "SELECT id, topping_name, quantity, price FROM coffee_toppings";
                        $toppingsResult = $conn->query($toppingsQuery);
                        ?>
                        <div class="div-add-ons" id="addonsContainer">
                            <h4>Flavors</h4>
                            <div class="sliderights">
                                <?php if ($flavorsResult && $flavorsResult->num_rows > 0): ?>
                                    <?php while ($flavor = $flavorsResult->fetch_assoc()): ?>
                                        <?php $isDisabled = $flavor['quantity'] == 0 ? 'disabled' : ''; ?>
                                        <div class="addon-item">
                                            <input type="checkbox" name="addons[]" value="flavor-<?php echo $flavor['id']; ?>" id="flavor-<?php echo $flavor['id']; ?>" <?php echo $isDisabled; ?>>
                                            <label for="flavor-<?php echo $flavor['id']; ?>">
                                                <?php echo $flavor['flavor_name']; ?>
                                                (₱<?php echo number_format($flavor['price'], 2); ?>)
                                            </label>
                                            <?php if ($flavor['quantity'] == 0): ?>
                                                <span class="out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p>No flavors available.</p>
                                <?php endif; ?>
                            </div>

                            <h4>Toppings</h4>
                            <div class="sliderights">
                                <?php if ($toppingsResult && $toppingsResult->num_rows > 0): ?>
                                    <?php while ($topping = $toppingsResult->fetch_assoc()): ?>
                                        <?php $isDisabled = $topping['quantity'] == 0 ? 'disabled' : ''; ?>
                                        <div class="addon-item">
                                            <input type="checkbox" name="addons[]" value="topping-<?php echo $topping['id']; ?>" id="topping-<?php echo $topping['id']; ?>" <?php echo $isDisabled; ?>>
                                            <label for="topping-<?php echo $topping['id']; ?>">
                                                <?php echo $topping['topping_name']; ?>
                                                (₱<?php echo number_format($topping['price'], 2); ?>)
                                            </label>
                                            <?php if ($topping['quantity'] == 0): ?>
                                                <span class="out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p>No toppings available.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="button">
                    <button type="submit" class="button2"
                        <?php
                        if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== 'verified') {
                            echo 'onclick="toverify()"';
                        }
                        ?>
                        name="add_to_cart">Add to Cart</button>

                </div>
            </div>
        </form>
    </div>
    <section class="categories">
        <h2>Available Categories</h2>
        <div class="category-list">
            <a href="menu.php" class="<?php echo !$selectedCategory ? 'active' : ''; ?>">
                <h3>All Products</h3>
            </a>
            <a href="menu.php?category=best" class="<?php echo $selectedCategory == 'best' ? 'active' : ''; ?>">
                <h3>Best Sellers</h3>
            </a>
            <?php while ($category = $categoryResult->fetch_assoc()): ?>
                <a href="menu.php?category=<?php echo $category['id']; ?>" class="<?php echo $selectedCategory == $category['id'] ? 'active' : ''; ?>">
                    <img src="<?php echo $category['category_image']; ?>" alt="<?php echo $category['category_name']; ?>" class="category-img">
                    <h3><?php echo $category['category_name']; ?></h3>
                </a>
            <?php endwhile; ?>
        </div>
    </section>
    <section class="products">
        <h2>
            <?php
            if ($selectedCategory && $selectedCategory != 'best') {
                $categoryQuery = "SELECT category_name FROM coffee_category WHERE id = ?";
                $stmt = $conn->prepare($categoryQuery);
                $stmt->bind_param("i", $selectedCategory);
                $stmt->execute();
                $categoryResult = $stmt->get_result();
                $category = $categoryResult->fetch_assoc();
                echo htmlspecialchars($category['category_name']);
            } elseif ($selectedCategory == null) {
                echo 'All Products';
            } else {
                echo 'Best Sellers';
            }
            ?>
        </h2>
        <div class="product-grid">
            <?php
            if ($selectedCategory == 'best'):
                if ($bestSellersResult->num_rows > 0):
                    $counter = 1;
                    while ($product = $bestSellersResult->fetch_assoc()): ?>
                        <div class="product">
                            <?php if ($counter <= $bestStampLimit): ?>
                                <div class="best-stamp">Best</div>
                            <?php endif; ?>
                            <div class="image-container">
                                <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                            </div>
                            <h3><?php echo $product['product_name']; ?></h3>
                            <p class="price">₱ <?php echo number_format($product['price'], 2); ?></p>
                            <p class="price">Solds: <?= $product['total_sales'] ?></p>
                            <button class="add-btn" onclick="openModal(
 <?php echo $product['id']; ?>, 
 '<?php echo addslashes($product['product_name']); ?>',
 '<?php echo addslashes($product['product_description']); ?>', 
 '<?php echo $product['price']; ?>', 
 '<?php echo $product['product_image']; ?>', 
 '<?php echo $product['total_sales']; ?>', 
 '<?php echo $product['base_name']; ?>',
 '<?php echo $product['flavor_name']; ?>',
 '<?php echo $product['topping_name']; ?>'
 )">Order</button>
                        </div>
                    <?php
                        $counter++;
                    endwhile;
                else:
                    echo "<p>No best sellers found.</p>";
                endif;
            else:
                $productsPerPage = 6;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $productsPerPage;
                $totalQuery = "SELECT COUNT(*) as total FROM coffee_products";
                if ($selectedCategory) {
                    $totalQuery .= " WHERE category_id = ?";
                }
                $totalStmt = $conn->prepare($totalQuery);
                if ($totalStmt === false) {
                    die("Error preparing the statement: " . $conn->error);
                }
                if ($selectedCategory) {
                    $totalStmt->bind_param("i", $selectedCategory);
                }
                $totalStmt->execute();
                $totalResult = $totalStmt->get_result();
                $totalRow = $totalResult->fetch_assoc();
                $totalProducts = $totalRow['total'];
                $totalPages = ceil($totalProducts / $productsPerPage);
                $totalStmt->close();
                $productQuery = "
 SELECT 
 coffee_products.*, 
 coffee_base.base_name, 
 coffee_base.quantity AS base_quantity,
 coffee_flavors.flavor_name, 
 coffee_flavors.quantity AS flavor_quantity,
 coffee_toppings.topping_name, 
 coffee_toppings.quantity AS topping_quantity
 FROM coffee_products
 LEFT JOIN coffee_base ON coffee_products.drink_bases = coffee_base.id
 LEFT JOIN coffee_flavors ON coffee_products.flavor_id = coffee_flavors.id
 LEFT JOIN coffee_toppings ON coffee_products.toppings_id = coffee_toppings.id
 ";
                if ($selectedCategory) {
                    $productQuery .= " WHERE category_id = ? LIMIT ?, ?";
                    $stmt = $conn->prepare($productQuery);
                    $stmt->bind_param("iii", $selectedCategory, $offset, $productsPerPage);
                } else {
                    $productQuery .= " LIMIT ?, ?";
                    $stmt = $conn->prepare($productQuery);
                    $stmt->bind_param("ii", $offset, $productsPerPage);
                }
                $stmt->execute();
                $productResult = $stmt->get_result();
                if ($productResult->num_rows > 0):
                    while ($product = $productResult->fetch_assoc()):
                    ?>
                        <div class="product">
                            <?php
                            $isSoldOut = ($product['base_quantity'] == 0 || $product['flavor_quantity'] == 0 || $product['topping_quantity'] == 0);
                            if ($isSoldOut): ?>
                                <div class="sold-out-overlay">Sold Out</div>
                            <?php endif; ?>
                            <div class="image-container">
                                <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                            </div>
                            <h3><?php echo $product['product_name']; ?></h3>
                            <p class="price">₱ <?php echo number_format($product['price'], 2); ?></p>
                            <p class="price">Solds: <?= $product['total_sales'] ?></p>
                            <button class="add-btn" <?php echo $isSoldOut ? 'disabled' : ''; ?> onclick="openModal(
 <?php echo $product['id']; ?>,
 '<?php echo addslashes($product['product_name']); ?>',
 '<?php echo addslashes($product['product_description']); ?>',
 '<?php echo $product['price']; ?>',
 '<?php echo $product['product_image']; ?>',
 '<?php echo $product['total_sales']; ?>',
 '<?php echo addslashes($product['base_name']); ?>',
 '<?php echo addslashes($product['flavor_name']); ?>',
 '<?php echo addslashes($product['topping_name']); ?>'
 )">Order</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products available in this category.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($selectedCategory != 'best'): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>">Prev</a>
                <?php endif; ?>
                <?php
                for ($i = 1; $i <= $totalPages; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
    <script>
        function openModal(
            productId,
            productName,
            productDesc,
            productPrice,
            productImage,
            totalSales,
            stockQuantity,
            drinkBases,
            flavorId,
            toppingsId
        ) {
            var overlay = document.getElementById("overlay");
            var modal = document.getElementById("quantityModal");
            var productIdInput = document.getElementById("modalProductId");
            var productNameInput = document.getElementById("modalProductName");
            var productDescInput = document.getElementById("modalProductDescription");
            var productPriceInput = document.getElementById("modalProductPrice");
            var productImageInput = document.getElementById("modalProductImage");
            var priceDisplay = document.getElementById("modalPrice");
            var soldDisplay = document.getElementById("modalSold");
            productIdInput.value = productId;
            productNameInput.value = productName;
            document.getElementById("modalProductDescription").textContent = productDesc;
            productPriceInput.value = productPrice;
            productImageInput.value = productImage;
            document.getElementById("modalProductImageDisplay").src = productImage;
            document.getElementById("modalProductNameDisplay").textContent = productName;
            priceDisplay.textContent = "₱ " + productPrice;
            soldDisplay.textContent = "Solds: " + totalSales;
            document.getElementById("drinkBasesDisplay").textContent = drinkBases;
            document.getElementById("flavorIdDisplay").textContent = flavorId;
            document.getElementById("toppingsIdDisplay").textContent = toppingsId;
            overlay.style.display = "block";
            modal.style.display = "flex";
        }
        var closeBtn = document.getElementsByClassName("close")[0];
        closeBtn.onclick = function() {
            var modal = document.getElementById("quantityModal");
            modal.style.display = "none";
            overlay.style.display = "none";
        }
        window.onclick = function(event) {
            var modal = document.getElementById("quantityModal");
            if (event.target == modal) {
                modal.style.display = "none";
                overlay.style.display = "none";
            }
        }

        function toverify() {
            window.location.href = 'otp.php';
            alert("Please log in to a verified account first!");

        }
    </script>
    <?php include 'footer.php'; ?>
</body>

</html>