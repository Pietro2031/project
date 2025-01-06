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
$userQuery = "SELECT * FROM user_account WHERE username = ?";
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
 coffee_products.product_name AS product_name, 
 coffee_products.product_image AS product_image, 
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
            <div class="user-info">
                <h1>Account Information</h1>
                <p><b>Name:</b> <?= $user['Fname'] . ' ' . $user['Lname'] ?></p>
                <p><b>Contact Number:</b> <?= $user['ContactNum'] ?></p>
                <select name="address" id="address">
                    <option value="<?= $user['Addresss'] ?>"><?= $user['Addresss'] ?></option>
                    <option value="<?= $user['addresss2'] ?>"><?= $user['addresss2'] ?></option>
                </select>
            </div>
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $row):
                    $productPrice = $row['Price'];
                    $addonPrice = 0;
                    $addonNames = [];
                    $addonIds = json_decode($row['addon_ids'], true);
                    if (!empty($addonIds)) {
                        $flavorIds = [];
                        $toppingIds = [];

                        foreach ($addonIds as $addon) {
                            if (str_starts_with($addon, 'flavor-')) {
                                $flavorIds[] = intval(str_replace('flavor-', '', $addon));
                            } elseif (str_starts_with($addon, 'topping-')) {
                                $toppingIds[] = intval(str_replace('topping-', '', $addon));
                            }
                        }

                        if (!empty($flavorIds)) {
                            $flavorPlaceholders = implode(',', array_fill(0, count($flavorIds), '?'));
                            $flavorQuery = "SELECT flavor_name, price  FROM coffee_flavors WHERE id IN ($flavorPlaceholders)";
                            $flavorStmt = $conn->prepare($flavorQuery);
                            $flavorStmt->bind_param(str_repeat('i', count($flavorIds)), ...$flavorIds);
                            $flavorStmt->execute();
                            $flavorResult = $flavorStmt->get_result();
                            while ($flavor = $flavorResult->fetch_assoc()) {
                                $addonNames[] = $flavor['flavor_name'];
                                $addonPrice += $flavor['price'];
                            }
                            $flavorStmt->close();
                        }

                        if (!empty($toppingIds)) {
                            $toppingPlaceholders = implode(',', array_fill(0, count($toppingIds), '?'));
                            $toppingQuery = "SELECT topping_name, price  FROM coffee_toppings WHERE id IN ($toppingPlaceholders)";
                            $toppingStmt = $conn->prepare($toppingQuery);
                            $toppingStmt->bind_param(str_repeat('i', count($toppingIds)), ...$toppingIds);
                            $toppingStmt->execute();
                            $toppingResult = $toppingStmt->get_result();
                            while ($topping = $toppingResult->fetch_assoc()) {
                                $addonNames[] = $topping['topping_name'];
                                $addonPrice += $topping['price'];
                            }
                            $toppingStmt->close();
                        }
                    }

                    if ($row['size'] == 'M') {
                        // Query the cup_size table for size 'M'
                        $cupSizeQuery = "SELECT price FROM cup_size WHERE size = 'M'";
                        $cupSizeStmt = $conn->prepare($cupSizeQuery);
                        $cupSizeStmt->execute();
                        $cupSizeResult = $cupSizeStmt->get_result();
                        if ($cupSizeRow = $cupSizeResult->fetch_assoc()) {
                            $productPrice += floatval($cupSizeRow['price']); // Add price for size 'M'
                        }
                        $cupSizeStmt->close();
                    } elseif ($row['size'] == 'L') {
                        // Query the cup_size table for size 'L'
                        $cupSizeQuery = "SELECT price FROM cup_size WHERE size = 'L'";
                        $cupSizeStmt = $conn->prepare($cupSizeQuery);
                        $cupSizeStmt->execute();
                        $cupSizeResult = $cupSizeStmt->get_result();
                        if ($cupSizeRow = $cupSizeResult->fetch_assoc()) {
                            $productPrice += floatval($cupSizeRow['price']); // Add price for size 'L'
                        }
                        $cupSizeStmt->close();
                    }

                    $totalPrice = $row['quantity'] * ($productPrice + $addonPrice);
                    $totalPurchaseValue += $totalPrice;
                ?>
                    <div class="itemrow">
                        <div class="itemproduct">
                            <div class="itemimg">
                                <img src="<?= $row['product_image']; ?>" alt="<?= htmlspecialchars($row['product_name']); ?>">
                                <div class="itemqnty"><?= $row['quantity']; ?></div>
                            </div>
                            <div class="slidedown">
                                <div class="itemname"><?= htmlspecialchars($row['product_name']); ?></div>
                                <div class="itemdetails">
                                    <p><strong>Base Price:</strong> ₱<?= number_format($row['Price'], 2); ?></p>

                                    <?php
                                    if ($row['size'] == 'M' || $row['size'] == 'L'):
                                        $cupSizeQuery = "SELECT price FROM cup_size WHERE size = ?";
                                        $cupSizeStmt = $conn->prepare($cupSizeQuery);
                                        $cupSizeStmt->bind_param("s", $row['size']);
                                        $cupSizeStmt->execute();
                                        $cupSizeResult = $cupSizeStmt->get_result();

                                        if ($cupSizeRow = $cupSizeResult->fetch_assoc()):
                                            $sizeAdjustmentPrice = floatval($cupSizeRow['price']);
                                        endif;
                                        $cupSizeStmt->close();
                                    ?>
                                        <p><strong>Size Adjustment (<?= $row['size']; ?>):</strong> ₱<?= number_format($sizeAdjustmentPrice, 2); ?></p>
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
                </div>

                <!-- Payment Method Section -->
                <div class="payment-method">
                    <h2>Payment Method</h2>
                    <form action="process_order.php" method="POST">
                        <input type="hidden" name="total" value="<?= $totalPurchaseValue ?>">
                        <label for="payment">Select Payment Method:</label>
                        <select name="payment_method" id="payment">
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="gcash">GCash</option>
                        </select>
                        <button type="submit" class="buybtn">Submit Order</button>
                    </form>
                </div>
            <?php else: ?>
                <p>No items in the cart.</p>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>