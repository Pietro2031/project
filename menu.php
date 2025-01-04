<?php
session_start();
include('connection.php');
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$categoryQuery = "SELECT * FROM coffee_category";
$categoryResult = $conn->query($categoryQuery);
$bestSellersQuery = "
    SELECT * FROM coffee_products
    ORDER BY total_sales DESC
    LIMIT 6
";
$bestSellersResult = $conn->query($bestSellersQuery);
$bestStampLimit = 3;
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    if (!isset($_SESSION['username'])) {
        die("Error: You must be logged in to add items to the cart.");
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

    $checkCartQuery = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($checkCartQuery);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {

        $updateCartQuery = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
        $updateStmt = $conn->prepare($updateCartQuery);
        $updateStmt->bind_param("iii", $quantity, $userId, $productId);
        $updateStmt->execute();
        $updateStmt->close();
    } else {

        $insertCartQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertCartQuery);
        $insertStmt->bind_param("iii", $userId, $productId, $quantity);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $stmt->close();
}
$productQuery = "SELECT * FROM coffee_products";
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Grid</title>
    <link rel="stylesheet" href="menu.css">
    <style>
        .best-stamp {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #ff5757;
            color: white;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            z-index: 2;
        }

        .product {
            position: relative;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 3;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 20%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <div id="quantityModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Input Quantity</h2>
            <form id="quantityForm" action="menu.php" method="post">
                <input type="hidden" name="product_id" id="modalProductId">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="modalQuantity" value="1" min="1" required>
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
        </div>
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
                    while ($product = $bestSellersResult->fetch_assoc()):
            ?>
                        <div class="product">
                            <?php if ($counter <= $bestStampLimit): ?>
                                <div class="best-stamp">Best</div>
                            <?php endif; ?>
                            <div class="image-container">
                                <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                            </div>
                            <h3><?php echo $product['product_name']; ?></h3>
                            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                            <p class="price">Solds: <?= $product['total_sales'] ?></p>
                            <button class="add-btn" onclick="openModal(<?php echo $product['id']; ?>)">Order</button>
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

                $productQuery = "SELECT * FROM coffee_products";
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
                        <div class="image-container">
                            <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                        </div>
                        <h3><?php echo $product['product_name']; ?></h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <p class="price">Solds: <?= $product['total_sales'] ?></p>
                        <button class="add-btn" onclick="openModal(<?php echo $product['id']; ?>)">Order</button>
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
        function openModal(productId) {
            var modal = document.getElementById("quantityModal");
            var productIdInput = document.getElementById("modalProductId");
            productIdInput.value = productId;
            modal.style.display = "block";
        }
        var closeBtn = document.getElementsByClassName("close")[0];
        closeBtn.onclick = function() {
            var modal = document.getElementById("quantityModal");
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            var modal = document.getElementById("quantityModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    <?php include 'footer.php'; ?>
</body>

</html>