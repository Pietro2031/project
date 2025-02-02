<link rel="stylesheet" href="css/global.css">
<?php
include('connection.php');
if (isset($_GET['status']) && isset($_GET['orderid'])) {
    $status = (int) $_GET['status'];
    $order_id = (int) $_GET['orderid'];
    $low_stock_items = [];
    $orderQuery = "SELECT * FROM `orders` WHERE id = $order_id";
    $orderResult = $conn->query($orderQuery);

    if ($orderResult->num_rows > 0) {
        $orderRow = $orderResult->fetch_assoc();
        $productIdsString = $orderRow['product_ids'];
        $flavorsString = $orderRow['flavor'];
        $toppingsString = $orderRow['toppings'];
        $sizesString = $orderRow['size'];
        $order_quantity = (int)$orderRow['order_quantity'];

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
            $updateproductQuery = "UPDATE coffee_products SET total_sales = total_sales + $quantity WHERE id = $productId;";
            $updateproductResult = $conn->query($updateproductQuery);
        }

        foreach ($flavorsArray as $flavorName) {
            $flavorQuery = "SELECT quantity, flavor_name FROM coffee_flavors WHERE flavor_name = '$flavorName'";
            $flavorResult = $conn->query($flavorQuery);
            if ($flavorResult->num_rows > 0) {
                $flavorRow = $flavorResult->fetch_assoc();
                if ($flavorRow['quantity'] < $order_quantity) {
                    $low_stock_items[] = $flavorRow['flavor_name'] . " (Flavor)";
                }
            }
        }

        foreach ($toppingsArray as $toppingName) {
            $toppingQuery = "SELECT quantity, topping_name FROM coffee_toppings WHERE topping_name = '$toppingName'";
            $toppingResult = $conn->query($toppingQuery);
            if ($toppingResult->num_rows > 0) {
                $toppingRow = $toppingResult->fetch_assoc();
                if ($toppingRow['quantity'] < $order_quantity) {
                    $low_stock_items[] = $toppingRow['topping_name'] . " (Topping)";
                }
            }
        }

        foreach ($sizesArray as $sizeName) {
            $sizeQuery = "SELECT quantity, size FROM cup_size WHERE size = '$sizeName'";
            $sizeResult = $conn->query($sizeQuery);
            if ($sizeResult->num_rows > 0) {
                $sizeRow = $sizeResult->fetch_assoc();
                if ($sizeRow['quantity'] < $order_quantity) {
                    $low_stock_items[] = $sizeRow['size'] . " (Cup Size)";
                }
            }
        }

        if (!empty($low_stock_items)) {
            $low_stock_list = implode(', ', $low_stock_items);
            $updateOrderStatusQuery = "UPDATE orders SET status = 2 WHERE id = $order_id";
            $conn->query($updateOrderStatusQuery);

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modal = document.createElement('div');
                    modal.style.position = 'fixed';
                    modal.style.top = '0';
                    modal.style.left = '0';
                    modal.style.width = '100%';
                    modal.style.height = '100%';
                    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                    modal.style.zIndex = '1000';
                    modal.style.display = 'flex';
                    modal.style.alignItems = 'center';
                    modal.style.justifyContent = 'center';

                    const content = document.createElement('div');
                    content.style.backgroundColor = '#fff';
                    content.style.padding = '20px';
                    content.style.borderRadius = '8px';
                    content.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
                    content.style.textAlign = 'center';
                    content.style.maxWidth = '500px';
                    content.style.width = '90%';

                    const header = document.createElement('h3');
                    header.textContent = 'Order Canceled';
                    header.style.marginBottom = '10px';

                    const message = document.createElement('p');
                    message.textContent = 'The order has been canceled due to insufficient stock for the following items:';

                    const itemsList = document.createElement('ul');
                    itemsList.style.textAlign = 'left';
                    itemsList.style.paddingLeft = '20px';
                    itemsList.style.marginTop = '10px';

                    '$low_stock_list'.split(', ').forEach(item => {
                        const listItem = document.createElement('li');
                        listItem.textContent = item;
                        itemsList.appendChild(listItem);
                    });

                    const closeButton = document.createElement('button');
                    closeButton.textContent = 'Close';
                    closeButton.style.marginTop = '20px';
                    closeButton.style.padding = '10px 20px';
                    closeButton.style.border = 'none';
                    closeButton.style.backgroundColor = '#337ab7';
                    closeButton.style.color = '#fff';
                    closeButton.style.borderRadius = '4px';
                    closeButton.style.cursor = 'pointer';

                    closeButton.addEventListener('click', function() {
                        modal.remove();
                        window.location.href = 'admin.php?view_order';
                    });

                    content.appendChild(header);
                    content.appendChild(message);
                    content.appendChild(itemsList);
                    content.appendChild(closeButton);

                    modal.appendChild(content);
                    document.body.appendChild(modal);
                });
            </script>";
            exit;
        }

        foreach ($productIdsArray as $productId) {
            $productQuery = "SELECT drink_bases FROM coffee_products WHERE id = $productId";
            $productResult = $conn->query($productQuery);
            if ($productResult->num_rows > 0) {
                $productRow = $productResult->fetch_assoc();
                $drinkBaseId = $productRow['drink_bases'];
                $conn->query("UPDATE coffee_base SET quantity = quantity - $order_quantity WHERE id = $drinkBaseId");
                $conn->query("UPDATE coffee_products SET total_sales = total_sales + $order_quantity WHERE id = $productId");
            }
        }

        foreach ($flavorsArray as $flavorName) {
            $conn->query("UPDATE coffee_flavors SET quantity = quantity - $order_quantity WHERE flavor_name = '$flavorName'");
        }

        foreach ($toppingsArray as $toppingName) {
            $conn->query("UPDATE coffee_toppings SET quantity = quantity - $order_quantity WHERE topping_name = '$toppingName'");
        }

        foreach ($sizesArray as $sizeName) {
            $conn->query("UPDATE cup_size SET quantity = quantity - $order_quantity WHERE size = '$sizeName'");
        }

        if ($status === 1 || $status === 2) {
            $updateStatusQuery = "UPDATE orders SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($updateStatusQuery);
            if ($stmt) {
                $stmt->bind_param('ii', $status, $order_id);
                if ($stmt->execute()) {
                    echo "<script>alert('Order status updated successfully!');</script>";
                } else {
                    echo "<script>alert('Error updating order status. Please try again.');</script>";
                }
                $stmt->close();
            }
        }
        echo "<script>window.location.href = 'admin.php?view_order';</script>";
    } else {
        echo "<script>alert('Order not found.');</script>";
        echo "<script>window.location.href = 'admin.php?view_order';</script>";
    }
} else {
    echo "<script>alert('Missing parameters.');</script>";
    echo "<script>window.location.href = 'admin.php?view_order';</script>";
}
?>