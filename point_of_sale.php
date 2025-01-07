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
<div id="quantityModal" class="modal" style="background: radial-gradient(black, transparent);">
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
      <div class="user-info">
        <h1>Customer Information</h1>
        <div><b>Name:</b> <input type="text" placeholder="Full name"></div>
        <div><b>Contact Number:</b> <input type="text" placeholder="09911180759"></div>
        <div><b>Address:</b> <input type="text" placeholder="bustos bulacan"></div>
      </div>
      <div class="itemrow">
      </div>
      <div class="checkout-info">
        <div class="total-payment">
          <span>Total Price:</span>
          <span>₱0.00</span>
        </div>
        <button type="button" class="checkout-btn">Proceed to Checkout</button>
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
    document.getElementById('modalPrice').textContent = '₱' + price;
    document.getElementById('modalProductImageDisplay').src = image; 
    document.getElementById('quantityModal').style.display = 'block';
  } 
   
  document.getElementById('quantityModal').addEventListener('click', function(event) {
    if (event.target == document.getElementById('quantityModal')) {
      document.getElementById('quantityModal').style.display = 'none';
    }
  }); 
   
  function addToCart() {
     
    const productId = document.getElementById('modalProductId').value;
    const productName = document.getElementById('modalProductName').value;
    const productPrice = parseFloat(document.getElementById('modalProductPrice').value);
    const productImage = document.getElementById('modalProductImage').value;
    const quantity = parseInt(document.getElementById('quantityInput').value); 
     
    const sizeInput = document.querySelector('input[name="size"]:checked');
    const sizeAdjustment = sizeInput ? parseFloat(sizeInput.nextElementSibling.nextElementSibling.innerText.replace('₱', '')) : 0;
    const sizeText = sizeInput ? sizeInput.nextElementSibling.innerText : ''; 
     
    const addonInputs = document.querySelectorAll('input[name="addons[]"]:checked');
    let addons = [];
    let addonCost = 0; 
    addonInputs.forEach(function(addon) {
      const addonLabel = document.querySelector('label[for="' + addon.id + '"]').innerText;
      const addonPrice = parseFloat(addonLabel.match(/₱(\d+.\d+)/)[1]);
      addons.push(addonLabel);
      addonCost += addonPrice;
    }); 
     
    const totalPrice = (productPrice + sizeAdjustment + addonCost) * quantity; 
     
    const checkoutSection = document.querySelector('.checkout .itemrow'); 
    const newCheckoutItem = `
      <div class="itemproduct">
        <div class="itemimg">
          <img src="${productImage}" alt="${productName}">
          <div class="itemqnty">${quantity}</div>
        </div>
        <div class="slidedown">
          <div class="itemname">${productName}</div>
          <div class="itemdetails">
            <p><strong>Base Price:</strong> ₱${productPrice.toFixed(2)}</p>
            <p><strong>Size Adjustment (${sizeText}):</strong> ₱${sizeAdjustment.toFixed(2)}</p>
            <p><strong>Add-ons:</strong> ${addons.join(', ') || 'None'}</p>
          </div>
        </div>
        <div class="price">₱${totalPrice.toFixed(2)}</div>
      </div>
    `; 
    checkoutSection.insertAdjacentHTML('beforeend', newCheckoutItem); 
     
    const totalPriceElement = document.querySelector('.total-payment span:last-child');
    const currentTotal = parseFloat(totalPriceElement.innerText.replace('₱', ''));
    const newTotal = currentTotal + totalPrice;
    totalPriceElement.innerText = '₱' + newTotal.toFixed(2); 
     
    document.getElementById('quantityModal').style.display = 'none';
  }
</script>