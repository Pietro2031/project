<?php




$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $query = "SELECT passwords FROM admin_account WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if ($password === $admin['passwords']) {

            header("Location: admin.php?theme");
            exit();
        } else {

            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "Admin not found.";
    }
}
?>

<link rel="stylesheet" href="theme1.css">


<div class="validation-container">
    <h2>Admin Validation</h2>
    <form method="POST" action="">
        <input type="password" name="password" placeholder="Enter Admin Password" required>
        <br>
        <button type="submit">Validate</button>
    </form>

    <?php if (!empty($error_message)): ?>
        <p style="color: red; margin-top: 10px;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
</div>