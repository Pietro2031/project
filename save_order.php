<?php
include('connection.php');
header('Content-Type: application/json');
session_start();

try {

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);


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


    if (!empty($missingFields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
    }


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
                $toppingIds[] = $topping['id'];
                $toppingNames[] = $topping['name'];
            }
        }
    }
    $toppingsJson = !empty($toppings) ? json_encode($toppings, JSON_UNESCAPED_UNICODE) : null;
    $toppingIdsString = !empty($toppingIds) ? implode(',', $toppingIds) : null;
    $toppingNamesString = !empty($toppingNames) ? implode(',', $toppingNames) : null;


    $stmt = $conn->prepare("
        INSERT INTO custom_drink (
            base_name, base_id, size_name, size_price, flavor_name, flavor_id, toppings, topping_ids, topping_names, total_price, payment_method, status, username
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");


    $status = 0;


    $stmt->bind_param(
        "sisdsssssdsis",
        $data['base']['name'],
        $data['base']['id'],
        $data['size']['name'],
        $data['size']['price'],
        $data['flavor']['name'],
        $data['flavor']['id'],
        $toppingsJson,
        $toppingIdsString,
        $toppingNamesString,
        $data['total_price'],
        $data['payment_method'],
        $status,
        $_SESSION['username']
    );


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
