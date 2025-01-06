<?php
include('connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['base'], $data['ingredients'], $data['total_price'], $data['payment_method'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
        exit;
    }

    $base = $data['base'];
    $ingredients = implode(", ", $data['ingredients']);
    $totalPrice = $data['total_price'];
    $paymentMethod = $data['payment_method'];
    $customerId = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;

    $query = "INSERT INTO custom_drink (customer_id, base, ingredients, total_price, payment_method, order_date) 
              VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issds", $customerId, $base, $ingredients, $totalPrice, $paymentMethod);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save the order.']);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
