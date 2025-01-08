<?php
include('connection.php');
if (isset($_GET['status']) && isset($_GET['orderid'])) {
    $status = (int) $_GET['status'];
    $order_id = (int) $_GET['orderid'];
    $orderQuery = "SELECT * FROM `orders` WHERE id = $order_id";
    $orderResult = $conn->query($orderQuery);
    if ($orderResult->num_rows > 0) {
        $orderRow = $orderResult->fetch_assoc();
        $productIdsString = $orderRow['product_ids'];
        $flavorsString = $orderRow['flavor'];
        $toppingsString = $orderRow['toppings'];
        $sizesString = $orderRow['size'];
        $productIdsArray = explode(',', $productIdsString);
        $flavorsArray = explode(',', $flavorsString);
        $toppingsArray = explode(',', $toppingsString);
        $sizesArray = explode(',', $sizesString);
        foreach ($productIdsArray as $productId) {
            $productId = (int)trim($productId);
            $productQuery = "SELECT drink_bases, flavor_id, toppings_id FROM coffee_products WHERE id = $productId";
            $productResult = $conn->query($productQuery);
            if ($productResult->num_rows > 0) {
                while ($productRow = $productResult->fetch_assoc()) {
                    $drinkBaseId = $productRow['drink_bases'];
                    $flavorId = $productRow['flavor_id'];
                    $toppingId = $productRow['toppings_id'];
                    $quantity = (int) $orderRow['order_quantity'];
                    $updateBaseQuery = "UPDATE coffee_base SET quantity = quantity - $quantity WHERE id = $drinkBaseId";
                    $conn->query($updateBaseQuery);
                    echo $updateBaseQuery;
                    $updateFlavorQuery = "UPDATE coffee_flavors SET quantity = quantity - $quantity WHERE id = $flavorId";
                    $conn->query($updateFlavorQuery);
                    echo $updateBaseQuery;
                    $updateToppingQuery = "UPDATE coffee_toppings SET quantity = quantity - $quantity WHERE id = $toppingId";
                    $conn->query($updateToppingQuery);
                    echo $updateBaseQuery;
                }
            }
        }
        foreach ($flavorsArray as $flavorId) {
            $flavorQuery = "SELECT id, flavor_name, quantity FROM coffee_flavors WHERE flavor_name = '$flavorId'";
            $flavorResult = $conn->query($flavorQuery);
            if ($flavorResult->num_rows > 0) {
                $flavorRow = $flavorResult->fetch_assoc();
                $flavorId = $flavorRow['id'];
                $flavorQuantity = (int) $flavorRow['quantity'];
                $quantity = (int) $orderRow['order_quantity'];
                if ($flavorQuantity >= $quantity) {
                    $updateFlavorQuery = "UPDATE coffee_flavors SET quantity = quantity - $quantity WHERE id = $flavorId";
                    if ($conn->query($updateFlavorQuery)) {
                        echo "Flavor stock updated: " . $updateFlavorQuery . "<br>";
                    } else {
                        echo "Error updating flavor stock: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Not enough stock for flavor: " . $flavorRow['flavor_name'] . "<br>";
                }
            } else {
                echo "Flavor not found: " . $flavorId . "<br>";
            }
        }
        foreach ($toppingsArray as $toppingName) {
            $toppingQuery = "SELECT id, topping_name, quantity FROM coffee_toppings WHERE topping_name = '$toppingName'";
            $toppingResult = $conn->query($toppingQuery);
            if ($toppingResult->num_rows > 0) {
                $toppingRow = $toppingResult->fetch_assoc();
                $toppingId = $toppingRow['id'];
                $toppingQuantity = (int) $toppingRow['quantity'];
                $quantity = (int) $orderRow['order_quantity'];
                if ($toppingQuantity >= $quantity) {
                    $updateToppingQuery = "UPDATE coffee_toppings SET quantity = quantity - $quantity WHERE id = $toppingId";
                    if ($conn->query($updateToppingQuery)) {
                        echo "Topping stock updated: " . $updateToppingQuery . "<br>";
                    } else {
                        echo "Error updating topping stock: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Not enough stock for topping: " . $toppingRow['topping_name'] . "<br>";
                }
            } else {
                echo "Topping not found: " . $toppingName . "<br>";
            }
        }
        foreach ($sizesArray as $sizeName) {

            $sizeQuery = "SELECT id, size, quantity FROM cup_size WHERE size = '$sizeName'";
            $sizeResult = $conn->query($sizeQuery);
            if ($sizeResult->num_rows > 0) {

                $sizeRow = $sizeResult->fetch_assoc();

                $sizeId = $sizeRow['id'];
                $sizeQuantity = (int) $sizeRow['quantity'];

                $quantity = (int) $orderRow['order_quantity'];

                if ($sizeQuantity >= $quantity) {

                    $updateSizeQuery = "UPDATE cup_size SET quantity = quantity - $quantity WHERE id = $sizeId";
                    if ($conn->query($updateSizeQuery)) {
                        echo "Size stock updated: " . $updateSizeQuery . "<br>";
                    } else {
                        echo "Error updating size stock: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Not enough stock for size: " . $sizeRow['size'] . "<br>";
                }
            } else {
                echo "Size not found: " . $sizeName . "<br>";
            }
        }
        if ($status === 1 || $status === 2) {
            $updateStatusQuery = "UPDATE orders SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($updateStatusQuery);
            if ($stmt) {
                $stmt->bind_param('ii', $status, $order_id);
                if ($stmt->execute()) {
                    echo "<script>alert('Order status updated successfully!');</script>";
                    echo "<script>window.location.href = 'admin.php?view_order';</script>";
                } else {
                    echo "<script>alert('Error updating order status. Please try again.');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Error preparing update query.');</script>";
            }
        } else {
            echo "<script>alert('Invalid status value.');</script>";
        }
    } else {
        echo "<script>alert('Order not found.');</script>";
        echo "<script>window.location.href = 'admin.php?view_order';</script>";
    }
} else {
    echo "<script>alert('Missing parameters.');</script>";
    echo "<script>window.location.href = 'admin.php?view_order';</script>";
}
