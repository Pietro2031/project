<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selectedItems']) && is_array($_POST['selectedItems'])) {
        $selectedItems = $_POST['selectedItems'];

        $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

        $deleteQuery = "DELETE FROM cart WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($deleteQuery);

        if ($stmt) {
            $stmt->bind_param(str_repeat('i', count($selectedItems)), ...$selectedItems);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Selected items have been deleted successfully.');
                    window.location.href = 'cart.php';
                </script>";
            } else {
                echo "<script>
                    alert('Error deleting items: " . addslashes($stmt->error) . "');
                    window.location.href = 'cart.php';
                </script>";
            }

            $stmt->close();
        } else {
            echo "<script>
                alert('Failed to prepare the delete query.');
                window.location.href = 'cart.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('No items selected for deletion.');
            window.location.href = 'cart.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid request method.');
        window.location.href = 'cart.php';
    </script>";
}

$conn->close();
