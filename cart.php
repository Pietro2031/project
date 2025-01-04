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

// Retrieve the user ID based on the session username
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

// Fetch items in the user's cart
$getCartItemsQuery = "
    SELECT 
        cart.id AS cart_id, 
        cart.user_id, 
        cart.product_id, 
        cart.quantity, 
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

// Fetch all rows into an array
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

$totalCartValue = 0;

// Debug: Print the cart items array
// Uncomment to debug the array structure
/*
echo "<pre>";
print_r($cartItems);
echo "</pre>";
*/
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
                                <th style="text-align: end; padding-right: 0px">Unit Price</th>
                            </tr>
                            <?php foreach ($cartItems as $item):
                                $totalPrice = $item['quantity'] * $item['price'];
                                $totalCartValue += $totalPrice;
                            ?>
                                <tr class="cart-item" style="height: 100px;">
                                    <td class="select">
                                        <input type="checkbox" name="selectedItems[]" value="<?= $item['cart_id']; ?>" onchange="updateTotal(this)">
                                    </td>
                                    <td>
                                        <div class="product">
                                            <img src="<?= $item['product_image']; ?>" alt="Product Image" class="cart-item-image">
                                            <div style="text-align: start;"> <?= $item['product_name']; ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" value="<?= $item['quantity']; ?>" min="1" data-price="<?= $item['price']; ?>" data-cart-id="<?= $item['cart_id']; ?>" onchange="updateQuantity(this)">
                                    </td>
                                    <td>
                                        <div class="total-price">
                                            <p class="sumtext"><?= $item['quantity']; ?> x $<?= $item['price'] ?></p>
                                            <p class="item-total-price">$<?= number_format($totalPrice, 2) ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
        </section>
        <?php if (count($cartItems) > 0): ?>
            <div class="CartTotal">
                <span id="totalCartValue">$<?= number_format($totalCartValue, 2) ?></span>
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
            document.getElementById('totalCartValue').textContent = '$' + total.toFixed(2);
        }


        function updateCartTotal() {
            var checkboxes = document.getElementsByName('selectedItems[]');
            var total = 0;

            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    var totalPriceElement = checkbox.closest('.cart-item').querySelector('.item-total-price');
                    var totalPriceText = totalPriceElement.textContent.trim();
                    var totalPrice = parseFloat(totalPriceText.replace(/[^\d.]/g, ''));
                    total += totalPrice;
                }
            });

            document.getElementById('totalCartValue').textContent = '$' + total.toFixed(2);
        }


        function updateCartTotal() {
            var checkboxes = document.getElementsByName('selectedItems[]');
            var total = 0;
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    var totalPriceElement = checkbox.closest('.cart-item').querySelector('.item-total-price');
                    var totalPriceText = totalPriceElement.textContent.trim();
                    var totalPrice = parseFloat(totalPriceText.replace(/[^\d.]/g, ''));
                    total += totalPrice;
                }
            });
            document.getElementById('totalCartValue').textContent = '$' + total.toFixed(2);
        }

        function updateQuantity(input) {
            var quantity = parseInt(input.value); // Convert quantity to an integer
            var price = parseFloat(input.dataset.price); // Convert price to a float
            var cartId = input.dataset.cartId; // ID of the cart item

            if (isNaN(quantity) || isNaN(price)) {
                console.error("Invalid quantity or price");
                return;
            }

            // Calculate the new total price for this item
            var newTotalPrice = quantity * price;

            // Update the total price displayed for this item
            var totalPriceElement = input.closest('.cart-item').querySelector('.item-total-price');
            totalPriceElement.textContent = '$' + newTotalPrice.toFixed(2);

            // Update the summary price text
            var sumtextElement = input.closest('.cart-item').querySelector('.sumtext');
            sumtextElement.textContent = quantity + ' x $' + price.toFixed(2);

            // Update the total cart value
            updateCartTotal();

            // Send the updated quantity to the server via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "updateQuantity.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        // Show a notification when the quantity is successfully updated
                        showNotification("Quantity updated successfully!");
                    } else {
                        // Show an error notification if the update fails
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

            document.getElementById('totalCartValue').textContent = '$' + total.toFixed(2);
        }

        function showNotification(message, type = "success") {
            // Create notification div
            var notification = document.createElement("div");
            notification.className = "notification " + type;
            notification.textContent = message;

            // Add the notification to the body
            document.body.appendChild(notification);

            // Remove the notification after 3 seconds
            setTimeout(function() {
                document.body.removeChild(notification);
            }, 3000000);
        }
    </script>
    <?php include "footer.php"; ?>
</body>

</html>