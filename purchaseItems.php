<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('connection.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/purchaseItems.css">
</head>

<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Error: You must be logged in to view your cart.");
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

if (!isset($_SESSION['selectedItems']) || !is_array($_SESSION['selectedItems']) || empty($_SESSION['selectedItems'])) {
    echo "<script>alert('No items selected for purchase.'); window.location.href = 'cart.php';</script>";
    exit();
}
$selectedItems = $_SESSION['selectedItems'];

$placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

$getSelectedItemsQuery = "
    SELECT 
        cart.id AS cart_id, 
        cart.product_id, 
        cart.quantity, 
        cart.size, 
        cart.addons AS addon_ids, 
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
                    $productPrice = $row['Price'];
                    $addonPrice = 0;
                    $addonNames = [];


                    $addonIds = json_decode($row['addon_ids'], true);
                    if (!empty($addonIds)) {
                        $placeholders = implode(',', array_fill(0, count($addonIds), '?'));
                        $addonQuery = "SELECT addon_name, addon_price FROM addons WHERE id IN ($placeholders)";
                        $addonStmt = $conn->prepare($addonQuery);
                        $addonStmt->bind_param(str_repeat('i', count($addonIds)), ...$addonIds);
                        $addonStmt->execute();
                        $addonResult = $addonStmt->get_result();
                        while ($addon = $addonResult->fetch_assoc()) {
                            $addonPrice += $addon['addon_price'];
                            $addonNames[] = $addon['addon_name'];
                        }
                        $addonStmt->close();
                    }


                    if ($row['size'] == 'M') {
                        $productPrice += 10;
                    } elseif ($row['size'] == 'L') {
                        $productPrice += 20;
                    }


                    $totalPrice = $row['quantity'] * ($productPrice + $addonPrice);
                    $totalPurchaseValue += $totalPrice;
                ?>
                    <div class="itemrow">
                        <div class="itemproduct">
                            <div class="itemimg">
                                <img src="<?= $row['BookImage']; ?>" alt="<?= htmlspecialchars($row['Title']); ?>">
                                <div class="itemqnty"><?= $row['quantity']; ?></div>
                            </div>
                            <div class="slidedown">
                                <div class="itemname"><?= htmlspecialchars($row['Title']); ?></div>
                                <div class="itemdetails">
                                    <p><strong>Base Price:</strong> ₱<?= number_format($row['Price'], 2); ?></p>
                                    <?php if ($row['size'] == 'M' || $row['size'] == 'L'): ?>
                                        <p><strong>Size Adjustment (<?= $row['size']; ?>):</strong> ₱<?= $row['size'] == 'M' ? '10.00' : '20.00'; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($addonNames)): ?>
                                        <p><strong>Add-ons:</strong> <?= implode(', ', $addonNames); ?></p>
                                        <p><strong>Add-on Total:</strong> ₱<?= number_format($addonPrice, 2); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
                <div class="total">
                    <p><b>Total</b></p>
                    <p><b>₱<?= number_format($totalPurchaseValue, 2); ?></b></p>
                    <?php $_SESSION['curenttotal'] = number_format($totalPurchaseValue, 2)  ?>
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