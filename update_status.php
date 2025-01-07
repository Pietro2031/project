<?php
include('connection.php');

if (isset($_GET['status']) && isset($_GET['orderid'])) {
    $status = (int) $_GET['status'];
    $order_id = (int) $_GET['orderid'];

    if ($status === 1 || $status === 2) {
        $updateQuery = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $status, $order_id);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Order status updated successfully!');</script>";
                echo "<script>window.location.href = 'admin.php?view_order';</script>";
            } else {
                echo "<script>alert('Error updating order status. Please try again.');</script>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<script>alert('Error preparing update query.');</script>";
        }
    } else {
        echo "<script>alert('Invalid status value.');</script>";
    }
} else {
    echo "<script>alert('Missing parameters.');</script>";
    echo "<script>window.location.href = 'admin.php?view_order';</script>";
}
