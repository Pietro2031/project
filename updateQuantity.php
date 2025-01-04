<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cartId = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if ($cartId > 0 && $quantity > 0) {
        // Update the quantity in the database
        $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $quantity, $cartId);

        if ($stmt->execute()) {
            echo "Quantity updated successfully!";
        } else {
            http_response_code(500); // Internal server error
            echo "Error updating quantity: " . $conn->error;
        }

        $stmt->close();
    } else {
        http_response_code(400); // Bad request
        echo "Invalid cart ID or quantity.";
    }
} else {
    http_response_code(405); // Method not allowed
    echo "Invalid request method.";
}

$conn->close();
