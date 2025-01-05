<?php
include('connection.php');
session_start();

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['base'], $data['ingredients'], $data['total_price'], $data['payment_method'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
    exit();
}

$base = $data['base'];
$ingredients = $data['ingredients'];
$total_price = $data['total_price'];
$payment_method = $data['payment_method'];
$username = $_SESSION['username']; // Retrieve username from session

try {
    // Fetch user_id based on username
    $userQuery = "SELECT id FROM user_account WHERE username = ?";
    $stmtUser = $conn->prepare($userQuery);
    $stmtUser->bind_param("s", $username);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    if ($resultUser->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    $user = $resultUser->fetch_assoc();
    $user_id = $user['id'];
    $stmtUser->close();

    $conn->begin_transaction();

    // Insert into orders table
    $orderQuery = "INSERT INTO orders (user_id, order_date, total_amount) VALUES (?, NOW(), ?)";
    $stmtOrder = $conn->prepare($orderQuery);
    $stmtOrder->bind_param("id", $user_id, $total_price);
    $stmtOrder->execute();
    $order_id = $stmtOrder->insert_id;
    $stmtOrder->close();

    // Insert into payment table
    $paymentQuery = "INSERT INTO payment (order_id, user_id, payment_mode, amount_paid) VALUES (?, ?, ?, ?)";
    $stmtPayment = $conn->prepare($paymentQuery);
    $stmtPayment->bind_param("iisd", $order_id, $user_id, $payment_method, $total_price);
    $stmtPayment->execute();
    $stmtPayment->close();

    $updateBaseQuery = "UPDATE coffee_products SET total_sales = total_sales + 1, quantity = quantity - 1 WHERE product_name = ?";
    $stmtUpdateBase = $conn->prepare($updateBaseQuery);
    $stmtUpdateBase->bind_param("s", $base);
    $stmtUpdateBase->execute();
    $stmtUpdateBase->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Order saved successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to save the order.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save the order.',
        'error' => $e->getMessage(),
        'sql_error' => $conn->error
    ]);
}
