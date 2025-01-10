<?php
include('connection.php');
header('Content-Type: application/json');
session_start();

try {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate data
    $missingFields = [];

    if (empty($data['base']['name'])) {
        $missingFields[] = 'Base name';
    }
    if (!isset($data['base']['id'])) {
        $missingFields[] = 'Base ID';
    }
    if (empty($data['size']['name'])) {
        $missingFields[] = 'Size name';
    }
    if (!isset($data['size']['price'])) {
        $missingFields[] = 'Size price';
    }
    if (!isset($data['total_price'])) {
        $missingFields[] = 'Total price';
    }
    if (empty($data['payment_method'])) {
        $missingFields[] = 'Payment method';
    }

    // Check if any fields are missing
    if (!empty($missingFields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
    }

    // Validate and prepare toppings
    $toppings = [];
    $toppingIds = [];
    $toppingNames = [];
    if (isset($data['toppings']) && is_array($data['toppings'])) {
        foreach ($data['toppings'] as $topping) {
            if (!empty($topping['name']) && isset($topping['id'])) {
                $toppings[] = [
                    'name' => $topping['name'],
                    'id' => $topping['id']
                ];
                $toppingIds[] = $topping['id']; // Collect topping IDs
                $toppingNames[] = $topping['name']; // Collect topping names
            }
        }
    }
    $toppingsJson = !empty($toppings) ? json_encode($toppings, JSON_UNESCAPED_UNICODE) : null;
    $toppingIdsString = !empty($toppingIds) ? implode(',', $toppingIds) : null; // Create comma-separated string of IDs
    $toppingNamesString = !empty($toppingNames) ? implode(',', $toppingNames) : null; // Create comma-separated string of names

    // Prepare SQL query
    $stmt = $conn->prepare("
        INSERT INTO custom_drink (
            base_name, base_id, size_name, size_price, flavor_name, flavor_id, toppings, topping_ids, topping_names, total_price, payment_method, status, username
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Set default status to 'Pending' (1)
    $status = 0;

    // Bind parameters
    $stmt->bind_param(
        "sisdsssssdsis",
        $data['base']['name'],
        $data['base']['id'],
        $data['size']['name'],
        $data['size']['price'],
        $data['flavor']['name'],
        $data['flavor']['id'],
        $toppingsJson,
        $toppingIdsString, // Pass comma-separated string of IDs
        $toppingNamesString, // Pass comma-separated string of names
        $data['total_price'],
        $data['payment_method'],
        $status,
        $_SESSION['username']
    );

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Order successfully saved.',
            'data_received' => [
                'base' => $data['base'],
                'size' => $data['size'],
                'flavor' => $data['flavor'],
                'toppings' => $toppings,
                'topping_ids' => $toppingIdsString,
                'topping_names' => $toppingNamesString,
                'total_price' => $data['total_price'],
                'payment_method' => $data['payment_method']
            ]
        ]);
    } else {
        throw new Exception('Failed to save order. ' . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
