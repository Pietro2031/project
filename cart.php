<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('connection.php'); ?>
    <title>User Cart</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/cart.css">
    <style>
        .center {
            margin-top: 110px;
        }
    </style>
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
$getCartItemsQuery = " 
    SELECT 
        cart.id AS cart_id, 
        cart.user_id, 
        cart.product_id, 
        cart.quantity, 
        cart.size, 
        cart.addons AS addon_ids, 
        coffee_products.product_name AS product_name, 
        coffee_products.product_image AS product_image, 
        coffee_products.price AS price 
    FROM cart 
    INNER JOIN coffee_products ON cart.product_id = coffee_products.id 
    WHERE cart.user_id = ? 
";
$cartStmt = $conn->prepare($getCartItemsQuery);
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$result = $cartStmt->get_result();
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}
$totalCartValue = 0;
?>

<body>
    <?php include("header.php"); ?>
    <form method="post" action="" id="cartForm" enctype="multipart/form-data">
        <section class="center">
            <div class="Itemcart">
                <h1>Your Shopping Cart</h1>
                <div class="cart-container">
                    <?php if (count($cartItems) > 0): ?>
                        <table class="itemtable">
                            <tr>
                                <th style="width:60px;">Select</th>
                                <th style="text-align: start;">Product</th>
                                <th>Quantity</th>
                                <th>Size</th>
                                <th>Add-ons</th>
                                <th style="text-align: end; padding-right: 0px">Unit Price</th>
                            </tr>
                            <?php foreach ($cartItems as $item):
                                $productPrice = $item['price'];
                                $totalPrice = $item['quantity'] * $productPrice;

                                // Size charge logic
                                $sizeCharge = 0;
                                if ($item['size'] == 'M') {
                                    $sizeCharge = 10;
                                } elseif ($item['size'] == 'L') {
                                    $sizeCharge = 20;
                                }

                                // Add-ons handling
                                $addonIds = json_decode($item['addon_ids'], true);
                                $addonPrice = 0;
                                $addonNames = [];
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
                                        $flavorQuery = "SELECT flavor_name, price FROM coffee_flavors WHERE id IN ($flavorPlaceholders)";
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
                                        $toppingQuery = "SELECT topping_name, price FROM coffee_toppings WHERE id IN ($toppingPlaceholders)";
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
                                $addonsText = $addonNames ? implode(", ", $addonNames) : "None";

                                // Final price calculation
                                $totalPrice += ($addonPrice + $sizeCharge) * $item['quantity'];
                                $totalCartValue += $totalPrice;
                            ?>
                                <tr class="cart-item" style="height: 100px;">
                                    <td class="select">
                                        <input type="checkbox" name="selectedItems[]" value="<?= $item['cart_id']; ?>" onchange="updateTotal(this)">
                                    </td>
                                    <td>
                                        <div class="product">
                                            <img src="<?= $item['product_image']; ?>" alt="Product Image" class="cart-item-image">
                                            <div style="text-align: start;font-size: 12px;"> <?= $item['product_name']; ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" value="<?= $item['quantity']; ?>" min="1" data-price="<?= $productPrice; ?>" data-cart-id="<?= $item['cart_id']; ?>" data-addon-price="<?= $addonPrice; ?>" data-size-charge="<?= $sizeCharge; ?>" onchange="updateQuantity(this)">
                                    </td>
                                    <td>
                                        <div class="size"><?= $item['size']; ?></div>
                                    </td>
                                    <td>
                                        <div class="addons"><?= $addonsText; ?></div>
                                    </td>
                                    <td>
                                        <div class="total-price">
                                            <p class="sumtext"><?= $item['quantity']; ?> x ₱<?= number_format($productPrice, 2) ?> + Add-ons (₱<?= number_format($addonPrice * $item['quantity'], 2) ?>) + Size Upgrade (₱<?= number_format($sizeCharge * $item['quantity'], 2) ?>)</p>
                                            <p class="item-total-price">₱<?= number_format($totalPrice, 2) ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php if (count($cartItems) > 0): ?>
            <div class="CartTotal">
                <span id="totalCartValue">₱<?= number_format($totalCartValue, 2) ?></span>
                <p>Shipping & taxes calculated at checkout.</p>
                <div>
                    <button type="submit" class="deletebtn" name="deleteSelected" formaction="deleteCartItem.php" onclick="return confirmAction()">Delete Selected</button>
                    <button type="submit" class="buybtn" name="buySelected" formaction="processCartAction.php">Check out →</button>
                </div>
            </div>
        <?php endif; ?>
    </form>
    <script>
        function confirmAction() {
            var checkboxes = document.getElementsByName('selectedItems[]');
            var checkedBoxes = Array.from(checkboxes).filter(checkbox => checkbox.checked);
            if (checkedBoxes.length === 0) {
                alert("Please select an item to proceed to checkout.");
                return false;
            } else {
                return confirm("Would you like to confirm the deletion of selected items?");
            }
        }

        function updateTotal(checkbox) {
            var checkboxes = document.getElementsByName('selectedItems[]');
            var total = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    var totalPriceElement = checkboxes[i].closest('.cart-item').querySelector('.item-total-price');
                    var totalPriceText = totalPriceElement.textContent.trim();
                    var totalPrice = parseFloat(totalPriceText.replace(/[^\d.]/g, ''));
                    total += totalPrice;
                }
            }
            document.getElementById('totalCartValue').textContent = '₱' + total.toFixed(2);
        }

        function updateQuantity(input) {
            var quantity = parseInt(input.value);
            var price = parseFloat(input.dataset.price);
            var addonPrice = parseFloat(input.dataset.addonPrice);
            var sizeCharge = parseFloat(input.dataset.sizeCharge);
            var cartId = input.dataset.cartId;
            if (isNaN(quantity) || isNaN(price)) {
                console.error("Invalid quantity or price");
                return;
            }

            var newTotalPrice = quantity * price + (addonPrice + sizeCharge) * quantity;
            var newAddonTotal = addonPrice * quantity;
            var newSizeChargeTotal = sizeCharge * quantity;
            var totalPriceElement = input.closest('.cart-item').querySelector('.item-total-price');
            totalPriceElement.textContent = '₱' + newTotalPrice.toFixed(2);
            var sumtextElement = input.closest('.cart-item').querySelector('.sumtext');
            sumtextElement.textContent = quantity + ' x ₱' + price.toFixed(2) + ' + Add-ons (₱' + newAddonTotal.toFixed(2) + ') + Size Upgrade (₱' + newSizeChargeTotal.toFixed(2) + ')';
            updateCartTotal();
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "updateQuantity.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        showNotification("Quantity updated successfully!");
                    } else {
                        showNotification("Error updating quantity. Please try again.", "error");
                    }
                }
            };
            xhr.send("cart_id=" + cartId + "&quantity=" + quantity);
        }

        function updateCartTotal() {
            var total = 0;
            var cartItems = document.querySelectorAll('.cart-item');
            cartItems.forEach(function(item) {
                var totalPriceElement = item.querySelector('.item-total-price');
                var totalPriceText = totalPriceElement.textContent.trim();
                var totalPrice = parseFloat(totalPriceText.replace(/[^\d.]/g, ''));
                total += totalPrice;
            });
            document.getElementById('totalCartValue').textContent = '₱' + total.toFixed(2);
        }

        function showNotification(message, type = "success") {
            var notification = document.createElement("div");
            notification.className = "notification " + type;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(function() {
                document.body.removeChild(notification);
            }, 3000);
        }
    </script>
    <?php include "footer.php"; ?>
</body>

</html>