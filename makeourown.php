<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('connection.php'); ?>
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
<button data-name="' . $ingredient['name'] . '" data-price="' . $ingredient['price'] . '" data-image="' . $ingredient['image'] . '">Add</button>
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
            const MAX_INGREDIENTS = 7;
            const cupContent = document.getElementById("cup-content");
            const totalPriceElement = document.getElementById("total-price");
            const ingredientTabs = document.querySelectorAll(".tab");
            const ingredientsList = document.getElementById("ingredients-list");
            let totalPrice = 0;
            let ingredientsCount = 0;
            let toggleAlignment = true;

            // Base Selection
            const baseItems = document.querySelectorAll(".base-item");
            const baseButtons = document.querySelectorAll(".base-item button");
            baseButtons.forEach((button, index) => {
                button.addEventListener("click", () => {
                    // Reset styles for all base items
                    baseItems.forEach(item => item.classList.remove("selected"));

                    // Add 'selected' class to the parent of the clicked button
                    baseItems[index].classList.add("selected");

                    // Update cup content and total price
                    const baseName = button.dataset.name;
                    const basePrice = parseFloat(button.dataset.price);
                    cupContent.innerHTML = `<div class="base">Base: ${baseName}</div>`;
                    totalPrice = basePrice;
                    ingredientsCount = 0; // Reset ingredient count
                    toggleAlignment = true; // Reset ingredient alignment
                    updateTotalPrice();
                });
            });

            // Ingredient Selection
            const ingredientButtons = document.querySelectorAll(".ingredient-item button");
            ingredientButtons.forEach(button => {
                button.addEventListener("click", () => {
                    if (ingredientsCount >= MAX_INGREDIENTS) {
                        alert(`You can only add up to ${MAX_INGREDIENTS} ingredients.`);
                        return;
                    }

                    const ingredientName = button.dataset.name;
                    const ingredientPrice = parseFloat(button.dataset.price);
                    const ingredientImage = button.dataset.image;
                    const alignmentClass = toggleAlignment ? "ingredient-left" : "ingredient-right";
                    toggleAlignment = !toggleAlignment;

                    // Add ingredient to the cup
                    const ingredientDiv = document.createElement("div");
                    ingredientDiv.classList.add("ingredient", alignmentClass);
                    ingredientDiv.innerHTML = `<img src="${ingredientImage}" alt="${ingredientName}" title="${ingredientName}">`;
                    cupContent.appendChild(ingredientDiv);

                    totalPrice += ingredientPrice;
                    ingredientsCount++;
                    updateTotalPrice();
                });
            });

            // Category Filtering
            ingredientTabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    ingredientTabs.forEach(t => t.classList.remove("active"));
                    tab.classList.add("active");

                    const category = tab.dataset.category;
                    const allIngredients = document.querySelectorAll(".ingredient-item");
                    allIngredients.forEach(ingredient => {
                        if (category === "all" || ingredient.dataset.category === category) {
                            ingredient.style.display = "flex";
                        } else {
                            ingredient.style.display = "none";
                        }
                    });
                });
            });

            function updateTotalPrice() {
                totalPriceElement.textContent = `₱${totalPrice.toFixed(2)}`;
            }
        });
    </script>
    <?php include('footer.php'); ?>
</body>

</html>