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
                    <th>Size</th> <!-- New column for cup size -->
                    <th>Quantity</th>
                    <th>Add-ons</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartData as $cartItem): ?>
                    <?php

                    $cupSize = getCupSizeDetails($cartItem['size']['id'], $conn);
                    ?>
                    <tr>
                        <td><?php echo $cartItem['productName']; ?></td>
                        <td><?php echo $cupSize['name']; ?> (₱<?php echo number_format($cupSize['price'], 2); ?>)</td> <!-- Display cup size name and price -->
                        <td><?php echo $cartItem['quantity']; ?></td>
                        <td>
                            <?php
                            $addonsPrice = 0;
                            if (!empty($cartItem['addons'])) {
                                foreach ($cartItem['addons'] as $addonId) {
                                    $addon = getAddonDetails($addonId, $conn);
                                    echo $addon['name'] . " (₱" . number_format($addon['price'], 2) . ")<br>";
                                    $addonsPrice += $addon['price'];
                                }
                            } else {
                                echo "None";
                            }
                            ?>
                        </td>
                        <td>
                            <?php

                            $itemPrice = ($cartItem['price'] + $cupSize['price'] + $addonsPrice) * $cartItem['quantity'];
                            echo "₱" . number_format($itemPrice, 2);
                            $totalPrice += $itemPrice;
                            ?>
                        </td>
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
            <button type="submit" class="checkout-btn">Place Order</button>
        </form>
    </div>
</body>

</html>