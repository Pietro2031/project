<?php
include 'connection.php';

if (isset($_GET['item_type']) && isset($_GET['id'])) {
    $item_type = $_GET['item_type'];
    $id = (int)$_GET['id'];

    // Determine the table name based on the item type
    switch ($item_type) {
        case 'base':
            $table = 'coffee_base';
            break;
        case 'flavor':
            $table = 'coffee_flavors';
            break;
        case 'topping':
            $table = 'coffee_toppings';
            break;
        case 'cup_size':
            $table = 'cup_size';
            break;
        default:
            echo "Invalid item type.";
            exit;
    }

    // Delete query
    $deleteQuery = "DELETE FROM $table WHERE id = $id";

    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Item deleted successfully.'); window.location.href = 'admin.php?view_inventory=1';</script>";
    } else {
        echo "<script>alert('Error deleting item: " . mysqli_error($conn) . "'); window.location.href = 'admin.php?view_inventory=1';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'admin.php?view_inventory=1';</script>";
}
