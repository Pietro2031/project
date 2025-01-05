<?php
include('connection.php');
if (isset($_GET['delete_ids']) && !empty($_GET['delete_ids'])) {
    $delete_ids = $_GET['delete_ids'];
    $delete_ids = array_map('intval', $delete_ids);
    $delete_ids_str = implode(",", $delete_ids);
    mysqli_begin_transaction($conn);
    try {
        $delete_from_cart_query = "DELETE FROM cart WHERE product_id IN ($delete_ids_str)";
        $run_delete_from_cart = mysqli_query($conn, $delete_from_cart_query);
        if (!$run_delete_from_cart) {
            throw new Exception("Error deleting items from cart.");
        }
        $delete_query = "DELETE FROM coffee_products WHERE id IN ($delete_ids_str)";
        $run_delete = mysqli_query($conn, $delete_query);
        if (!$run_delete) {
            throw new Exception("Error deleting products from coffee_products.");
        }
        mysqli_commit($conn);
        echo "<script>alert('The selected products have been deleted.');</script>";
        echo "<script>window.open('admin.php?view_products', '_self');</script>";
    } catch (Exception $e) {
        mysqli_roll_back($conn);
        echo "<script>alert('An error occurred while deleting the products.');</script>";
        echo "<script>window.open('admin.php?view_products', '_self');</script>";
    }
} else {
    echo "<script>alert('No products were selected for deletion.');</script>";
    echo "<script>window.open('admin.php?view_products', '_self');</script>";
}
