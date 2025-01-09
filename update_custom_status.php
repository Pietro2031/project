<link rel="stylesheet" href="css/global.css">
<?php
include('connection.php');
if (isset($_GET['status']) && isset($_GET['orderid'])) {
    $status = (int) $_GET['status'];
    $order_id = (int) $_GET['orderid'];
    $low_stock_items = [];
    $orderQuery = "SELECT * FROM `custom_drink` WHERE id = $order_id";
    $orderResult = $conn->query($orderQuery);

    if ($orderResult->num_rows > 0) {
        $orderRow = $orderResult->fetch_assoc();
        $base_id = !empty($orderRow['base_id']) ? $orderRow['base_id'] : 0;
        $flavor_id = !empty($orderRow['flavor_id']) ? $orderRow['flavor_id'] : 0;
        $topping_ids = !empty($orderRow['topping_ids']) ? explode(',', $orderRow['topping_ids']) : [0];
        $size_name = !empty($orderRow['size_name']) ? $orderRow['size_name'] : 0;

        $order_quantity = 1;

        // Check stock for drink base
        $baseQuery = "SELECT quantity, base_name FROM coffee_base WHERE id = $base_id";
        $baseResult = $conn->query($baseQuery);
        if ($baseResult->num_rows > 0) {
            $baseRow = $baseResult->fetch_assoc();
            if ($baseRow['quantity'] < $order_quantity) {
                $low_stock_items[] = $baseRow['base_name'] . " (Base)";
            }
        }

        // Check stock for flavor
        $flavorQuery = "SELECT quantity, flavor_name FROM coffee_flavors WHERE id = $flavor_id";

        $flavorResult = $conn->query($flavorQuery);
        if ($flavorResult->num_rows > 0) {
            $flavorRow = $flavorResult->fetch_assoc();
            if ($flavorRow['quantity'] < $order_quantity) {
                $low_stock_items[] = $flavorRow['flavor_name'] . " (Flavor)";
            }
        }

        // Check stock for toppings
        foreach ($topping_ids as $topping_id) {
            $toppingQuery = "SELECT quantity, topping_name FROM coffee_toppings WHERE id = $topping_id";
            $toppingResult = $conn->query($toppingQuery);
            if ($toppingResult->num_rows > 0) {
                $toppingRow = $toppingResult->fetch_assoc();
                if ($toppingRow['quantity'] < $order_quantity) {
                    $low_stock_items[] = $toppingRow['topping_name'] . " (Topping)";
                }
            }
        }

        // Check stock for cup size
        $sizeQuery = "SELECT quantity, size FROM cup_size WHERE size = '$size_name'";
        $sizeResult = $conn->query($sizeQuery);
        if ($sizeResult->num_rows > 0) {
            $sizeRow = $sizeResult->fetch_assoc();
            if ($sizeRow['quantity'] < $order_quantity) {
                $low_stock_items[] = $sizeRow['size'] . " (Cup Size)";
            }
        }

        // Handle low stock scenario
        if (!empty($low_stock_items)) {
            $low_stock_list = implode(', ', $low_stock_items);
            $updateOrderStatusQuery = "UPDATE custom_drink SET status = 2 WHERE id = $order_id";
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
                        window.location.href = 'admin.php?view_custom_orders';
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

        $conn->query("UPDATE coffee_base SET quantity = quantity - $order_quantity WHERE id = $base_id");
        $conn->query("UPDATE coffee_flavors SET quantity = quantity - $order_quantity WHERE id = $flavor_id");
        foreach ($topping_ids as $topping_id) {
            $conn->query("UPDATE coffee_toppings SET quantity = quantity - $order_quantity WHERE id = $topping_id");
        }
        $conn->query("UPDATE cup_size SET quantity = quantity - $order_quantity WHERE size = '$size_name'");

        if ($status === 1 || $status === 2) {
            $updateStatusQuery = "UPDATE custom_drink SET status = ? WHERE id = ?";
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
        echo "<script>window.location.href = 'admin.php?view_custom_orders';</script>";
    } else {
        echo "<script>alert('Order not found.');</script>";
        echo "<script>window.location.href = 'admin.php?view_custom_orders';</script>";
    }
} else {
    echo "<script>alert('Missing parameters.');</script>";
    echo "<script>window.location.href = 'admin.php?view_custom_orders';</script>";
}
?>