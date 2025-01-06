<?php
include 'connection.php';
$categoryQuery = "SELECT id, category_name FROM coffee_category";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = [];
while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $categoryRow;
}
$baseQuery = "SELECT id, base_name FROM coffee_base";
$baseResult = mysqli_query($conn, $baseQuery);
$bases = [];
while ($baseRow = mysqli_fetch_assoc($baseResult)) {
    $bases[] = $baseRow;
}
$flavorQuery = "SELECT id, flavor_name FROM coffee_flavors";
$flavorResult = mysqli_query($conn, $flavorQuery);
$flavors = [];
while ($flavorRow = mysqli_fetch_assoc($flavorResult)) {
    $flavors[] = $flavorRow;
}
$toppingQuery = "SELECT id, topping_name FROM coffee_toppings";
$toppingResult = mysqli_query($conn, $toppingQuery);
$toppings = [];
while ($toppingRow = mysqli_fetch_assoc($toppingResult)) {
    $toppings[] = $toppingRow;
}
?>
<link rel="stylesheet" href="css/insertform.css">
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Add Coffee Product</h3>
            </div>
            <div class="panel-body">
                <div class="insertform">
                    <form class="formdiv" id="productForm" method="post" action="" enctype="multipart/form-data">
                        <div class="formsection">
                            <div class="formsectioninside">
                                <div class="sectiondiv">
                                    <div class="sectioninsidediv">
                                        <span>Product Name:</span>
                                        <input type="text" name="name" id="name" required>
                                        <p class="tooltiptext">Enter the coffee product name</p>
                                    </div>
                                    <div class="sectioninsidediv">
                                        <span>Category:</span>
                                        <select name="category_id" id="category" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category) : ?>
                                                <option value="<?= $category['id'] ?>">
                                                    <?= $category['category_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="tooltiptext">Select the coffee category (e.g., Espresso, Latte, etc.)</p>
                                    </div>
                                </div>
                                <div class="sectiondiv">
                                    <div class="sectioninsidediv">
                                        <span>Base:</span>
                                        <select name="drink_bases" id="base" required>
                                            <option value="">Select Base</option>
                                            <?php foreach ($bases as $base) : ?>
                                                <option value="<?= $base['id'] ?>">
                                                    <?= $base['base_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="tooltiptext">Select the coffee base (e.g., Espresso, Cold Brew, etc.)</p>
                                    </div>
                                    <div class="sectioninsidediv">
                                        <span>Flavor:</span>
                                        <select name="flavor_id" id="flavor" required>
                                            <option value="">Select Flavor</option>
                                            <?php foreach ($flavors as $flavor) : ?>
                                                <option value="<?= $flavor['id'] ?>">
                                                    <?= $flavor['flavor_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="tooltiptext">Select the coffee flavor (e.g., Vanilla, Caramel, etc.)</p>
                                    </div>
                                    <div class="sectioninsidediv">
                                        <span>Toppings:</span>
                                        <select name="toppings_id" id="toppings">
                                            <option value="">Select Toppings (Optional)</option>
                                            <?php foreach ($toppings as $topping) : ?>
                                                <option value="<?= $topping['id'] ?>">
                                                    <?= $topping['topping_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="tooltiptext">Select any toppings (optional, e.g., Whipped Cream, Chocolate Drizzle)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="sectioninsidediv">
                                <span>Image:</span>
                                <img id="profileImage" src="#" style="display:none;">
                                <label class="btn-upload-img">
                                    Upload Image<input type="file" id="img" name="ItemImg" accept="image/*" required>
                                </label>
                            </div>
                        </div>
                        <div class="formsection">
                            <div class="formsectioninside">
                                <div class="sectiondiv">
                                    <div class="sectioninsidediv">
                                        <span>Price:</span>
                                        <input type="text" name="price" id="price" required>
                                        <p class="tooltiptext">Enter the product price</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="submit" class="submit-btn" value="Add Coffee">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('img').addEventListener('change', function(event) {
        const fileInput = event.target;
        const profileImage = document.getElementById('profileImage');
        const file = fileInput.files[0];
        if (file) {
            profileImage.style.display = 'block';
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('productForm').addEventListener('submit', function(event) {
        const name = document.getElementById('name').value;
        const category = document.getElementById('category').value;
        const base = document.getElementById('base').value;
        const flavor = document.getElementById('flavor').value;
        const price = document.getElementById('price').value;
        const img = document.getElementById('img').value;
        if (!name || !category || !base || !flavor || !price || !img) {
            event.preventDefault();
            alert('Please fill all the required fields before submitting.');
        }
    });
</script>
<?php
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $base_id = $_POST['drink_bases'];
    $flavor_id = $_POST['flavor_id'];
    $toppings_id = $_POST['toppings_id'] ?: null;
    $price = $_POST['price'];
    $total_sales = 0;
    if (isset($_FILES['ItemImg']) && $_FILES['ItemImg']['error'] == 0) {
        $image = $_FILES['ItemImg']['name'];
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['ItemImg']['tmp_name'], $target);
    } else {
        $target = null;
    }
    $insertQuery = "INSERT INTO coffee_products 
                    (product_name, category_id, drink_bases, flavor_id, toppings_id, price, product_image, total_sales) 
                    VALUES ('$name', '$category_id', '$base_id', '$flavor_id', '$toppings_id', '$price', '$target', '$total_sales')";
    if (mysqli_query($conn, $insertQuery)) {
        echo "<script>alert('Product added successfully!'); window.location = 'admin.php?view_inventory';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>