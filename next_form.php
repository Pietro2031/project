<?php

$cartData = isset($_POST['cartData']) ? json_decode($_POST['cartData'], true) : [];

if (empty($cartData)) {
    echo "No items in the cart.";
    exit();
}

include('connection.php');

$totalPrice = 0;

function getAddonDetails($addonId, $conn)
{
    if (strpos($addonId, 'flavor-') !== false) {
        $id = str_replace('flavor-', '', $addonId);
        $query = "SELECT flavor_name AS name, price FROM coffee_flavors WHERE id = ?";
    } elseif (strpos($addonId, 'topping-') !== false) {
        $id = str_replace('topping-', '', $addonId);
        $query = "SELECT topping_name AS name, price FROM coffee_toppings WHERE id = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $addon = $result->fetch_assoc();

    return $addon;
}

function getCupSizeDetails($sizeId, $conn)
{
    $query = "SELECT size AS name, price FROM cup_size WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sizeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cupSize = $result->fetch_assoc();

    return $cupSize;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Order Summary</title>
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>
    <div class="order-summary">
        <h1>Your Order Summary</h1>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Add-ons</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Prepare data to pass to the front-end
                $consoleData = [];
                foreach ($cartData as $cartItem):
                    $cupSize = getCupSizeDetails($cartItem['size']['id'], $conn);

                    // Capture details for debugging in JS
                    $itemDetails = [
                        'product' => $cartItem['productName'],
                        'size' => $cupSize['name'],
                        'sizePrice' => $cupSize['price'],
                        'quantity' => $cartItem['quantity'],
                        'addons' => [],
                        'itemPrice' => 0
                    ];

                    $addonsPrice = 0;
                    if (!empty($cartItem['addons'])) {
                        foreach ($cartItem['addons'] as $addonId) {
                            $addon = getAddonDetails($addonId, $conn);
                            $itemDetails['addons'][] = [
                                'name' => $addon['name'],
                                'price' => $addon['price']
                            ];
                            $addonsPrice += $addon['price'];
                        }
                    }

                    $itemPrice = $cartItem['price'] + ($cupSize['price'] * $cartItem['quantity']) + $addonsPrice * $cartItem['quantity'];
                    $itemDetails['itemPrice'] = $itemPrice;
                    $totalPrice += $itemPrice;

                    // Add item details to console log data
                    $consoleData[] = $itemDetails;
                ?>
                    <tr>
                        <td><?php echo $cartItem['productName']; ?></td>
                        <td><?php echo $cupSize['name']; ?> (₱<?php echo number_format($cupSize['price'], 2); ?>)</td>
                        <td><?php echo $cartItem['quantity']; ?></td>
                        <td>
                            <?php
                            if (!empty($cartItem['addons'])) {
                                foreach ($cartItem['addons'] as $addonId) {
                                    $addon = getAddonDetails($addonId, $conn);
                                    echo $addon['name'] . " (₱" . number_format($addon['price'], 2) . ")<br>";
                                }
                            } else {
                                echo "None";
                            }
                            ?>
                        </td>
                        <td>₱<?php echo number_format($itemPrice, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-price">
            <h2>Total Price: ₱<?php echo number_format($totalPrice, 2); ?></h2>
        </div>

        <form action="place_order.php" method="POST">
            <input type="hidden" name="cartData" value='<?php echo json_encode($cartData); ?>'>
            <input type="hidden" name="totalPrice" value="<?php echo $totalPrice; ?>">
            <input type="hidden" name="addonsprice" value="<?= $addonsPrice ?>">
            <button type="submit" class="checkout-btn">Place Order</button>
        </form>
    </div>

    <script>
        // Log the PHP data to the browser console
        const consoleData = <?php echo json_encode($consoleData); ?>;
        console.log("Order Details:", consoleData);

        // Log the total price as well
        console.log("Total Price: ₱<?php echo number_format($totalPrice, 2); ?>");
    </script>
</body>

</html>