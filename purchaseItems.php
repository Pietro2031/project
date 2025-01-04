<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('connection.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/purchaseItems.css">
</head>

<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    die("Error: You must be logged in to view your cart.");
}

$username = $_SESSION['username'];

// Fetch user ID from the session
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

if (!isset($_SESSION['selectedItems']) || !is_array($_SESSION['selectedItems']) || empty($_SESSION['selectedItems'])) {
    echo "<script>alert('No items selected for purchase.'); window.location.href = 'cart.php';</script>";
    exit();
}

$selectedItems = $_SESSION['selectedItems'];

// Create placeholders for the SQL IN clause
$placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

// Prepare the SQL query to fetch selected items
$getSelectedItemsQuery = "
    SELECT 
        cart.id AS cart_id, 
        cart.product_id, 
        cart.quantity, 
        coffee_products.product_name AS Title, 
        coffee_products.product_image AS BookImage, 
        coffee_products.price AS Price
    FROM cart
    INNER JOIN coffee_products ON cart.product_id = coffee_products.id
    WHERE cart.user_id = ? AND cart.id IN ($placeholders)
";

$stmt = $conn->prepare($getSelectedItemsQuery);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

// Bind the user ID and selected items to the query
$params = array_merge([$userId], $selectedItems);
$types = str_repeat('i', count($params));
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo "<script>alert('Error retrieving selected items.'); window.location.href = 'cart.php';</script>";
    exit();
}

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

$totalPurchaseValue = 0;
?>

<body>
    <?php include("header.php"); ?>
    <section class="cashoutsec">
        <div class="checkout">
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $row):
                    $totalPrice = $row['quantity'] * $row['Price'];
                    $totalPurchaseValue += $totalPrice; ?>
                    <div class="itemrow">
                        <div class="itemproduct">
                            <div class="itemimg">
                                <img src="<?= $row['BookImage']; ?>">
                                <div class="itemqnty"><?= $row['quantity']; ?></div>
                            </div>
                            <div class="itemname"><?= htmlspecialchars($row['Title']); ?></div>
                        </div>
                        <div class="itemtotal">
                            <p class="itemprice">$<?= number_format($totalPrice, 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="total">
                    <p><b>Total</b></p>
                    <p><b>$<?= number_format($totalPurchaseValue, 2); ?></b></p>
                </div>
                <p class="itemname">Click to proceed to payment and finalize your purchase.</p>
                <button class="buybtn" onclick="checkout()">Checkout</button>
            <?php else: ?>
                <p>No items in the cart.</p>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function checkout() {
            window.location.href = "payment.php";
        }
    </script>

</body>

</html>