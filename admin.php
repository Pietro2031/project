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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information</title>
    <link rel="stylesheet" href="userinfo.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="profile">
            <center>
                <div class="profile-image-container">
                    <!-- Use the fetched profile picture from the database, or fallback to a default image -->
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
                <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="themevalidation.php"><i class="fas fa-paint-brush"></i> Theme</a></li>
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
        <h1>Admin Dashboard</h1>
        <a href="adminlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</body>
</html>
