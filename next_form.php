<?php
include('connection.php');

if (isset($_POST['cartData'])) {
    // Decode the cart data from JSON
    $cartData = json_decode($_POST['cartData'], true);

    // Common order details
    $userId = 0; // Replace with actual user ID if available
    $orderDate = date('Y-m-d H:i:s');
    $status = 1; // Example status, modify as per your application logic
    $paymentMethod = 'pay on conter'; // Replace with dynamic method if needed
    $totalAmount = 0;
    $orderQuantity = 0;

    // Prepare for capturing data
    $productIds = [];
    $orderItems = []; // To store detailed product data for insertion

    foreach ($cartData as $item) {
        // Extract individual item details
        $productId = $item['productId'];
        $productName = $item['productName'] ?? '';
        $quantity = $item['quantity'];
        $price = $item['price'];

        $size = $item['size'] ?? ''; // Capture size if provided
        $sizePrice = $item['sizePrice'] ?? 0;

        $addons = $item['addons'] ?? [];
        $addonPrice = 0;
        $addonDetails = '';

        // Check if addons is an array
        if (is_array($addons)) {
            $addonPrice = array_sum(array_column($addons, 'price')); // Sum all addon prices
            $addonDetails = implode(', ', array_column($addons, 'name')); // Combine addon names
        }

        $flavors = $item['flavors'] ?? [];
        $flavorDetails = is_array($flavors) ? implode(', ', $flavors) : '';

        $toppings = $item['toppings'] ?? [];
        $toppingDetails = is_array($toppings) ? implode(', ', $toppings) : '';

        // Update order totals
        $orderQuantity += $quantity;
        $totalAmount += $price;

        // Add product ID for reference
        $productIds[] = $productId;

        // Prepare order item details for insertion
        $orderItems[] = [
            'product_id' => $productId,
            'product_name' => $productName,
            'quantity' => $quantity,
            'price' => $price,
            'size' => $size,
            'size_price' => $sizePrice,
            'addon_details' => $addonDetails,
            'addon_price' => $addonPrice,
            'flavor_details' => $flavorDetails,
            'topping_details' => $toppingDetails,
        ];
    }

    // Convert product IDs to string
    $productIdsStr = implode(',', $productIds);

    // Insert main order into `orders` table
    $insertOrderQuery = "
    INSERT INTO `orders` 
    (`user_id`, `order_date`, `total_amount`, `order_quantity`, `product_ids`, `status`, `payment_method`, `created_at`) 
    VALUES 
    ($userId, '$orderDate', $totalAmount, $orderQuantity, '$productIdsStr', $status, '$paymentMethod', '$orderDate')";

    if ($conn->query($insertOrderQuery) === TRUE) {
        // Get the last inserted order ID
        $orderId = $conn->insert_id;

        // Insert each order item into `order_items` table
        foreach ($orderItems as $item) {
        }

        echo "<script>alert('Order placed successfully!'); window.location.href = 'admin.php?POS';</script>";
    } else {
        echo "Error placing order: " . $conn->error;
    }
}

$conn->close();
