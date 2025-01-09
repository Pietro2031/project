<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('connection.php');
    session_start(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Your Drink</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/makeyourown.css">
    <link rel="stylesheet" href="css/payment.css">
</head>

<body>
    <?php include('header.php'); ?>
    <?php if ($_SESSION['verified'] != 'verified') {
        echo "<script> window.location.href = 'otp.php'; alert('Please log in to a verified account first!');</script>";
    } ?>
    <section class="section">
        <h1>Create Your Drink</h1>
        <div class="customization-container">
            <div class="base-selection">
                <h2>What kind of coffee?</h2>
                <div class="base-options">
                    <?php
                    $baseQuery = "SELECT * FROM coffee_base";
                    $baseResult = $conn->query($baseQuery);
                    if ($baseResult->num_rows > 0) {
                        while ($base = $baseResult->fetch_assoc()) {
                            echo '<div class="base-item">
<img src="' . $base['img'] . '" alt="' . $base['base_name'] . '">
<div class="base-name">' . $base['base_name'] . '</div>
<div class="base-price">₱' . number_format($base['price'], 2) . '</div> 
<button class="select-base-btn" data-id="' . $base['id'] . '" data-name="' . $base['base_name'] . '" data-price="' . $base['price'] . '" data-img="' . $base['img'] . '">Select</button>
</div>';
                        }
                    } else {
                        echo '<p>No drink bases available.</p>';
                    }
                    ?>
                </div>
            </div>
            <div class="cup-container">
                <h2>Your Custom Drink</h2>
                <div class="virtual-cup">
                    <img src="img/cup.png" alt="" style=" filter: drop-shadow(0px 0.1px 4px #999); ">
                    <img src="<?= $logo ?>" style=" z-index: 99;width: 150px;height: 150px;position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);">
                    <div id="cup-content" class="cup-content"></div>
                </div>
                <div class="total-price">
                    Total Price: <span id="total-price">₱0.00</span>
                </div>
                <!-- <div class="indicator" id="ingredient-indicator">Remaining Ingredients: 7</div> -->
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
                                        value="<?php echo $cupSize['size']; ?>"
                                        id="size-<?php echo $cupSize['size']; ?>"
                                        data-price="<?php echo $cupSize['price']; ?>"
                                        <?php echo $isDisabled; ?> required>
                                    <label for="size-<?php echo $cupSize['size']; ?>">
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
                <button id="checkout-btn" class="checkout-btn">Checkout</button>
            </div>
            <div class="ingredient-selector">
                <h2>Add Ingredients</h2>
                <div class="ingredient-tabs">
                    <button class="tab active" data-category="flavors">Flavors</button>
                    <button class="tab" data-category="toppings">Toppings</button>
                </div>
                <div class="ingredients-list" id="ingredients-list">
                    <?php
                    function displayIngredients($conn, $table, $category)
                    {
                        $ingredientQuery = "SELECT * FROM $table";
                        $ingredientResult = $conn->query($ingredientQuery);
                        if ($ingredientResult->num_rows > 0) {
                            while ($ingredient = $ingredientResult->fetch_assoc()) {
                                $nameField = ($table == "coffee_flavors") ? "flavor_name" : "topping_name";
                                $imgPath = $ingredient['img'];
                                echo '<div class="ingredient-item" data-category="' . $category . '">
                                    <img src="' . $imgPath . '" alt="' . $ingredient[$nameField] . '">
                                    <span class="base-name">' . $ingredient[$nameField] . ' - ₱' . number_format($ingredient['price'], 2) . '</span>
                                    <button data-id="' . $ingredient['id'] . '" data-name="' . $ingredient[$nameField] . '" 
                                    data-price="' . $ingredient['price'] . '" 
                                    data-img="' . $imgPath . '" 
                                    data-category="' . $category . '">Add</button>
                                    </div>';
                            }
                        } else {
                            echo '<p>No ' . $category . ' available.</p>';
                        }
                    }
                    displayIngredients($conn, "coffee_flavors", "flavors");
                    displayIngredients($conn, "coffee_toppings", "toppings");
                    ?>
                </div>
            </div>
        </div>
    </section>
    <div class="checkout-popup done" id="checkout-popup">
        <h2>Checkout</h2>
        <p>Total: <span id="popup-total-price">₱0.00</span></p>
        <form method="post" action="" class="payment-form">
            <div class="form-group">
                <label for="paymentMode">Payment Mode:</label>
                <div class="method_img">
                    <img src="uploads/method/link-91720ed84858d490ca62142de0494559.png" alt="GCash">
                    <img src="uploads/method/link-cf7aaa8b59e07c8548d2f03f0d930acb.png" alt="Debit Card">
                    <img src="uploads/method/link-4a1f1c2d9ee1820ccc9621b44f277387.png" alt="PayPal">
                    <img src="uploads/method/link-8efc3b564e08e9e864ea83ab43d9f913.png" alt="Counter Payment">
                </div>
                <select name="paymentMode" id="paymentMode" required>
                    <option value="GCash">GCash</option>
                    <option value="Debit Card">Debit Card</option>
                </select>
            </div>
            <div class="payment-button">
                <button class="buybtn" type="button" onclick="confirmOrder()">Submit Payment</button>
                <button class="cancelbtn" type="button" onclick="closeCheckout()">Cancel</button>
            </div>
        </form>
    </div>
    <div class="checkout-overlay" id="checkout-overlay"></div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const checkoutBtn = document.getElementById("checkout-btn");
            const checkoutPopup = document.getElementById("checkout-popup");
            const checkoutOverlay = document.getElementById("checkout-overlay");
            const popupTotalPrice = document.getElementById("popup-total-price");
            checkoutBtn.addEventListener("click", () => {
                popupTotalPrice.textContent = document.getElementById("total-price").textContent;
                checkoutPopup.style.display = "block";
                checkoutOverlay.style.display = "block";
            });
            window.closeCheckout = () => {
                checkoutPopup.style.display = "none";
                checkoutOverlay.style.display = "none";
            };
            window.confirmOrder = () => {
                const paymentMethod = document.getElementById("paymentMode").value;
                const totalPrice = parseFloat(document.getElementById("popup-total-price").textContent.replace('₱', ''));
                const base = document.querySelector(".base");
                const size = document.querySelector("input[name='size']:checked");
                const flavor = document.querySelector(".cup-content .flavor img");
                const toppings = Array.from(document.querySelectorAll(".cup-content .topping img"));
                if (!totalPrice || totalPrice <= 0) {
                    alert("Your order total is invalid.");
                    return;
                }
                if (!base) {
                    alert("Please select a drink base.");
                    return;
                }
                if (!size) {
                    alert("Please select a drink size.");
                    return;
                }
                const orderData = {
                    base: {
                        name: base.querySelector("img").alt,
                        id: base.dataset.id || null,
                    },
                    size: {
                        name: size.value,
                        price: parseFloat(size.dataset.price) || 0,
                    },
                    flavor: flavor ? {
                        name: flavor.alt,
                        id: flavor.parentElement.dataset.id || null,
                    } : null,
                    toppings: toppings.map(topping => ({
                        name: topping.alt,
                        id: topping.parentElement.dataset.id || null,
                    })),
                    total_price: totalPrice,
                    payment_method: paymentMethod,
                };
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "save_order.php", true);
                xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    displayReceivedData(response.data_received);
                                    alert("Your order has been successfully placed! Thank you for your purchase.");
                                    window.location.href = "home.php";
                                } else {
                                    alert(`Failed to process your order: ${response.message}`);
                                    if (response.error) console.error(`Error details: ${response.error}`);
                                }
                            } catch (e) {
                                alert("An unexpected error occurred while processing your request.");
                                console.error("Failed to parse server response:", e);
                            }
                        } else {
                            alert(`An error occurred: HTTP ${xhr.status}`);
                            console.error("Server error:", xhr.statusText);
                        }
                    }
                };
                xhr.onerror = function() {
                    alert("A network error occurred. Please check your internet connection and try again.");
                    console.error("Network error");
                };
                xhr.send(JSON.stringify(orderData));
            };

            function displayReceivedData(data) {
                const resultContainer = document.createElement('div');
                resultContainer.style.padding = '20px';
                resultContainer.style.border = '1px solid #ccc';
                resultContainer.style.marginTop = '20px';
                resultContainer.style.backgroundColor = '#f9f9f9';
                resultContainer.innerHTML = `
                    <h3>Order Summary</h3>
                    <p><strong>Base:</strong> ${data.base.name} (ID: ${data.base.id})</p>
                    <p><strong>Size:</strong> ${data.size.name} (Price: ₱${data.size.price})</p>
                    <p><strong>Flavor:</strong> ${data.flavor ? `${data.flavor.name} (ID: ${data.flavor.id})` : 'None'}</p>
                    <p><strong>Toppings:</strong> ${data.toppings.length > 0
                    ? data.toppings.map(topping => `${topping.name} (ID: ${topping.id})`).join(', ')
                    : 'None'}</p>
                    <p><strong>Total Price:</strong> ₱${data.total_price}</p>
                    <p><strong>Payment Method:</strong> ${data.payment_method}</p>
`;
                document.body.appendChild(resultContainer);
            }
            const cupContent = document.getElementById("cup-content");
            const totalPriceElement = document.getElementById("total-price");
            const ingredientTabs = document.querySelectorAll(".tab");
            const ingredientsList = document.getElementById("ingredients-list");
            const ingredientIndicator = document.getElementById("ingredient-indicator");
            let totalPrice = 0;
            let ingredientsCount = 0;
            let sizePrice = 0;
            let maxIngredients = 7;
            let toggleAlignment = true;
            document.querySelectorAll(".select-base-btn").forEach(button => {
                button.addEventListener("click", () => {
                    document.querySelectorAll(".base-item").forEach(item => item.classList.remove("selected"));
                    button.parentElement.classList.add("selected");
                    const basePrice = parseFloat(button.dataset.price);
                    totalPrice = basePrice + sizePrice;
                    ingredientsCount = 0;
                    cupContent.innerHTML = `
                        <div class="base" data-id="${button.dataset.id}">
                        <img src="${button.dataset.img}" alt="${button.dataset.name}" title="${button.dataset.name}">
                        </div>`;
                    toggleAlignment = true;
                    updateTotalPrice();
                });
            });
            document.querySelectorAll(".ingredient-item button").forEach(button => {
                button.addEventListener("click", () => {
                    const category = button.dataset.category;
                    const ingredientName = button.dataset.name.toLowerCase();
                    const ingredientPrice = parseFloat(button.dataset.price);
                    const ingredientImg = button.dataset.img;
                    const ingredientId = button.dataset.id;
                    const existingIngredient = Array.from(cupContent.children).find(
                        ingredient => ingredient.querySelector("img").title.toLowerCase() === ingredientName
                    );
                    if (existingIngredient) {
                        alert(`You can only add one of each ingredient. "${ingredientName}" is already in your cup.`);
                        return;
                    }
                    const ingredientDiv = document.createElement("div");
                    if (category === "flavors") {
                        const existingFlavor = document.querySelector(".cup-content .flavor");
                        if (existingFlavor) {
                            totalPrice -= parseFloat(existingFlavor.dataset.price);
                            cupContent.removeChild(existingFlavor);
                        }
                        ingredientDiv.className = "ingredient flavor";
                    } else if (category === "toppings") {
                        ingredientDiv.className = "ingredient topping";
                    }
                    ingredientDiv.dataset.price = ingredientPrice;
                    ingredientDiv.dataset.id = ingredientId;
                    ingredientDiv.dataset.name = ingredientName;
                    ingredientDiv.innerHTML = `<img src="${ingredientImg}" alt="${ingredientName}" title="${ingredientName}">`;
                    cupContent.appendChild(ingredientDiv);
                    ingredientDiv.addEventListener("click", () => {
                        cupContent.removeChild(ingredientDiv);
                        totalPrice -= ingredientPrice;
                        ingredientsCount--;
                        updateTotalPrice();
                    });
                    totalPrice += ingredientPrice;
                    ingredientsCount++;
                    updateTotalPrice();
                });
            });
            window.confirmOrder = () => {
                const paymentMethod = document.getElementById("paymentMode").value;
                const totalPrice = parseFloat(document.getElementById("popup-total-price").textContent.replace('₱', ''));
                const base = document.querySelector(".base");
                const size = document.querySelector("input[name='size']:checked");
                const flavor = document.querySelector(".cup-content .flavor");
                const toppings = Array.from(document.querySelectorAll(".cup-content .topping"));
                if (!totalPrice || totalPrice <= 0) {
                    alert("Your order total is invalid.");
                    return;
                }
                if (!base) {
                    alert("Please select a drink base.");
                    return;
                }
                if (!size) {
                    alert("Please select a drink size.");
                    return;
                }
                const orderData = {
                    base: {
                        name: base.querySelector("img").alt,
                        id: base.dataset.id || null,
                    },
                    size: {
                        name: size.value,
                        price: parseFloat(size.dataset.price) || 0,
                    },
                    flavor: flavor ? {
                        name: flavor.dataset.name,
                        id: flavor.dataset.id || null,
                    } : null,
                    toppings: toppings.map(topping => ({
                        name: topping.dataset.name,
                        id: topping.dataset.id || null,
                    })),
                    total_price: totalPrice,
                    payment_method: paymentMethod,
                };
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "save_order.php", true);
                xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    displayReceivedData(response.data_received);
                                    alert("Your order has been successfully placed! Thank you for your purchase.");
                                    window.location.href = "home.php";
                                } else {
                                    alert(`Failed to process your order: ${response.message}`);
                                }
                            } catch (e) {
                                alert("An unexpected error occurred while processing your request.");
                                console.error("Failed to parse server response:", e);
                            }
                        } else {
                            alert(`An error occurred: HTTP ${xhr.status}`);
                        }
                    }
                };
                xhr.onerror = function() {
                    alert("A network error occurred. Please check your internet connection and try again.");
                };
                xhr.send(JSON.stringify(orderData));
            };

            function updateTotalPrice() {
                totalPriceElement.textContent = `₱${totalPrice.toFixed(2)}`;
            }
            <?php
            $cupSizesQuery = "SELECT size, price, max_ingredients FROM cup_size";
            $cupSizesResult = $conn->query($cupSizesQuery);
            $sizesData = [];
            if ($cupSizesResult && $cupSizesResult->num_rows > 0) {
                while ($size = $cupSizesResult->fetch_assoc()) {
                    $sizesData[] = $size;
                }
            }
            ?>
            document.querySelectorAll("input[name='size']").forEach(input => {
                input.addEventListener("change", () => {
                    const selectedSize = input.value;
                    const previousSizePrice = sizePrice;
                    const sizesData = <?php echo json_encode($sizesData); ?>;
                    const selectedSizeData = sizesData.find(size => size.size === selectedSize);
                    if (selectedSizeData) {
                        sizePrice = parseFloat(selectedSizeData.price) || 0;
                        maxIngredients = parseInt(selectedSizeData.max_ingredients) || 7;
                    }
                    totalPrice += sizePrice - previousSizePrice;
                    updateTotalPrice();
                });
            });
            ingredientTabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    ingredientTabs.forEach(t => t.classList.remove("active"));
                    tab.classList.add("active");
                    const category = tab.dataset.category;
                    document.querySelectorAll(".ingredient-item").forEach(item => {
                        if (category === "all" || item.dataset.category === category) {
                            item.style.display = "flex";
                        } else {
                            item.style.display = "none";
                        }
                    });
                });
            });
        });
    </script>
    <?php include('footer.php'); ?>
</body>

</html>