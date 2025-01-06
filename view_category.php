<?php

// Fetch all categories from the coffee_category table
$categories_query = "SELECT * FROM coffee_category";
$categories_result = mysqli_query($conn, $categories_query);

// Handle delete action
if (isset($_GET['delete_category'])) {
    $id = $_GET['delete_category'];

    // Fetch the image path before deleting the category
    $category_query = "SELECT category_image FROM coffee_category WHERE id = '" . mysqli_real_escape_string($conn, $id) . "'";
    $category_result = mysqli_query($conn, $category_query);
    $category = mysqli_fetch_assoc($category_result);
    $image_path = $category['category_image'];

    // Delete the category
    $delete_query = "DELETE FROM coffee_category WHERE id = '" . mysqli_real_escape_string($conn, $id) . "'";
    if (mysqli_query($conn, $delete_query)) {
        // Delete the image file from the server
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        echo "<script>alert('Category has been deleted successfully');</script>";
        echo "<script>window.location.href='admin.php?view_category'</script>";
    } else {
        echo "<script>alert('Error deleting category');</script>";
    }
}

// Handle adding a new category
if (isset($_POST['add_category'])) {
    $new_category_name = $_POST['category_name'];
    $category_image = $_FILES['category_image'];

    if (!empty($new_category_name) && !empty($category_image['name'])) {
        // Handle image upload
        $target_dir = "uploads/category/";
        $target_file = $target_dir . basename($category_image["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $check = getimagesize($category_image["tmp_name"]);
        if ($check === false) {
            echo "<script>alert('File is not an image.');</script>";
        } elseif (!move_uploaded_file($category_image["tmp_name"], $target_file)) {
            echo "<script>alert('There was an error uploading the image.');</script>";
        } else {
            // Insert new category with image
            $insert_query = "INSERT INTO coffee_category (category_name, category_image) VALUES ('" . mysqli_real_escape_string($conn, $new_category_name) . "', '" . mysqli_real_escape_string($conn, $target_file) . "')";
            if (mysqli_query($conn, $insert_query)) {
                echo "<script>alert('Category added successfully');</script>";
                echo "<script>window.location.href='admin.php?view_category'</script>";
            } else {
                echo "<script>alert('Error adding category');</script>";
            }
        }
    } else {
        echo "<script>alert('Category name and image cannot be empty');</script>";
    }
}

// Handle category update (edit)
if (isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'];
    $updated_category_name = $_POST['edit_category_name'];
    $category_image = $_FILES['edit_category_image'];

    if (!empty($updated_category_name)) {
        // Update image if a new one is uploaded
        $image_update_clause = '';
        if (!empty($category_image['name'])) {
            $target_dir = "uploads/category/";
            $target_file = $target_dir . basename($category_image["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $check = getimagesize($category_image["tmp_name"]);
            if ($check === false) {
                echo "<script>alert('File is not an image.');</script>";
            } elseif (!move_uploaded_file($category_image["tmp_name"], $target_file)) {
                echo "<script>alert('There was an error uploading the image.');</script>";
            } else {
                // If the image is successfully uploaded, set the image update query part
                $image_update_clause = ", category_image='" . mysqli_real_escape_string($conn, $target_file) . "'";
            }
        }

        // Update the category in the database
        $update_query = "UPDATE coffee_category SET category_name='" . mysqli_real_escape_string($conn, $updated_category_name) . "' $image_update_clause WHERE id='" . mysqli_real_escape_string($conn, $category_id) . "'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Category updated successfully');</script>";
            echo "<script>window.location.href='admin.php?view_category'</script>";
        } else {
            echo "<script>alert('Error updating category');</script>";
        }
    } else {
        echo "<script>alert('Category name cannot be empty');</script>";
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">View Categories</h3>
            </div>
            <div class="panel-body">
                <!-- Add Category Form -->
                <form method="POST" action="admin.php?view_category" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="category_name">Add New Category:</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" placeholder="Enter Category Name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_image">Category Image:</label>
                        <input type="file" name="category_image" id="category_image" class="form-control" required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
                </form>
                <br>

                <!-- Categories Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Name</th>
                                <th>Image</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            while ($row_category = mysqli_fetch_assoc($categories_result)) {
                                $i++;
                                $id = $row_category['id'];
                                $category_name = $row_category['category_name'];
                                $category_image = $row_category['category_image'];
                            ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $category_name; ?></td>
                                    <td><img src="<?php echo $category_image; ?>" width="60" height="60" alt="Category Image"></td>
                                    <td><button class="btn btn-primary" onclick="openEditModal('<?php echo $id; ?>', '<?php echo $category_name; ?>', '<?php echo $category_image; ?>')">Edit</button></td>
                                    <td><a href="admin.php?view_category&delete_category=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this category?');"><button>Delete</button></a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Category</h2>
        <form method="POST" action="admin.php?view_category" enctype="multipart/form-data">
            <input type="hidden" name="category_id" id="edit_category_id">
            <div class="form-group">
                <label for="edit_category_name">Category Name:</label>
                <input type="text" name="edit_category_name" id="edit_category_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit_category_image">Category Image:</label>
                <input type="file" name="edit_category_image" id="edit_category_image" class="form-control">
                <img id="current_category_image" src="" width="60" height="60" alt="Current Image">
            </div>
            <button type="submit" name="edit_category" class="btn btn-success">Save Changes</button>
        </form>
    </div>
</div>

<!-- Styles for Modal -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 100px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<!-- Script for Opening and Closing Modal -->
<script>
    function openEditModal(id, name, image) {
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_category_name').value = name;
        document.getElementById('current_category_image').src = image;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>