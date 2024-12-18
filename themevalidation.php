<?php
session_start(); // Start the session to access session variables

// Include the database connection file
include('connection.php');

// Fetch user data from the database
$username = "admin"; // Assuming you want to fetch the admin's profile image
$query = "SELECT profile_picture FROM admin_account WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $profile_picture = $admin['profile_picture']; // Get the profile picture from the database
} else {
    $profile_picture = 'default-profile.png'; // Set a default image if no profile picture exists
}
if (!isset($_SESSION['admin_username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}


// Initialize error message
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];

    // Validate admin password
    $query = "SELECT passwords FROM admin_account WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if ($password === $admin['passwords']) {
            // Password is correct, redirect to theme.php
            header("Location: theme.php");
            exit();
        } else {
            // Incorrect password
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Validation</title>
    <link rel="stylesheet" href="theme1.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="profile">
            <center>
                <div class="profile-image-container">
                    <img src="<?= !empty($profile_picture) ? htmlspecialchars($profile_picture) : 'default-profile.png' ?>" alt="Admin" class="profile-image">
                </div>
            </center>
            <div class="profile-info">
                <p class="profile-name">Hello, Admin</p>
                <p class="profile-role">Administrator</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="themevalidation.php" class="active"><i class="fas fa-paint-brush"></i> Theme</a></li>
                <li><a href="#"><i class="fas fa-box-open"></i> Products</a></li>
                <li><a href="#"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="#"><i class="fas fa-th-list"></i> Inventory</a></li>
                <li><a href="#"><i class="fas fa-receipt"></i> Payment History</a></li>
                <li><a href="userinfo.php"><i class="fas fa-user-tag"></i> User Information</a></li>
            </ul>
        </nav>
        <div class="sidebar-bottom">
            <ul>
                <li><a href="adminprofile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
            </ul>
        </div>
    </div>

    <div class="topbar">
        <h1>Admin Validation</h1>
        <a href="adminlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Validation Container -->
    <div class="validation-container">
        <h2>Admin Validation</h2>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Enter Admin Password" required>
            <br>
            <button type="submit">Validate</button>
        </form>
        
        <!-- Display error message if password is incorrect -->
        <?php if (!empty($error_message)): ?>
            <p style="color: red; margin-top: 10px;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
