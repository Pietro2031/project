<?php
include 'connection.php';
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$product_id) {
    echo "<script>alert('Product ID not specified!'); window.location = 'admin.php?view_products';</script>";
    exit();
}
$productQuery = "SELECT * FROM coffee_products WHERE id = $product_id";
$productResult = mysqli_query($conn, $productQuery);
if (mysqli_num_rows($productResult) == 0) {
    echo "<script>alert('Product not found!'); window.location = 'admin.php?view_products';</script>";
    exit();
}
$product = mysqli_fetch_assoc($productResult);
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
                <h3 class="panel-title">Edit Coffee Product</h3>
            </div>
            <div class="panel-body">
                <div class="insertform">
                    <form class="formdiv" method="post" action="" enctype="multipart/form-data">
                        <div class="formsection">
                            <div class="formsectioninside">
                                <div class="sectiondiv">
                                    <div class="sectioninsidediv">
                                        <span>Product Name:</span>
                                        <input type="text" name="name" value="<?= $product['product_name'] ?>" required>
                                    </div>
                                    <div class="sectioninsidediv">
                                        <span>Category:</span>
                                        <select name="category_id" id="category" required>
                                            <?php foreach ($categories as $category) : ?>
                                                <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                                    <?= $category['category_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="sectiondiv">
                                    <div class="sectioninsidediv">
                                        <span>Base:</span>
                                        <select name="drink_bases" id="base" required>
                                            <?php foreach ($bases as $base) : ?>
                                                <option value="<?= $base['id'] ?>" <?= $base['id'] == $product['drink_bases'] ? 'selected' : '' ?>>
                                                    <?= $base['base_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="sectioninsidediv">
                                        <span>Flavor:</span>
                                        <select name="flavor_id" id="flavor" required>
                                            <?php foreach ($flavors as $flavor) : ?>
                                                <option value="<?= $flavor['id'] ?>" <?= $flavor['id'] == $product['flavor_id'] ? 'selected' : '' ?>>
                                                    <?= $flavor['flavor_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="sectioninsidediv">
                                        <span>Toppings:</span>
                                        <select name="toppings_id" id="toppings">
                                            <?php foreach ($toppings as $topping) : ?>
                                                <option value="<?= $topping['id'] ?>" <?= $topping['id'] == $product['toppings_id'] ? 'selected' : '' ?>>
                                                    <?= $topping['topping_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="sectioninsidediv">
                                <span>Image:</span>
                                <img id="profileImage" src="<?= $product['product_image'] ?>" width="150" height="150">
                                <label class="btn-upload-img">
                                    Upload Image<input type="file" id="img" name="ItemImg" accept="image/*">
                                </label>
                            </div>
                        </div>
                        <div class="formsection">
                            <div class="formsectioninside">
                                <div class="sectiondiv">
                                    <div class="sectioninsidediv">
                                        <span>Price:</span>
                                        <input type="text" name="price" value="<?= $product['price'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="submit" class="submit-btn" value="Update Product">
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
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
<?php
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $base_id = $_POST['drink_bases'];
    $flavor_id = $_POST['flavor_id'];
    $toppings_id = $_POST['toppings_id'];
    $price = $_POST['price'];
    $total_sales = 0;
    $image = $_FILES['ItemImg']['name'] ? $_FILES['ItemImg']['name'] : $product['product_image'];
    $target = "uploads/" . basename($image);
    if ($_FILES['ItemImg']['error'] == 0) {
        move_uploaded_file($_FILES['ItemImg']['tmp_name'], $target);
    }
    $updateQuery = "UPDATE coffee_products 
SET product_name = '$name', 
category_id = '$category_id', 
drink_bases = '$base_id', 
flavor_id = '$flavor_id', 
toppings_id = '$toppings_id', 
price = '$price', 
product_image = '$target' 
WHERE id = $product_id";
    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Product updated successfully!'); window.location = 'admin.php?view_products';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>