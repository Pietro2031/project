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
</head>

<body>
    <?php include('header.php'); ?>
    <section class="section">
        <h1>Create Your Drink</h1>
        <div class="customization-container">
            <div class="base-selection">
                <h2>What kind of coffee?</h2>
                <div class="base-options">
                    <?php
                    $baseQuery = "SELECT * FROM drink_bases";
                    $baseResult = $conn->query($baseQuery);
                    if ($baseResult->num_rows > 0) {
                        while ($base = $baseResult->fetch_assoc()) {
                            echo '<div class="base-item">
 <img src="' . $base['image'] . '" alt="' . $base['name'] . '">
 <div class="base-name">' . $base['name'] . '</div>
 <div class="base-price">₱' . number_format($base['price'], 2) . '</div>
 <button class="select-base-btn" data-id="' . $base['id'] . '" data-name="' . $base['name'] . '" data-price="' . $base['price'] . '">Select</button>
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
                    <img src="img/cup.png" alt="">
                    <div id="cup-content" class="cup-content"></div>
                </div>
                <div class="total-price">
                    Total Price: <span id="total-price">₱0.00</span>
                </div>
                <div class="indicator" id="ingredient-indicator">Remaining Ingredients: 7</div>
                <div class="div-22">
                    <div class="label-size">Size</div>
                    <div class="div-size">
                        <div class="div-size-info">
                            <input type="radio" name="size" value="S" id="sizeS" required>
                            <label for="sizeS">S</label>
                        </div>
                        <div class="div-size-info">
                            <input type="radio" name="size" value="M" id="sizeM" required>
                            <label for="sizeM">M</label>
                            <p>+₱10</p>
                        </div>
                        <div class="div-size-info">
                            <input type="radio" name="size" value="L" id="sizeL" required>
                            <label for="sizeL">L</label>
                            <p>+₱20</p>
                        </div>
                    </div>
                </div>
                <button id="checkout-btn" class="checkout-btn">Checkout</button>
            </div>
            <div class="ingredient-selector">
                <h2>Add Ingredients</h2>
                <div class="ingredient-tabs">
                    <button class="tab active" data-category="all">All</button>
                    <button class="tab" data-category="classics">Classics</button>
                    <button class="tab" data-category="fruity">Fruity</button>
                    <button class="tab" data-category="unexpected">Unexpected</button>
                </div>
                <div class="ingredients-list" id="ingredients-list">
                    <?php
                    $ingredientQuery = "SELECT * FROM ingredients";
                    $ingredientResult = $conn->query($ingredientQuery);
                    if ($ingredientResult->num_rows > 0) {
                        while ($ingredient = $ingredientResult->fetch_assoc()) {
                            echo '<div class="ingredient-item" data-category="' . $ingredient['category'] . '">
 <img src="' . $ingredient['image'] . '" alt="' . $ingredient['name'] . '">
 <span>' . $ingredient['name'] . ' (₱' . number_format($ingredient['price'], 2) . ')</span>
 <button data-id="' . $ingredient['id'] . '" data-name="' . $ingredient['name'] . '" data-price="' . $ingredient['price'] . '" data-image="' . $ingredient['image'] . '">Add</button>
 <span class="ingredient-counter" id="counter-' . $ingredient['id'] . '">x0</span>
 </div>';
                        }
                    } else {
                        echo '<p>No ingredients available.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
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
                    cupContent.innerHTML = `<div class="base">Base: ${button.dataset.name}</div>`;
                    toggleAlignment = true;
                    updateTotalPrice();
                    updateIngredientIndicator();
                });
            });
            document.querySelectorAll(".ingredient-item button").forEach(button => {
                button.addEventListener("click", () => {
                    if (ingredientsCount >= maxIngredients) {
                        alert(`You can only add up to ${maxIngredients} ingredients, Try upgrading your cup.`);
                        return;
                    }
                    const ingredientId = button.dataset.id;
                    const ingredientName = button.dataset.name;
                    const ingredientPrice = parseFloat(button.dataset.price);
                    const ingredientImage = button.dataset.image;
                    const alignmentClass = toggleAlignment ? "ingredient-left" : "ingredient-right";
                    toggleAlignment = !toggleAlignment;
                    const ingredientDiv = document.createElement("div");
                    ingredientDiv.classList.add("ingredient", alignmentClass);
                    ingredientDiv.innerHTML = `<img src="${ingredientImage}" alt="${ingredientName}" title="${ingredientName}">`;
                    ingredientDiv.addEventListener("click", () => {
                        cupContent.removeChild(ingredientDiv);
                        totalPrice -= ingredientPrice;
                        ingredientsCount--;
                        updateTotalPrice();
                        updateIngredientIndicator();
                        updateIngredientCounter(ingredientId, -1);
                    });
                    cupContent.appendChild(ingredientDiv);
                    totalPrice += ingredientPrice;
                    ingredientsCount++;
                    updateTotalPrice();
                    updateIngredientIndicator();
                    updateIngredientCounter(ingredientId, 1);
                });
            });
            document.querySelectorAll("input[name='size']").forEach(input => {
                input.addEventListener("change", () => {
                    const selectedSize = input.value;
                    const previousSizePrice = sizePrice;
                    if (selectedSize === "S") {
                        sizePrice = 0;
                        maxIngredients = 7;
                    }
                    if (selectedSize === "M") {
                        sizePrice = 10;
                        maxIngredients = 8;
                    }
                    if (selectedSize === "L") {
                        sizePrice = 20;
                        maxIngredients = 10;
                    }
                    while (ingredientsCount > maxIngredients) {
                        const lastIngredient = cupContent.lastChild;
                        const ingredientPrice = parseFloat(lastIngredient.getAttribute('data-price'));
                        const ingredientId = lastIngredient.getAttribute('data-id');
                        cupContent.removeChild(lastIngredient);
                        totalPrice -= ingredientPrice;
                        ingredientsCount--;
                        updateIngredientCounter(ingredientId, -1);
                    }
                    totalPrice += sizePrice - previousSizePrice;
                    document.querySelector(".virtual-cup").className = `virtual-cup selected-${selectedSize.toLowerCase()}`;
                    updateTotalPrice();
                    updateIngredientIndicator();
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

            function updateTotalPrice() {
                totalPriceElement.textContent = `₱${totalPrice.toFixed(2)}`;
            }

            function updateIngredientIndicator() {
                ingredientIndicator.textContent = `Remaining Ingredients: ${maxIngredients - ingredientsCount}`;
            }

            function updateIngredientCounter(ingredientId, change) {
                const counterElement = document.getElementById(`counter-${ingredientId}`);
                const currentCount = parseInt(counterElement.textContent.replace('x', ''));
                counterElement.textContent = `x${currentCount + change}`;
            }
        });
    </script>
    <?php include('footer.php'); ?>
</body>

</html>