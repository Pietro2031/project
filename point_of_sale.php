<?php
include('connection.php');

$categoryQuery = "SELECT * FROM coffee_category";
$categoryResult = $conn->query($categoryQuery);

$productQuery = "
SELECT coffee_products.*, coffee_base.base_name, coffee_flavors.flavor_name, coffee_toppings.topping_name 
FROM coffee_products
LEFT JOIN coffee_base ON coffee_products.drink_bases = coffee_base.id
LEFT JOIN coffee_flavors ON coffee_products.flavor_id = coffee_flavors.id
LEFT JOIN coffee_toppings ON coffee_products.toppings_id = coffee_toppings.id";
$productResult = $conn->query($productQuery);
?>
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="css/pos.css">
<div id="quantityModal" class="modal" style="background: #80808080">
    <div id="quantityForm">
        <input type="hidden" name="product_id" id="modalProductId">
        <input type="hidden" name="product_name" id="modalProductName">
        <input type="hidden" name="product_description" id="modalProductDesc">
        <input type="hidden" name="product_price" id="modalProductPrice">
        <input type="hidden" name="product_img" id="modalProductImage">
        <div class="modal-confirm-order">
            <div class="div-item-info">
                <img class="itemimg" src="" id="modalProductImageDisplay" />
                <div class="div-item-text-info">
                    <div class="itemname" id="modalProductNameDisplay"></div>
                    <div class="iteminfo" id="modalProductDescription"></div>
                    <div class="iteminfo" id="modalSold"></div>
                    <div class="div-price">
                        <div class="price" id="modalPrice"></div>
                    </div>
                </div>
            </div>
            <div class="div-inputs">
                <div class="div-2">
                    <div class="label-size">Quantity</div>
                    <input type="number" name="quantity" id="quantityInput" min="1" value="1">
                </div>
            </div>
            <div class="div-inputs">
                <div class="div-22">
                    <div class="label-size">Size</div>
                    <div class="div-size">
                        <?php
                        $cupSizesQuery = "SELECT id, size, quantity, price, img FROM cup_size";
                        $cupSizesResult = $conn->query($cupSizesQuery);
                        if ($cupSizesResult && $cupSizesResult->num_rows > 0):
                            while ($cupSize = $cupSizesResult->fetch_assoc()):
                                $isDisabled = $cupSize['quantity'] == 0 ? 'disabled' : '';
                        ?>
                                <div class="div-size-info">
                                    <input type="radio" name="size"
                                        value="<?php echo $cupSize['id']; ?>"
                                        id="size-<?php echo $cupSize['id']; ?>"
                                        <?php echo $isDisabled; ?> required>
                                    <label for="size-<?php echo $cupSize['id']; ?>">
                                        <?php echo $cupSize['size']; ?>
                                    </label>
                                    <p><?php echo $cupSize['quantity'] > 0 ? '+₱' . number_format($cupSize['price'], 2) : 'Out of Stock'; ?></p>
                                </div>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </div>
                </div>
                <div class="div-23">
                    <div class="label-size">Add-ons</div>
                    <?php
                    
                    $flavorsQuery = "SELECT id, flavor_name, quantity, price FROM coffee_flavors";
                    $flavorsResult = $conn->query($flavorsQuery);
                    $toppingsQuery = "SELECT id, topping_name, quantity, price FROM coffee_toppings";
                    $toppingsResult = $conn->query($toppingsQuery);
                    ?>
                    <div class="div-add-ons" id="addonsContainer">
                        <h4>Flavors</h4>
                        <div class="sliderights">
                            <?php if ($flavorsResult && $flavorsResult->num_rows > 0): ?>
                                <?php while ($flavor = $flavorsResult->fetch_assoc()): ?>
                                    <?php $isDisabled = $flavor['quantity'] == 0 ? 'disabled' : ''; ?>
                                    <div class="addon-item">
                                        <input type="checkbox" name="addons[]" value="flavor-<?php echo $flavor['id']; ?>" id="flavor-<?php echo $flavor['id']; ?>" <?php echo $isDisabled; ?>>
                                        <label for="flavor-<?php echo $flavor['id']; ?>">
                                            <?php echo $flavor['flavor_name']; ?>
                                            (₱<?php echo number_format($flavor['price'], 2); ?>)
                                        </label>
                                        <?php if ($flavor['quantity'] == 0): ?>
                                            <span class="out-of-stock">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No flavors available.</p>
                            <?php endif; ?>
                        </div>
                        <h4>Toppings</h4>
                        <div class="sliderights">
                            <?php if ($toppingsResult && $toppingsResult->num_rows > 0): ?>
                                <?php while ($topping = $toppingsResult->fetch_assoc()): ?>
                                    <?php $isDisabled = $topping['quantity'] == 0 ? 'disabled' : ''; ?>
                                    <div class="addon-item">
                                        <input type="checkbox" name="addons[]" value="topping-<?php echo $topping['id']; ?>" id="topping-<?php echo $topping['id']; ?>" <?php echo $isDisabled; ?>>
                                        <label for="topping-<?php echo $topping['id']; ?>">
                                            <?php echo $topping['topping_name']; ?>
                                            (₱<?php echo number_format($topping['price'], 2); ?>)
                                        </label>
                                        <?php if ($topping['quantity'] == 0): ?>
                                            <span class="out-of-stock">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No toppings available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="button">
                <div class="button">
                    <button type="button" class="button2" name="add_to_cart" onclick="addToCart()">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="slideright1">
    <section class="products" style="max-width: 650px; margin: 0;">
        <div class="user-info">All Products</div>
        <div class="product-grid">
            <?php while ($product = $productResult->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                    <h3><?php echo $product['product_name']; ?></h3>
                    <p class="price">₱ <?php echo number_format($product['price'], 2); ?></p>
                    <button class="add-btn" onclick="openModal(
                    '<?php echo $product['id']; ?>',
                    '<?php echo $product['product_name']; ?>',
                    '<?php echo $product['product_description']; ?>',
                    '<?php echo number_format($product['price'], 2); ?>',
                    '<?php echo $product['product_image']; ?>'
                    )">Order</button>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <section class="cashoutsec">
        <div class="checkout">
            <div class="itemrow">
            </div>
            <div class="checkout-info">
                <div class="total-payment">
                    <span>Total Price:</span>
                    <span>₱0.00</span>
                </div>
                <form id="checkoutForm" action="next_form.php" method="POST">
                    <input type="hidden" name="cartData" id="cartData">
                    <button type="button" class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                </form>
            </div>
        </div>
    </section>
</div>
<script>
    function openModal(id, name, description, price, image) {
        document.getElementById('modalProductId').value = id;
        document.getElementById('modalProductName').value = name;
        document.getElementById('modalProductDesc').value = description;
        document.getElementById('modalProductPrice').value = price;
        document.getElementById('modalProductImage').value = image;
        document.getElementById('modalProductNameDisplay').textContent = name;
        document.getElementById('modalProductDescription').textContent = description;
        document.getElementById('modalPrice').textContent = "₱" + price;
        document.getElementById('modalProductImageDisplay').src = image;
        document.getElementById('quantityModal').style.display = 'block';
    }

    function addToCart() {
        var productId = document.getElementById('modalProductId').value;
        var productName = document.getElementById('modalProductName').value;
        var productPrice = parseFloat(document.getElementById('modalProductPrice').value);
        var quantity = parseInt(document.getElementById('quantityInput').value);
        var totalPrice = productPrice * quantity;

        
        var addons = [];
        var checkboxes = document.querySelectorAll('input[name="addons[]"]:checked');
        checkboxes.forEach(function(checkbox) {
            addons.push(checkbox.value);
        });

        
        var selectedSize = document.querySelector('input[name="size"]:checked');
        if (!selectedSize) {
            alert('Please select a cup size!');
            return;
        }
        var cupSizeId = selectedSize.value;
        var cupSizeText = selectedSize.nextElementSibling.textContent;

        
        var cartItem = {
            productId: productId,
            productName: productName,
            quantity: quantity,
            addons: addons,
            size: {
                id: cupSizeId,
                name: cupSizeText
            },
            price: totalPrice
        };

        
        var cartData = document.getElementById('cartData').value;
        var cart = cartData ? JSON.parse(cartData) : [];
        cart.push(cartItem);
        document.getElementById('cartData').value = JSON.stringify(cart);

        
        var itemRow = document.createElement('div');
        itemRow.classList.add('itemrow');
        itemRow.innerHTML = '<p>' + productName + ' (' + cupSizeText + ') (x' + quantity + ')</p><p>₱' + totalPrice.toFixed(2) + '</p>';
        document.querySelector('.checkout .itemrow').appendChild(itemRow);

        
        var currentTotal = parseFloat(document.querySelector('.total-payment span:last-child').textContent.replace('₱', ''));
        var newTotal = currentTotal + totalPrice;
        document.querySelector('.total-payment span:last-child').textContent = '₱' + newTotal.toFixed(2);

        
        document.getElementById('quantityModal').style.display = 'none';
    }


    function proceedToCheckout() {
        var cartData = document.getElementById('cartData').value;
        if (cartData.length === 0) {
            alert('Your cart is empty!');
        } else {
            document.getElementById('checkoutForm').submit();
        }
    }
</script>