<?php
session_start();
include 'connection.php';

// Check if items are selected
if (!isset($_SESSION['selectedItems']) || empty($_SESSION['selectedItems'])) {
    echo "<script>alert('No coffee_products selected for purchase.'); window.location.href = 'home.php';</script>";
    exit();
}

// Get the selected items from session
$selectedItems = implode(',', $_SESSION['selectedItems']);

// Get user details
$userQuery = "SELECT id FROM user_account WHERE username = ?";
$userStmt = $conn->prepare($userQuery);
if (!$userStmt) {
    echo "<script>alert('Error preparing user query: {$conn->error}'); window.location.href = 'home.php';</script>";
    exit();
}
$userStmt->bind_param("s", $_SESSION['username']);
$userStmt->execute();
$userResult = $userStmt->get_result();
if ($userResult->num_rows === 0) {
    echo "<script>alert('User not found.'); window.location.href = 'home.php';</script>";
    exit();
}
$user = $userResult->fetch_assoc();
$UserID = $user['id'];
$userStmt->close();

// Get selected items from the cart
$getSelectedItemsQuery = "
SELECT 
    coffee_products.id, 
    coffee_products.product_name, 
    coffee_products.Price, 
    cart.Quantity, 
    cart.size, 
    cart.addons AS addon_ids, 
    cart.id AS cart_id
FROM cart 
INNER JOIN coffee_products ON cart.product_id = coffee_products.id 
WHERE cart.user_id = ? AND cart.id IN ($selectedItems)
";
$stmtGetSelectedItems = $conn->prepare($getSelectedItemsQuery);
if (!$stmtGetSelectedItems) {
    echo "<script>alert('Error preparing selected coffee_products query: {$conn->error}'); window.location.href = 'home.php';</script>";
    exit();
}
$stmtGetSelectedItems->bind_param("i", $UserID);
$stmtGetSelectedItems->execute();
$result = $stmtGetSelectedItems->get_result();

$totalPurchaseValue = 0;
$productIDs = [];
$quantities = [];
$cartIds = [];

while ($row = $result->fetch_assoc()) {
    $productPrice = $row['Price'];
    $addonIds = json_decode($row['addon_ids'], true);
    $addonNames = [];
    $addonPrice = 0;  // Reset addon price for each item

    // Calculate add-on prices
    if (!empty($addonIds)) {
        $placeholders = implode(',', array_fill(0, count($addonIds), '?'));
        $addonQuery = "SELECT addon_name, addon_price FROM addons WHERE id IN ($placeholders)";
        $addonStmt = $conn->prepare($addonQuery);
        $addonStmt->bind_param(str_repeat('i', count($addonIds)), ...$addonIds);
        $addonStmt->execute();
        $addonResult = $addonStmt->get_result();
        while ($addon = $addonResult->fetch_assoc()) {
            $addonPrice += $addon['addon_price'];  // Accumulate add-on price for the current item
            $addonNames[] = $addon['addon_name'];
        }
        $addonStmt->close();
    }

    // Calculate size price
    $sizePrice = 0;
    if ($row['size'] == 'M') {
        $sizePrice = 10;
    } elseif ($row['size'] == 'L') {
        $sizePrice = 20;
    }

    // Calculate the total price for the current item
    $totalItemPrice = ($productPrice + $addonPrice + $sizePrice) * $row['Quantity'];
    $totalPurchaseValue += $totalItemPrice;  // Add item total to the overall purchase value

    // Store product IDs, quantities, and cart IDs
    $productIDs[] = $row['id'];
    $quantities[] = $row['Quantity'];
    $cartIds[] = $row['cart_id'];
}

$stmtGetSelectedItems->close();

// Process payment when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $paymentMode = $_POST['paymentMode'];
    $_SESSION['curenttotal'] = $totalPurchaseValue;
    $conn->begin_transaction();

    try {
        // Save order
        $saveOrderQuery = "INSERT INTO orders (user_id, order_date, total_amount, order_quantity, product_ids) VALUES (?, NOW(), ?, ?, ?)";
        $stmtSaveOrder = $conn->prepare($saveOrderQuery);
        if (!$stmtSaveOrder) {
            throw new Exception("Error preparing order query: " . $conn->error);
        }
        $orderQuantity = array_sum($quantities);
        $productIDsString = implode(',', $productIDs);
        $stmtSaveOrder->bind_param("idss", $UserID, $_SESSION['curenttotal'], $orderQuantity, $productIDsString);
        $stmtSaveOrder->execute();
        $orderId = $stmtSaveOrder->insert_id;
        $stmtSaveOrder->close();

        // Save payment
        $savePaymentQuery = "INSERT INTO payment (order_id, user_id, payment_mode, amount_paid) VALUES (?, ?, ?, ?)";
        $stmtSavePayment = $conn->prepare($savePaymentQuery);
        if (!$stmtSavePayment) {
            throw new Exception("Error preparing payment query: " . $conn->error);
        }
        $stmtSavePayment->bind_param("iisd", $orderId, $UserID, $paymentMode, $_SESSION['curenttotal']);
        $stmtSavePayment->execute();
        $stmtSavePayment->close();

        // Update inventory
        foreach ($productIDs as $index => $productId) {
            $quantity = $quantities[$index];
            $updateItemsQuery = "UPDATE coffee_products SET Quantity = Quantity - ?, total_sales = total_sales + ? WHERE id = ?";
            $stmtUpdateItems = $conn->prepare($updateItemsQuery);
            if (!$stmtUpdateItems) {
                throw new Exception("Error updating item quantities: " . $conn->error);
            }
            $stmtUpdateItems->bind_param("iii", $quantity, $quantity, $productId);
            $stmtUpdateItems->execute();
            $stmtUpdateItems->close();
        }

        // Delete cart items
        $cartIdsString = implode(',', $cartIds);
        $deleteCartItemsQuery = "DELETE FROM cart WHERE id IN ($cartIdsString)";
        if (!$conn->query($deleteCartItemsQuery)) {
            throw new Exception("Error removing items from cart: " . $conn->error);
        }

        // Commit transaction
        $conn->commit();
        echo "<script>alert('Order placed successfully!'); window.location.href = 'home.php';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error processing your order: {$e->getMessage()}'); window.location.href = 'home.php';</script>";
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Payment</title>
 <link rel="stylesheet" href="css/global.css">
 <link rel="stylesheet" href="css/payment.css">
</head>
<body>
 <?php include("header.php"); ?>
 <div class="popupd">
 <div class="done">
 <form method="post" action="" class="payment-form">
 <div class="form-group">
 <label for="paymentMode">Payment Mode:<?= $_SESSION['curenttotal'] ?></label>
 <div class="method_img">
 <img src="uploads/method/link-91720ed84858d490ca62142de0494559.png">
 <img src="uploads/method/link-cf7aaa8b59e07c8548d2f03f0d930acb.png">
 <img src="uploads/method/link-4a1f1c2d9ee1820ccc9621b44f277387.png">
 <img src="uploads/method/link-8efc3b564e08e9e864ea83ab43d9f913.png">
 </div>
 <select name="paymentMode" id="paymentMode" required>
 <option value="GCash">GCash</option>
 <option value="Debit Card">Debit Card</option>
 <option value="Pay on the Counter">Pay on the Counter</option>
 </select>
 </div>
 <div class="payment-button">
 <button class="buybtn" type="submit">Submit Payment</button>
 </div>
 </form>
 </div>
 </div>
 <?php include("footer.php"); ?>
</body>
</html>