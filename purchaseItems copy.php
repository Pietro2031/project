<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("connect.php");;
    include("query.php") ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/purchaseItems.css">
</head>

<?php

session_start();

if (!isset($_SESSION['selectedItems'])) {
    echo "No items selected for purchase.";
    exit();
}

$selectedItems = $_SESSION['selectedItems'];

$getSelectedItemsQuery = "SELECT items.ItemID, items.ItemName, items.ItemImg, cart.Quantity, items.Price
                         FROM cart
                         INNER JOIN items ON cart.ItemID = items.ItemID
                         WHERE cart.customer_id = ?
                         AND cart.cart_id IN ($selectedItems)";
$stmt = mysqli_prepare($con, $getSelectedItemsQuery);
mysqli_stmt_bind_param($stmt, "i", $UserID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo "Error retrieving selected items: " . mysqli_error($con);
    exit();
}
?>

<body>
    <?php
    include("header.php");
    ?>
    <section class="cashoutsec">
        <div class="checkout">
            <?php
            $totalPurchaseValue = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $totalPrice = $row['Quantity'] * $row['Price'];
                $totalPurchaseValue += $totalPrice;
            ?>

                <div class="itemrow">
                    <div class="itemproduct">
                        <div class="itemimg">
                            <img src="<?= $row['ItemImg']; ?>">
                            <div class="itemqnty"><?= $row['Quantity']; ?></div>
                        </div>
                        <div class="itemname"><?= $row['ItemName']; ?></div>
                    </div>
                    <div class="itemtotal">
                        <p class="itemprice">₱<?= $totalPrice; ?></p>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="promoCode" style="opacity: 0;">
                <input type="text" id="promoCode" placeholder="Enter promo code">
                <button onclick="applyPromoCode()" class="promobtn">Apply</button>
            </div>
            <div class="total">
                <p><b>Total</b></p>
                <p><b>₱<?= $totalPurchaseValue; ?></b></p>
            </div>
            <p class="tooltip">Click to proceed to payment and finalize your purchase.</p>
            <button class="buybtn" onclick="checkout()">Checkout</button>
        </div>
    </section>


    <script>
        function checkout() {
            window.location.href = "payment.php";
        }

        function applyPromoCode() {
            var promoCode = document.getElementById("promoCode").value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "validate_promo_code.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.valid) {
                        alert("Promo code applied! You've received a discount of " + response.discount + "%");

                        var discountPercentage = response.discount / 100;
                        var discountedTotal = <?= $totalPurchaseValue; ?> * (1 - discountPercentage);

                        sessionStorage.setItem('discountedPrice', discountedTotal);

                        var formData = new FormData();
                        formData.append('promoCode', promoCode);
                        formData.append('discountedPrice', discountedTotal);

                        var xhrDiscountedPrice = new XMLHttpRequest();
                        xhrDiscountedPrice.open("POST", "process_discounted_price.php", true);
                        xhrDiscountedPrice.send(formData);

                        document.querySelector(".total p:last-child").innerHTML = "<b>₱" + discountedTotal.toFixed(2) + "</b>";
                    } else {
                        alert("Invalid promo code. Please try again.");
                    }
                }
            };
            xhr.send("promoCode=" + encodeURIComponent(promoCode));
        }
    </script>
    <?php include('footer.html'); ?>
</body>

</html>