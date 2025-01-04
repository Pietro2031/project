<?php
include('connection.php');
session_start();

if (!empty($_POST['selectedItems']) && is_array($_POST['selectedItems'])) {
    $_SESSION['selectedItems'] = $_POST['selectedItems'];

    header('Location: purchaseItems.php');
    exit();
} else {
    echo '<script>
            alert("Please select an item to proceed to checkout.");
            window.history.back();
          </script>';
    exit();
}
