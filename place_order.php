<?php

include('connection.php');

$cartData = isset($_POST['cartData']) ? json_decode($_POST['cartData'], true) : [];
$totalPrice = isset($_POST['totalPrice']) ? $_POST['totalPrice'] : 0;

if (empty($cartData)) {
    echo "No items in the cart to place an order.";
    exit();
}

session_start();
$userId = 0; // Modify this based on your session management

$productIds = [];
$totalQuantity = 0;
$flavors = [];
$toppings = [];
$sizes = []; // Store multiple sizes
$basePrice = 0.00;
$addonPrice = 0.00;

foreach ($cartData as $cartItem) {
    $productIds[] = $cartItem['productId'];
    $totalQuantity += $cartItem['quantity'];

    $sizes[] = isset($cartItem['size']['name']) ? trim($cartItem['size']['name']) : '';

    $productId = $cartItem['productId'];
    $quantity = $cartItem['quantity'];

    $baseQuery = "SELECT drink_bases, flavor_id, toppings_id FROM coffee_products WHERE id = $productId";
    $baseResult = $conn->query($baseQuery);

    while ($row = $baseResult->fetch_assoc()) {
        $productId = $row['drink_bases'];
        $flavor_id = $row['flavor_id'];
        $toppings_id = $row['toppings_id'];
        $updateBaseQuery = "UPDATE coffee_base SET quantity = quantity - $quantity WHERE id = $productId";
        // $conn->query($updateBaseQuery);
        $updateBaseQuery = "UPDATE coffee_flavors SET quantity = quantity - $quantity WHERE id = $flavor_id";
        // $conn->query($updateBaseQuery);
        $updateBaseQuery = "UPDATE coffee_toppings SET quantity = quantity - $quantity WHERE id = $toppings_id";
        // $conn->query($updateBaseQuery);
    }

    foreach ($cartItem['addons'] as $addon) {
        if (strpos($addon, 'flavor') !== false) {
            $flavorId = str_replace('flavor-', '', $addon);
            $flavorQuery = "SELECT flavor_name, quantity FROM coffee_flavors WHERE id = $flavorId";
            $flavorResult = $conn->query($flavorQuery);
            $flavorRow = $flavorResult->fetch_assoc();

            $flavorName = $flavorRow['flavor_name'];
            $flavorQuantity = $flavorRow['quantity'];

            $newFlavorQuantity = $flavorQuantity - $quantity;
            $updateFlavorQuery = "UPDATE coffee_flavors SET quantity = $newFlavorQuantity WHERE id = $flavorId";
            // $conn->query($updateFlavorQuery);

            $flavors[] = $flavorName;
        } elseif (strpos($addon, 'topping') !== false) {
            $toppingId = str_replace('topping-', '', $addon);
            $toppingQuery = "SELECT topping_name, quantity FROM coffee_toppings WHERE id = $toppingId";
            $toppingResult = $conn->query($toppingQuery);
            $toppingRow = $toppingResult->fetch_assoc();

            $toppingName = $toppingRow['topping_name'];
            $toppingQuantity = $toppingRow['quantity'];

            $newToppingQuantity = $toppingQuantity - $quantity;
            $updateToppingQuery = "UPDATE coffee_toppings SET quantity = $newToppingQuantity WHERE id = $toppingId";
            // $conn->query($updateToppingQuery);

            $toppings[] = $toppingName;
        }
    }

    $basePrice += $cartItem['price'];
}

$productIdsString = implode(',', $productIds);
$flavorsString = implode(', ', $flavors);
$toppingsString = implode(', ', $toppings);
$sizesString = implode(', ', $sizes);

$paymentMethod = "Cash";
$orderQuery = "
    INSERT INTO orders (
        user_id, total_amount, order_quantity, product_ids, size, base_price, payment_method, flavor, toppings, order_date, addon_price
    ) VALUES (
        $userId, $totalPrice, $totalQuantity, '$productIdsString', '$sizesString', $basePrice, '$paymentMethod', '$flavorsString', '$toppingsString', NOW(), {$_POST['addonsprice']}
    )
";
$conn->query($orderQuery);

$orderId = $conn->insert_id;

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>
    <div class="order-confirmation">
        <h1>Thank you for your order!</h1>
        <p>Your order has been placed successfully. Here is a summary of your order:</p>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Size</th>
                    <th>Add-ons</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartData as $cartItem): ?>
                    <tr>
                        <td><?php echo $cartItem['productName']; ?></td>
                        <td><?php echo $cartItem['quantity']; ?></td>
                        <td><?php echo isset($cartItem['size']['name']) ? trim($cartItem['size']['name']) : ''; ?></td>
                        <td>
                            <?php
                            $addons = [];
                            foreach ($cartItem['addons'] as $addon) {
                                if (strpos($addon, 'flavor') !== false) {
                                    $addons[] = "Flavor: " . $flavorName;
                                } elseif (strpos($addon, 'topping') !== false) {
                                    $addons[] = "Topping: " . $toppingName;
                                }
                            }
                            echo !empty($addons) ? implode('<br>', $addons) : 'None';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-price">
            <h2>Total Price: ₱<?php echo number_format($totalPrice, 2); ?></h2>
        </div>

        <p>Your order number is: <strong>#<?php echo $orderId; ?></strong></p>
        <p>We will notify you when your order is ready for pickup.</p>

        <a href="admin.php?POS" class="back-btn">Back to Menu</a>
    </div>
</body>

</html>