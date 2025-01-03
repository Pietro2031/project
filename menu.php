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
$bestStampLimit = 3; // kung ilan malalagyan ng best

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
            z-index: 10;
        }

        .product {
            position: relative;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
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
                            <button class="add-btn">Order</button>
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
                            <button class="add-btn">Order</button>
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
        function menuToggle() {
            const menu = document.querySelector(".menu");
            const profile = document.querySelector(".profile");
            menu.classList.toggle("active");
            const expanded = profile.getAttribute("aria-expanded") === "true";
            profile.setAttribute("aria-expanded", !expanded);
            menu.setAttribute("aria-hidden", expanded);
        }
        document.addEventListener("click", (event) => {
            const menu = document.querySelector(".menu");
            const profile = document.querySelector(".profile");
            if (!menu.contains(event.target) && !profile.contains(event.target)) {
                menu.classList.remove("active");
                profile.setAttribute("aria-expanded", "false");
                menu.setAttribute("aria-hidden", "true");
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</body>

</html>