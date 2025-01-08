<?php
// Include connection file
include('connection.php');

// Fetching cart data and total price from the previous form
$cartData = isset($_POST['cartData']) ? json_decode($_POST['cartData'], true) : [];
$totalPrice = isset($_POST['totalPrice']) ? $_POST['totalPrice'] : 0;

if (empty($cartData)) {
    echo "No items in the cart to place an order.";
    exit();
}

// Assuming there's a user session for placing the order
session_start();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // For now, default to user_id = 1

// Prepare the data for insertion
$productIds = [];
$totalQuantity = 0;
$flavors = [];
$toppings = [];
$size = '';
$basePrice = 0.00;
$addonPrice = 0.00;

foreach ($cartData as $cartItem) {
    $productIds[] = $cartItem['productId']; // Collect all product IDs
    $totalQuantity += $cartItem['quantity']; // Sum up the total quantity
    $size = $cartItem['size']; // Assume all items have the same size, otherwise, adjust logic accordingly
    $flavors[] = !empty($cartItem['addons']['flavor']) ? $cartItem['addons']['flavor'] : ''; // Collect flavors
    $toppings[] = !empty($cartItem['addons']['topping']) ? $cartItem['addons']['topping'] : ''; // Collect toppings
    $basePrice += $cartItem['price']; // Sum base price of each product
    $addonPrice += !empty($cartItem['addons']) ? array_sum(array_column($cartItem['addons'], 'price')) : 0.00; // Sum the price of add-ons
}

// Serialize arrays for insertion
$productIdsString = implode(',', $productIds);
$flavorsString = implode(', ', $flavors);
$toppingsString = implode(', ', $toppings);

// Example payment method
$paymentMethod = "Cash"; // You can modify this based on actual payment options

// Insert the order into the database
$orderQuery = "
    INSERT INTO orders (
        user_id, total_amount, order_quantity, product_ids, size, base_price, addon_price, payment_method, flavor, toppings, order_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param(
    "idissddsss",
    $userId,
    $totalPrice,
    $totalQuantity,
    $productIdsString,
    $size,
    $basePrice,
    $addonPrice,
    $paymentMethod,
    $flavorsString,
    $toppingsString
);
$stmt->execute();

// Fetch the order ID
$orderId = $stmt->insert_id;

// Close the statement and connection
$stmt->close();
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
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartData as $cartItem): ?>
                    <tr>
                        <td><?php echo $cartItem['productName']; ?></td>
                        <td><?php echo $cartItem['quantity']; ?></td>
                        <td><?php echo $cartItem['size']; ?></td>
                        <td>
                            <?php
                            $addons = [];
                            if (!empty($cartItem['addons'])) {
                                foreach ($cartItem['addons'] as $addonType => $addon) {
                                    $addons[] = ucfirst($addonType) . ': ' . implode(', ', $addon);
                                }
                            }
                            echo !empty($addons) ? implode('<br>', $addons) : 'None';
                            ?>
                        </td>
                        <td>₱<?php echo number_format($cartItem['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-price">
            <h2>Total Price: ₱<?php echo number_format($totalPrice, 2); ?></h2>
        </div>

        <p>Your order number is: <strong>#<?php echo $orderId; ?></strong></p>
        <p>We will notify you when your order is ready for pickup.</p>

        <a href="menu.php" class="back-btn">Back to Menu</a>
    </div>
</body>

</html>