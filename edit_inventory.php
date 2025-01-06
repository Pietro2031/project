<?php

include 'connection.php';


$item_id = isset($_GET['id']) ? $_GET['id'] : '';
$item_type = isset($_GET['item_type']) ? $_GET['item_type'] : '';


if (!$item_id || !$item_type) {
    echo "<script>window.location = 'admin.php?view_inventory';</script>";
    exit();
}


switch ($item_type) {
    case 'edit_base':
        $query = "SELECT * FROM coffee_base WHERE id = $item_id";
        break;
    case 'edit_flavor':
        $query = "SELECT * FROM coffee_flavors WHERE id = $item_id";
        break;
    case 'edit_topping':
        $query = "SELECT * FROM coffee_toppings WHERE id = $item_id";
        break;
    case 'edit_cup_size':
        $query = "SELECT * FROM cup_size WHERE id = $item_id";
        break;
    default:
        echo "<script>window.location = 'admin.php?view_inventory';</script>";
        exit();
}


$result = mysqli_query($conn, $query);
$item = mysqli_fetch_assoc($result);


if (!$item) {
    echo "<script>window.location = 'admin.php?view_inventory';</script>";
    exit();
}


if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $image = $_FILES['ItemImg']['name'] ? $_FILES['ItemImg']['name'] : $item['img'];


    if ($_FILES['ItemImg']['error'] == 0) {
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['ItemImg']['tmp_name'], $target);
    }


    switch ($item_type) {
        case 'edit_base':
            $updateQuery = "UPDATE coffee_base SET base_name = '$name', price = '$price', quantity = '$quantity', img = '$target' WHERE id = $item_id";
            break;
        case 'edit_flavors':
            $updateQuery = "UPDATE coffee_flavors SET flavor_name = '$name', price = '$price', quantity = '$quantity', img = '$target' WHERE id = $item_id";
            break;
        case 'edit_toppings':
            $updateQuery = "UPDATE coffee_toppings SET topping_name = '$name', price = '$price', quantity = '$quantity', img = '$target' WHERE id = $item_id";
            break;
        case 'edit_cup_size':
            $updateQuery = "UPDATE cup_size SET size = '$name', price = '$price', quantity = '$quantity', img = '$target' WHERE id = $item_id";
            break;
    }


    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Item updated successfully!'); window.location = 'admin.php?view_inventory';</script>";
    } else {
        echo "<script>alert('Error updating item!');</script>";
    }
}
if ($item_type == 'edit_base') {
    $item_name = $item['base_name'];
} elseif ($item_type == 'edit_flavors') {
    $item_name = $item['flavor_name'];
} elseif ($item_type == 'edit_toppings') {
    $item_name = $item['topping_name'];
} elseif ($item_type == 'edit_cup_size') {
    $item_name = $item['size'];
} else {
    $item_name = '';
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Edit <?php echo ucfirst(str_replace('_', ' ', $item_type)); ?> Item</h3>
            </div>
            <div class="panel-body">
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Item Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $item_name; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="text" class="form-control" id="price" name="price" value="<?php echo $item['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $item['quantity']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ItemImg">Image:</label>
                        <img id="profileImage" src="<?php echo $item['img']; ?>" width="150" height="150">
                        <input type="file" id="ItemImg" name="ItemImg" accept="image/*">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Update Item</button>
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
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>