<?php
session_start();
include('connection.php');
if (!isset($_SESSION['username'])) {
    die("Error: You must be logged in to place an order.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Invalid request method.");
}
$username = $_SESSION['username'];
$totalPurchaseValue = isset($_SESSION['curenttotal']) ? floatval($_SESSION['curenttotal']) : 0;
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
if (!$paymentMethod) {
    die("Error: Payment method is required.");
}
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
    die("Error: No items selected for purchase.");
}
$selectedItems = $_SESSION['selectedItems'];
$placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
$getCartItemsQuery = "
SELECT 
cart.id AS cart_id, 
cart.product_id, 
cart.quantity, 
cart.size, 
cart.addons AS addon_ids,
coffee_products.price AS product_price,
coffee_products.drink_bases
FROM cart
INNER JOIN coffee_products ON cart.product_id = coffee_products.id
WHERE cart.user_id = ? AND cart.id IN ($placeholders)
";
$stmt = $conn->prepare($getCartItemsQuery);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}
$params = array_merge([$userId], $selectedItems);
$types = str_repeat('i', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$cartItemsResult = $stmt->get_result();
if (!$cartItemsResult) {
    die("Error retrieving selected items.");
}
$baseFlavor = '';
$toppings = [];
$totalPurchaseValue = 0;
while ($row = $cartItemsResult->fetch_assoc()) {
    $productId = $row['product_id'];
    $quantity = $row['quantity'];
    $size = $row['size'];
    $addonIds = json_decode($row['addon_ids'], true);
    $addonIds = is_array($addonIds) ? $addonIds : [];
    $productPrice = floatval($row['product_price']);
    $drinkBaseId = $row['drink_bases'];
    $totalPurchaseValue += $productPrice * $quantity;

    // Deduct the quantity of selected cup size from cup_size table
    if ($size === 'M' || $size === 'L') {
        $cupSizeQuery = "SELECT quantity FROM cup_size WHERE size = ?";
        $cupSizeStmt = $conn->prepare($cupSizeQuery);
        $cupSizeStmt->bind_param("s", $size);
        $cupSizeStmt->execute();
        $cupSizeResult = $cupSizeStmt->get_result();
        if ($cupSizeRow = $cupSizeResult->fetch_assoc()) {
            $currentQuantity = $cupSizeRow['quantity'];
            if ($currentQuantity >= $quantity) {
                // Deduct the quantity
                $newQuantity = $currentQuantity - $quantity;
                $updateCupSizeQuery = "UPDATE cup_size SET quantity = ? WHERE size = ?";
                $updateCupSizeStmt = $conn->prepare($updateCupSizeQuery);
                $updateCupSizeStmt->bind_param("is", $newQuantity, $size);
                $updateCupSizeStmt->execute();
                $updateCupSizeStmt->close();
            } else {
                die("Error: Insufficient stock for the selected cup size.");
            }
        }
        $cupSizeStmt->close();
    }

    if ($drinkBaseId) {
        $baseQuery = "SELECT price FROM coffee_base WHERE id = ?";
        $baseStmt = $conn->prepare($baseQuery);
        $baseStmt->bind_param("i", $drinkBaseId);
        $baseStmt->execute();
        $baseResult = $baseStmt->get_result();
        if ($baseRow = $baseResult->fetch_assoc()) {
            $totalPurchaseValue += floatval($baseRow['price']) * $quantity;
            $updateBaseQuery = "UPDATE coffee_base SET quantity = quantity - ? WHERE id = ?";
            $updateBaseStmt = $conn->prepare($updateBaseQuery);
            $updateBaseStmt->bind_param("ii", $quantity, $drinkBaseId);
            $updateBaseStmt->execute();
            $updateBaseStmt->close();
        }
        $baseStmt->close();
    }

    $flavorNames = [];
    $toppingNames = [];
    foreach ($addonIds as $addon) {
        $addonType = explode('-', $addon)[0];
        $addonId = intval(explode('-', $addon)[1]);
        if ($addonType === 'flavor') {
            $flavorQuery = "SELECT flavor_name FROM coffee_flavors WHERE id = ?";
            $flavorStmt = $conn->prepare($flavorQuery);
            $flavorStmt->bind_param("i", $addonId);
            $flavorStmt->execute();
            $flavorResult = $flavorStmt->get_result();
            if ($flavor = $flavorResult->fetch_assoc()) {
                $flavorNames[] = $flavor['flavor_name'];
            }
            $flavorStmt->close();
        } elseif ($addonType === 'topping') {
            $toppingQuery = "SELECT topping_name FROM coffee_toppings WHERE id = ?";
            $toppingStmt = $conn->prepare($toppingQuery);
            $toppingStmt->bind_param("i", $addonId);
            $toppingStmt->execute();
            $toppingResult = $toppingStmt->get_result();
            if ($topping = $toppingResult->fetch_assoc()) {
                $toppingNames[] = $topping['topping_name'];
            }
            $toppingStmt->close();
        }
    }
    if (!empty($flavorNames)) {
        $baseFlavor = $flavorNames[0];
    }
    if (!empty($toppingNames)) {
        $toppings[] = implode(', ', $toppingNames);
    }
    foreach ($addonIds as $addonId) {
        $addonType = explode('-', $addonId)[0];
        $addonId = intval(explode('-', $addonId)[1]);
        if ($addonType === 'flavor') {
            $updateFlavorQuery = "UPDATE coffee_flavors SET quantity = quantity - ? WHERE id = ?";
            $updateFlavorStmt = $conn->prepare($updateFlavorQuery);
            $updateFlavorStmt->bind_param("ii", $quantity, $addonId);
            $updateFlavorStmt->execute();
            $updateFlavorStmt->close();
        } elseif ($addonType === 'topping') {
            $updateToppingQuery = "UPDATE coffee_toppings SET quantity = quantity - ? WHERE id = ?";
            $updateToppingStmt = $conn->prepare($updateToppingQuery);
            $updateToppingStmt->bind_param("ii", $quantity, $addonId);
            $updateToppingStmt->execute();
            $updateToppingStmt->close();
        }
    }
}

$orderQuery = "
INSERT INTO `orders` (user_id, total_amount, payment_method, flavor, toppings, created_at) 
VALUES (?, ?, ?, ?, ?, NOW())
";
$orderStmt = $conn->prepare($orderQuery);
$baseFlavor = !empty($baseFlavor) ? $baseFlavor : NULL;
$toppingsStr = !empty($toppings) ? implode(', ', $toppings) : NULL;
$orderStmt->bind_param("idsss", $userId, $totalPurchaseValue, $paymentMethod, $baseFlavor, $toppingsStr);
$orderStmt->execute();
$orderStmt->close();

$clearCartQuery = "DELETE FROM cart WHERE user_id = ? AND id IN ($placeholders)";
$clearCartStmt = $conn->prepare($clearCartQuery);
$clearCartStmt->bind_param($types, ...$params);
$clearCartStmt->execute();
$clearCartStmt->close();

unset($_SESSION['selectedItems']);
unset($_SESSION['curenttotal']);
echo "<script>alert('Order placed successfully!'); window.location.href = 'order_history.php';</script>";
