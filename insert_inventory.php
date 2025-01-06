<?php

include 'connection.php';


if (isset($_POST['submit'])) {
    $item_type = mysqli_real_escape_string($conn, $_POST['item_type']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $image = $_FILES['ItemImg']['name'];

    
    if (!empty($name) && !empty($price) && !empty($quantity) && !empty($image)) {
        
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        
        $check = getimagesize($_FILES['ItemImg']['tmp_name']);
        if ($check === false) {
            echo "<script>alert('File is not an image.');</script>";
        } elseif (!move_uploaded_file($_FILES['ItemImg']['tmp_name'], $target_file)) {
            echo "<script>alert('There was an error uploading the image.');</script>";
        } else {
            
            switch ($item_type) {
                case 'base':
                    $insert_query = "INSERT INTO coffee_base (base_name, price, quantity, img) VALUES ('$name', '$price', '$quantity', '$target_file')";
                    break;
                case 'flavor':
                    $insert_query = "INSERT INTO coffee_flavors (flavor_name, price, quantity, img) VALUES ('$name', '$price', '$quantity', '$target_file')";
                    break;
                case 'topping':
                    $insert_query = "INSERT INTO coffee_toppings (topping_name, price, quantity, img) VALUES ('$name', '$price', '$quantity', '$target_file')";
                    break;
                case 'cup_size':
                    $insert_query = "INSERT INTO cup_size (size, price, quantity, img) VALUES ('$name', '$price', '$quantity', '$target_file')";
                    break;
                default:
                    echo "<script>alert('Invalid item type selected!');</script>";
                    exit();
            }

            
            if (mysqli_query($conn, $insert_query)) {
                echo "<script>alert('Item added successfully!'); window.location = 'admin.php?view_inventory';</script>";
            } else {
                echo "<script>alert('Error adding item!');</script>";
            }
        }
    } else {
        echo "<script>alert('All fields are required!');</script>";
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Add New Inventory Item</h3>
            </div>
            <div class="panel-body">
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="item_type">Select Item Type:</label>
                        <select class="form-control" id="item_type" name="item_type" required>
                            <option value="">Select Item Type</option>
                            <option value="base">Base</option>
                            <option value="flavor">Flavor</option>
                            <option value="topping">Topping</option>
                            <option value="cup_size">Cup Size</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Item Name:</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Item Name" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="text" class="form-control" id="price" name="price" placeholder="Enter Item Price" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter Quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="ItemImg">Image:</label>
                        <input type="file" id="ItemImg" name="ItemImg" accept="image/*" required>
                        <img id="profileImage" src="#" width="150" height="150" style="display: none;">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Add Item</button>
                    <a href="admin.php?view_inventory" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('ItemImg').addEventListener('change', function(event) {
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
</script>