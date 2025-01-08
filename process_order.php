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
$productIds = [];
$orderQuantity = 0;
$sizePrice = 0;
$addonPrice = 0;
$sizeNames = [];
while ($row = $cartItemsResult->fetch_assoc()) {
    $productId = $row['product_id'];
    $productIds[] = $productId;
    $quantity = $row['quantity'];
    $orderQuantity += $quantity;
    $size = $row['size'];
    $sizeNames[] = $size;
    $addonIds = json_decode($row['addon_ids'], true);
    $addonIds = is_array($addonIds) ? $addonIds : [];
    $productPrice = floatval($row['product_price']);
    $drinkBaseId = $row['drink_bases'];
    $totalPurchaseValue = $_POST['total'];
    $sizePrice += $productPrice * $quantity;

    // Removed stock updates for cup size and product base
    foreach ($addonIds as $addon) {
        $addonType = explode('-', $addon)[0];
        $addonId = intval(explode('-', $addon)[1]);

        if ($addonType === 'flavor') {
            $addonQuery = "SELECT price FROM coffee_flavors WHERE id = ?";
        } elseif ($addonType === 'topping') {
            $addonQuery = "SELECT price FROM coffee_toppings WHERE id = ?";
        } else {
            continue;
        }

        $addonStmt = $conn->prepare($addonQuery);
        $addonStmt->bind_param("i", $addonId);
        $addonStmt->execute();
        $addonResult = $addonStmt->get_result();

        if ($addonRow = $addonResult->fetch_assoc()) {
            $addonPrice += floatval($addonRow['price']) * $quantity;
        }

        $addonStmt->close();
    }

    // Removed drink base updates
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
}

$orderQuery = "
INSERT INTO `orders` (user_id, total_amount, payment_method, flavor, toppings, order_quantity, product_ids, base_price, addon_price, size) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";
$orderStmt = $conn->prepare($orderQuery);
$productIdsStr = implode(',', $productIds);
$baseFlavor = !empty($baseFlavor) ? $baseFlavor : NULL;
$toppingsStr = !empty($toppings) ? implode(', ', $toppings) : NULL;
$sizeNamesStr = implode(',', $sizeNames);
$orderStmt->bind_param("idsssisdss", $userId, $totalPurchaseValue, $paymentMethod, $baseFlavor, $toppingsStr, $orderQuantity, $productIdsStr, $sizePrice, $addonPrice, $sizeNamesStr);
$orderStmt->execute();
$orderStmt->close();

// $clearCartQuery = "DELETE FROM cart WHERE user_id = ? AND id IN ($placeholders)";
// $clearCartStmt = $conn->prepare($clearCartQuery);
// $clearCartStmt->bind_param($types, ...$params);
// $clearCartStmt->execute();
// $clearCartStmt->close();

unset($_SESSION['selectedItems']);
unset($_SESSION['curenttotal']);
echo "<script>alert('Order placed successfully!'); window.location.href = 'order_history.php';</script>";
?>
